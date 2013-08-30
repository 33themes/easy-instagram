<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Easy_Instagram_Cache {
    static $cache_dir = 'cache/';
	static $minimum_cache_expire_minutes = 10;
	static $default_cache_expire_minutes = 30;
    static $default_image_sizes = array( 'thumbnail', 'low_resolution', 'standard_resolution' );

	//================================================================

	static public function get_cache_dir() {
		return EASY_INSTAGRAM_PLUGIN_PATH . '/' . self::$cache_dir;
	}

	//=========================================================================

	static function get_refresh_minutes() {
		return get_option( 'easy_instagram_cache_expire_time', 
                           self::$default_cache_expire_minutes );
	}

	//================================================================

	static function set_refresh_minutes( $minutes = 0 ) {
		if ( 0 == $minutes ) {
			$minutes = self::$default_cache_expire_minutes;
		}
		update_option( 'easy_instagram_cache_expire_time', (int) $minutes );
	}

	//=================================================================

	// Returns the cached data and a flag telling if the data expired
	static function get_cached_data_for_user_or_tag( $id_or_tag, $limit, $type = 'tag' ) {
		$now = time();
		$hash = md5( $type . $id_or_tag );

		$path = self::get_cache_dir() . $hash . '.cache';

        $cached_data = self::_get_cache_file_content( $path );

		if ( ( NULL == $cached_data ) || !isset( $cached_data['data'] ) || !isset( $cached_data['cache_timestamp'] ) ) {
			return array( NULL, FALSE ); //No cached data found
		}

		// If limit is greater than the cached data size, force clear cache
		if ( $limit > count( $cached_data['data'] ) ) {
			return array( $cached_data, TRUE );
		}

		$cache_minutes = Easy_Instagram_Cache::get_refresh_minutes();

		$delta = ( $now - $cached_data['cache_timestamp'] ) / 60;
		if ( $delta > $cache_minutes ) {
			return array( $cached_data, TRUE );
		}
		else {
			return array( $cached_data, FALSE );
		}
	}

    //================================================================

	static private function _cache_data( $data, $id_or_tag, $type ) {
		$hash = md5( $type . $id_or_tag );

		$path = self::get_cache_dir() . $hash . '.cache';

        if ( file_exists( $path ) ) {
            rename( $path, $path . '.old' );
        }

		$handle = fopen( $path, 'w' );
		if ( FALSE === $handle ) {
			return FALSE;
		}

		$serialized = serialize( $data );

		$would_block = TRUE;
		if ( flock( $handle, LOCK_EX, $would_block ) ) {
			fwrite( $handle, $serialized );
			fflush( $handle );
			flock( $handle, LOCK_UN ); // release the lock
		}
		else {
			error_log( 'Couldn\'t get the lock in cache_data.' );
		}

		fclose( $handle );

        self::_clear_old_cache( $id_or_tag, $type );

		return TRUE;
	}

	//=========================================================================

	static private function _clear_old_cache( $id_or_tag, $type ) {
		$hash = md5( $type . $id_or_tag );

		$old_cache_path = self::get_cached_file_path( $hash . '.cache.old' );
        $new_cache_path = self::get_cached_file_path( $hash . '.cache' );

		if ( !file_exists( $old_cache_path ) || !file_exists( $new_cache_path ) ) {
            return;
        }
            
        $old_cached_data = self::_get_cache_file_content( $old_cache_path );
        $new_cached_data = self::_get_cache_file_content( $new_cache_path );	

        if ( !is_array( $old_cached_data ) || !isset( $old_cached_data['data'] ) ) {
            unlink( $old_cache_path );
            return;
        }
            
        //Get files that are in old cache and not in new cache and delete it
        $new_images = array();
        foreach ( $new_cached_data['data'] as $elem ) {
            foreach ( self::$default_image_sizes as $image_size ) {
                if ( isset( $elem[$image_size] ) && isset( $elem[$image_size]['url'] ) ) {
                    $new_images[] = basename( $elem[$image_size]['url'] );
                }
            }
        }

        $to_delete = array();
        foreach ( $old_cached_data['data'] as $elem ) {
            //Delete old images not in new_files
            foreach ( self::$default_image_sizes as $image_size ) {
                if ( isset( $elem[$image_size] ) && isset( $elem[$image_size]['url'] ) ) {
                    // Extract the file name from the file URL and look for the file in the cache directory
                    $image_basename = basename( $elem[$image_size]['url'] );
                    if ( !in_array( $image_basename, $new_images ) ) {
                        //if ( !preg_match( '/[0-9]+x[0-9]+\.[^\.]+$/', $image_basename ) ) 
                        $to_delete[] = $image_basename;
                    }
                }
            }
        }

        unlink( $old_cache_path );

        if ( empty( $to_delete ) ) {
            return;
        }

        $cache_dir = self::get_cache_dir();
        $files = scandir( $cache_dir );

        foreach ( $to_delete as $filename ) {
            $file_path = self::get_cached_file_path( $filename );
            $path_parts = pathinfo( $file_path );

            if ( file_exists( $file_path ) ) {
                unlink( $file_path );
            }
            //Check for custom thumbnails
            if ( FALSE !== stripos( $path_parts['filename'], 'standard_resolution' ) ) { 
                foreach ( $files as $file ) {
                    if ( preg_match( '/^'.$path_parts['filename'].'-[0-9]+x[0-9]+/', $file ) ) {
                        unlink( self::get_cached_file_path ( $file ) );
                    }
                }
            }
        }
    }

	//================================================================

    static private function _get_cache_file_content( $path ) {
        if ( !file_exists( $path ) ) {
            return NULL;
        }

		$handle = fopen( $path, 'r' );
        if ( FALSE === $handle ) {
            return NULL;
        }

        $locking = TRUE;
		if ( flock( $handle, LOCK_SH, $locking ) ) {
			$data = fread( $handle, filesize( $path ) );
            flock( $handle, LOCK_UN ); // release the lock
		}

		fclose( $handle );
        
		if ( empty( $data ) ) {
            return NULL;
        }

		$cached_data = unserialize( $data );
        return $cached_data;
    }

	//================================================================

	static function clear_expired_cache_action() {
		$valid_files = array( '.gitignore' );
		$cache_dir = self::get_cache_dir();

		$files = scandir( $cache_dir );

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( preg_match( '/\.cache$/', $file ) ) {
					$ret = self::remove_cache_data_if_expired( $file );

					if ( ! empty( $ret ) ) {
						$valid_files = array_merge( $valid_files, $ret );
					}
				}
			}

			// Remove all the files from the cache folder not in the valid files array (or valid files is empty)
			foreach ( $files as $file ) {
				if ( ( '.' != $file ) && ( '..' != $file ) ) {
					if ( ! in_array( $file, $valid_files ) ) {
						$file_path = self::get_cached_file_path( $file );
						if ( file_exists( $file_path ) ) {
							unlink( $file_path );
						}
					}
				}
			}
		}
	}

	//================================================================

	static function remove_cache_data_if_expired( $filename ) {
		$path = self::get_cached_file_path( $filename );

        $cached_data = self::_get_cache_file_content( $path );

		$now = time();
		$delta = ( $now - $cached_data['cache_timestamp'] ) / 60;

		$valid_files = array();

		if ( NULL == $cached_data ) {
			return $valid_files;
		}

		//if ( $delta > 24 * 60 )	{
		if ( $delta > 60 )	{
			if ( !empty( $cached_data['data'] ) ) {
                $cache_dir = self::get_cache_dir();
                $files = scandir( $cache_dir );

				foreach ( $cached_data['data'] as $elem ) {
					//Delete images
					foreach ( self::$default_image_sizes as $image_size ) {
						if ( isset( $elem[$image_size] ) && isset( $elem[$image_size]['url'] ) ) {
							// Extract the file name from the file URL and look for the file in the cache directory
							$file_path = self::get_cached_file_path( basename( $elem[$image_size]['url'] ) );
							if ( file_exists( $file_path ) ) {
								unlink( $file_path );
							}
					   
                            //Remove custom thumbnails if any
                            if ( 'standard_resolution' == $image_size ) {
                                $path_parts = pathinfo( $file_path );
                                foreach ( $files as $file ) {
                                    if ( preg_match( '/^'.$path_parts['filename'].'-[0-9]+x[0-9]+/', $file ) ) {
                                        unlink( self::get_cached_file_path( $file ) );
                                    }
                                }
                            }
                        }
					}
				}
                unlink( $path );
			}
		}
		else {
			if ( ! empty( $cached_data['data'] ) ) {
                $cache_dir = self::get_cache_dir();
                $files = scandir( $cache_dir );

				foreach ( $cached_data['data'] as $elem ) {
					foreach ( self::$default_image_sizes as $image_size ) {
						if ( isset( $elem[$image_size]['url'] ) ) {
							$image_filename = basename( $elem[$image_size]['url'] );
							$file_path = self::get_cached_file_path( $image_filename );
							if ( file_exists( $file_path ) ) {
								$valid_files[] = $image_filename;
							}

                            //Add custom thumbnails as valid
                            if ( 'standard_resolution' == $image_size) {
                                $path_parts = pathinfo( $file_path );
                                foreach ( $files as $file ) {
                                    if ( preg_match( '/^'.$path_parts['filename'].'-[0-9]+x[0-9]+/', $file ) ) {
                                        $valid_files[] = $file;
                                    }
                                }
                            }
                        }
                    }
                }
			}
			$valid_files[] = $filename; //Keep the cache file as valid
		}

		return $valid_files;
	}

	//================================================================

	static function save_remote_image( $remote_image_url, $id ) {
		$filename = '';
		if ( preg_match( '/([^\/\.\?\&]+)\.([^\.\?\/]+)(\?[^\.\/]*)?$/', $remote_image_url, $matches ) ) {
			$filename .= $matches[1] . '_' . $id . '.' . $matches[2];
		}
		else {
			return NULL;
		}

		$path = self::get_cached_file_path( $filename );

        $filename_url = self::get_cached_file_url( $filename );

        //If file already in cache, do not download it again
        if ( file_exists( $path ) ) {
            return $filename_url;
        }

		$image_data = wp_remote_get( $remote_image_url );
		if ( is_wp_error( $image_data ) ) {
			return NULL;
		}
		$content = ( isset( $image_data['body'] ) ? $image_data['body'] : '' );

		if ( empty( $content ) ) {
			return NULL;
		}

		if ( FALSE == file_put_contents( $path, $content ) ) {
			return NULL;
		}

        return $filename_url;
	}

	//=========================================================================

    static function get_cached_file_path( $filename ) {
        return self::get_cache_dir() . $filename;
    }

	//=========================================================================

    static function get_cached_file_url( $filename ) {
        return plugins_url( Easy_Instagram_Cache::$cache_dir . $filename, dirname( __FILE__ ) );
    }

	//=========================================================================

    static function cache_live_data( $live_data, $endpoint_id, $endpoint_type ) {
        $timestamp = time();
        $cache_data = array( 'cache_timestamp' => $timestamp );
        $cache_data['data'] = array();
        
        foreach ( $live_data as $elem ) {

            list( $user_name, $caption_from, 
                  $caption_text, $caption_created_time ) 
                = Easy_Instagram_Utils::get_usename_caption( $elem );

            $cached_elem = array(
                'link'                  => isset( $elem->link ) ? $elem->link : '#',
                'caption_text' 		    => $caption_text,
                'caption_from' 		    => $caption_from,
                'created_time' 		    => $elem->created_time,
                'caption_created_time' 	=> $caption_created_time,
                'user_name'		        => $user_name
			);

            $images = $elem->images;

            foreach ( self::$default_image_sizes as $image_size ) {
                if ( isset( $images->$image_size ) ) {
                    $cached_elem[$image_size] = array(
                        'width'  => $images->$image_size->width,
                        'height' => $images->$image_size->height
                    );

                    $local_url = self::save_remote_image(
                        $images->$image_size->url,
                        $image_size
                    );

                    if ( NULL == $local_url ) {
                        $cached_elem[$image_size]['url'] = $images->$image_size->url;
                    }
                    else {
                        $cached_elem[$image_size]['url'] = $local_url;
                    }
                }
            }
            
            $cache_data['data'][] = $cached_elem;
        }

        self::_cache_data( $cache_data, $endpoint_id, $endpoint_type );

        return $cache_data;
    } 

    //================================================================

    static function _get_cached_custom_thumbnail_basename( $large_image_url, $suffix ) {
        $thumb_basename = NULL;
        $path_parts = pathinfo( $large_image_url );

        if ( isset( $path_parts['filename'] ) ) {
            $thumb_basename = $path_parts['filename'] . '-' . $suffix . '.' . $path_parts['extension'];
        }
        else {
            //PHP < 5.2.0
            if ( preg_match( '/^([^\.]+)\.[^\.]+$/', $path_parts['basename'], $matches ) ) {
                $thumb_basename = $matches[1] . '-' . $suffix . '.' . $path_parts['extension'];
            }
        }
        return $thumb_basename;
    }

    //================================================================

    static function get_cached_custom_thumbnail_path( $large_image_url, $suffix ) {
        $thumb_basename = self::_get_cached_custom_thumbnail_basename( $large_image_url, $suffix );
        if ( empty( $thumb_basename ) ) {
            return NULL;
        }

        $thumb_path = Easy_Instagram_Cache::get_cached_file_path( $thumb_basename );
        if ( file_exists( $thumb_path ) ) {
            return $thumb_path;
        }

        return NULL;
    }

    //================================================================

    static function get_cached_custom_thumbnail_url( $large_image_url, $suffix ) {
        $thumb_basename = self::_get_cached_custom_thumbnail_basename( $large_image_url, $suffix );
        if ( empty( $thumb_basename ) ) {
            return NULL;
        }

        $thumb_path = Easy_Instagram_Cache::get_cached_file_path( $thumb_basename );
        if ( file_exists( $thumb_path ) ) {
            $thumb_url = Easy_Instagram_Cache::get_cached_file_url( $thumb_basename );
            return $thumb_url;
        }

        return NULL;
    }

    //================================================================

    static function get_custom_thumbnail_url( $elem, $width, $height ) {
        $large_image_url = $elem['standard_resolution']['url'];
        $thumbnail_url = $elem['thumbnail']['url'];

        if ( ! function_exists( 'wp_get_image_editor' ) ) {
            return $thumbnail_url;
        }

        $suffix = $width . 'x' . $height;
        //If thumbnail in cache, return it
        $custom_thumb_url = self::get_cached_custom_thumbnail_url( $large_image_url, $suffix );
        if ( !empty( $custom_thumb_url ) ) {
            return $custom_thumb_url;
        }

        //If cache failed and this is not a local file, return the default thumbnail_url
        $cache_url = plugins_url( self::$cache_dir, dirname( __FILE__ ) );
        $pos = strpos( $large_image_url, $cache_url );
        if ( 0 !== $pos ) {
            return $thumbnail_url;
        }

        //Large image in cache, let's create a new thumbnail
        $large_image_filename = basename( $large_image_url );
        $large_image_path = self::get_cached_file_path( $large_image_filename );
        
        $image = wp_get_image_editor( $large_image_path );
        $image->resize( $width, $height, false );
        $new_filename = $image->generate_filename( $suffix );
        $ret = $image->save( $new_filename );        
        if ( is_wp_error( $ret ) ) {
            return $thumbnail_url;
        }
        
        return self::get_cached_file_url( basename( $new_filename ) );
    }
}