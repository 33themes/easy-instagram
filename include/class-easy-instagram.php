<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once 'class-easy-instagram-utils.php';
require_once 'class-easy-instagram-cache.php';

class Easy_Instagram {
	static $max_images = 10;
	static $default_caption_char_limit = 100;
	static $default_author_text = 'by %s';
	static $default_thumb_click = '';
	static $default_time_text = 'posted #T#'; //#T# will be replaced with the specified time_format
	static $default_time_format = '#R#'; //Relative time
    static $default_thumb_size = ''; //Leave empty for default Instagram thumb size
    static $min_thumb_size = 10;

	static $load_scripts_and_styles = FALSE;

	static function get_thumb_click_options() {
		return array(
			''			=> __( 'Do Nothing', 'Easy_Instagram' ),
			'thickbox'	=> __( 'Show in Thickbox', 'Easy_Instagram' ) ,
			'original'	=> __( 'Show original in a new tab', 'Easy_Instagram' )
		);
	}

	//=========================================================================

	static function admin_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'Easy Instagram', 'Easy_Instagram' ),
			__( 'Easy Instagram', 'Easy_Instagram' ),
			'manage_options',
			'easy-instagram',
			array( 'Easy_Instagram', 'admin_page' )
		);
	}

	//=========================================================================

	static function register_scripts_and_styles() {
		if ( ! is_admin() ) {
			wp_register_style( 'Easy_Instagram', plugins_url( 'css/style.css', dirname( __FILE__ ) ) );
			wp_register_script( 'Easy_Instagram', plugins_url( 'js/main.js', dirname( __FILE__ ) ) );
			
			$after_ajax_content_load = self::get_general_setting( 'after_ajax_content_load' );
			wp_localize_script( 'Easy_Instagram', 'Easy_Instagram_Settings', 
				array(
					'ajaxurl'                 => admin_url( 'admin-ajax.php' ), 
					'after_ajax_content_load' => $after_ajax_content_load 
				) 
			);  
		}
	}

	//=========================================================================

	static function enqueue_scripts_and_styles() {
		if ( true == self::$load_scripts_and_styles ) {
			wp_enqueue_style( 'Easy_Instagram' );
			wp_enqueue_script( 'Easy_Instagram' );
		}
	}

	//=========================================================================

    static function admin_init() {
        wp_register_style( 'Easy_Instagram_Admin', plugins_url( 'css/admin.css', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'Easy_Instagram_Admin' );

		global $pagenow;
		if ( 'options-general.php' == $pagenow ) {
            wp_register_script( 'Easy_Instagram_Admin', plugins_url( 'js/admin.js', dirname( __FILE__ ) ) );
			wp_enqueue_script( 'Easy_Instagram_Admin' );
		}
	}

	//=========================================================================

	static function init() {
		add_thickbox();
	}

	//=========================================================================

	static function set_instagram_settings( $client_id, $client_secret, $redirect_uri = '' ) {
		update_option( 'easy_instagram_client_id', $client_id );
		update_option( 'easy_instagram_client_secret', $client_secret );
		if ( '' != $redirect_uri ) {
			update_option( 'easy_instagram_redirect_uri', $redirect_uri );
		}
	}

	//=========================================================================

	static function get_instagram_settings() {
		$client_id = get_option( 'easy_instagram_client_id' );
		$client_secret = get_option( 'easy_instagram_client_secret' );
		
		$default_redirect_uri = admin_url( 'options-general.php?page=easy-instagram' );
		$redirect_uri = get_option( 'easy_instagram_redirect_uri', $default_redirect_uri );
		return array( $client_id, $client_secret, $redirect_uri );
	}

	//=========================================================================
		
	static function set_general_settings( $settings ) {
		update_option( 'easy_instagram_general_settings', $settings );
	}

	//=========================================================================
	
	static function get_general_settings() {
		return get_option( 'easy_instagram_general_settings', array() );
	}

	//=========================================================================
	
	static function get_general_setting( $key ) {
		$settings = self::get_general_settings();
		if ( isset ( $settings[$key] ) ) {
			return $settings[$key];
		}
		return NULL;
	}
	
	//=========================================================================

	static function get_instagram_config() {
		list( $client_id, $client_secret, $redirect_uri ) = self::get_instagram_settings();

		return array(
			'client_id' 	=> $client_id,
			'client_secret' => $client_secret,
			'grant_type' 	=> 'authorization_code',
			'redirect_uri' 	=> $redirect_uri
		);
	}

	//=========================================================================

	static function admin_page() {
		if ( isset( $_POST['ei_general_settings'] ) &&
				check_admin_referer( 'ei_general_settings_nonce', 'ei_general_settings_nonce' ) ) {

			$errors = array();

			$instagram_client_id = isset( $_POST['ei_client_id'] )
				? trim( $_POST['ei_client_id'] )
				: '';

			$instagram_client_secret = isset( $_POST['ei_client_secret'] )
				? trim( $_POST['ei_client_secret'] )
				: '';
			
			if ( isset( $_POST['ei_redirect_uri'] ) ) {
				$instagram_redirect_uri = trim( $_POST['ei_redirect_uri'] );
			}

			if ( empty( $instagram_client_id ) ) {
				$errors['client_id'] = __( 'Please enter your Instagram client id', 'Easy_Instagram' );
			}

			if ( empty( $instagram_client_secret ) ) {
				$errors['client_secret'] = __( 'Please enter your Instagram client secret', 'Easy_Instagram' );
			}

			if ( empty( $errors ) ) {
				self::set_instagram_settings( $instagram_client_id, $instagram_client_secret, $instagram_redirect_uri );
			}

			$cache_expire_time = isset( $_POST['ei_cache_expire_time'] )
				? (int) $_POST['ei_cache_expire_time']
				: 0;

			if ( $cache_expire_time < Easy_Instagram_Cache::$minimum_cache_expire_minutes ) {
				$cache_expire_time = Easy_Instagram_Cache::$minimum_cache_expire_minutes;
			}
			
			Easy_Instagram_Cache::set_refresh_minutes( $cache_expire_time );
		
			$settings = self::get_general_settings();
			$after_ajax_content_load = isset( $_POST['ei_after_ajax_content_load'] )
				? trim( $_POST['ei_after_ajax_content_load'] )
				: '';
			$settings['after_ajax_content_load'] = $after_ajax_content_load;
			self::set_general_settings( $settings );
		}
		else {
			$after_ajax_content_load = self::get_general_setting( 'after_ajax_content_load' );
		}

		list( $instagram_client_id, $instagram_client_secret, $instagram_redirect_uri )
			= self::get_instagram_settings();
		
		$logout_requested = false;
		if ( isset( $_POST['instagram-logout'] )
				&& check_admin_referer( 'ei_user_logout_nonce', 'ei_user_logout_nonce' ) ) {
			self::set_access_token( '' );
			update_option( 'ei_access_token', '' );
			$logout_requested = true;
		}


		$config = self::get_instagram_config();
		$instagram = new MC_Instagram_Connector( $config );
		$access_token = self::get_access_token();
		$cache_dir = Easy_Instagram_Cache::get_cache_dir();
		$cache_expire_time = Easy_Instagram_Cache::get_refresh_minutes();
		$instagram_exception = NULL;
		
		if ( ! $logout_requested && empty ( $access_token ) ) {
			if ( isset( $_GET['code'] ) ) {
				try {
					$access_token = $instagram->getAccessToken();
					if ( ! empty( $access_token ) ) {
						self::set_access_token( $access_token );
					}

					$instagram_user = $instagram->getCurrentUser();
					if ( ! empty( $instagram_user ) ) {
						self::set_instagram_user_data( $instagram_user->username, $instagram_user->id );
					}
				} catch ( Exception $ex ) {
					$instagram_exception = $ex;
				}
			}
		}
?>
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php _e( 'Easy Instagram', 'Easy_Instagram' ) ?></h2>

	<h2 class='ei-nav-tab-wrapper'>
	<a href='#' class='ei-nav-tab ei-nav-tab-active' id='ei-select-general-settings'><?php _e( 'Plugin Settings', 'Easy_Instagram' ); ?></a>
	<a href='#' class='ei-nav-tab' id='ei-select-help'><?php _e( 'Help', 'Easy_Instagram' ); ?></a>
	</h2>

	<div id='ei-general-settings'>
	<form method='POST' action='' class='easy-instagram-settings-form'>

		<table class='easy-instagram-settings'>
			<?php if ( !is_writable( $cache_dir ) ): ?>
				<tr class='warning'>
					<td colspan='2'>
						<?php printf( __( 'The directory %s is not writable !', 'Easy_Instagram' ), $cache_dir ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td colspan='2'><h3><?php _e( 'General Settings', 'Easy_Instagram' ); ?></h3></td>
			</tr>
			<tr>
				<td class='labels'>
					<label for='ei-client-id'><?php _e( 'Application Client ID', 'Easy_Instagram' ); ?></label>
				</td>
				<td>
					<input type='text' name='ei_client_id' id='ei-client-id' value='<?php echo esc_html( $instagram_client_id ); ?>' />
					<br />
					<?php if ( isset( $errors['client_id'] ) ): ?>
						<div class='form-error'><?php echo $errors['client_id']; ?></div>
					<?php endif; ?>

					<span class='info'><?php _e( 'This is the ID of your Instagram application', 'Easy_Instagram' ); ?></span>
				</td>
			</tr>

			<tr>
				<td class='labels'>
					<label for='ei-client-secret'><?php _e( 'Application Client Secret', 'Easy_Instagram' ); ?></label>
				</td>
				<td>
					<input type='text' name='ei_client_secret' id='ei-client-secret' value='<?php echo esc_html( $instagram_client_secret ); ?>' />
					<br />
					<?php if ( isset( $errors['client_secret'] ) ): ?>
						<div class='form-error'><?php echo $errors['client_secret']; ?></div>
					<?php endif; ?>

					<span class='info'><?php _e( 'This is your Instagram application secret', 'Easy_Instagram' ); ?></span>
				</td>
			</tr>

			<tr>
				<td class='labels'>
					<label for='ei-redirect-uri'><?php _e( 'Application Redirect URI', 'Easy_Instagram' ); ?></label>
				</td>
				<td>
					<input type='text' name='ei_redirect_uri' id='ei-redirect-uri' value='<?php echo esc_html( $instagram_redirect_uri ); ?>' />
					<br />
					<?php if ( isset( $errors['redirect_uri'] ) ): ?>
						<div class='form-error'><?php echo $errors['redirect_uri']; ?></div>
					<?php endif; ?>
					<span class='info'><?php _e( 'This is your Instagram application redirect URI.', 'Easy_Instagram' ); ?></span>
				</td>
			</tr>

			<tr>
				<td class='labels'>
					<label for='ei-cache-expire-time'><?php _e( 'Cache Expire Time (minutes)', 'Easy_Instagram' ); ?></label>
				</td>
				<td>
					<input type='text' name='ei_cache_expire_time' id='ei-cache-expire-time' value='<?php echo esc_html( $cache_expire_time ); ?>' />
					<br />
					<span class='info'>
						<?php printf( __( 'Minimum expire time: %d minutes.',
											'Easy_Instagram' ),
										Easy_Instagram_Cache::$minimum_cache_expire_minutes ); ?>
					</span>
				</td>
			</tr>

			<tr>
				<td class='labels'>
					<label for='ei-after-ajax-content-load'><?php _e( 'Extra JS to run after AJAX Easy Instagram content load', 'Easy_Instagram' ); ?></label>
				</td>
				<td>
					<textarea name='ei_after_ajax_content_load' id='ei-after-ajax-content-load'><?php echo esc_html( $after_ajax_content_load ); ?></textarea>
					<br />
					<span class='info'>EXPERIMENTAL. Use with caution, it might break your site.</span>
				</td>
			</tr>
			
			<tr>
				<td>
					<input type='hidden' name='ei_general_settings' value='1' />
					<?php wp_nonce_field( 'ei_general_settings_nonce', 'ei_general_settings_nonce' ); ?>
				</td>
				<td>
					<input type='submit' value='<?php _e( "Save Settings" , "Easy_Instagram" ) ?>' name='submit' />
				</td>
			</tr>

		</table>
	</form>

	<form method='POST' action='' class='easy-instagram-settings-form'>
		<table class='easy-instagram-settings'>
		<?php if ( empty( $access_token ) ) : ?>
			<tr>
				<td colspan='2'><h3><?php _e( 'Instagram Account', 'Easy_Instagram' ); ?></h3></td>
			</tr>

			<tr>
				<td>
					<?php if ( !empty( $instagram_client_id )
						&& !empty( $instagram_client_secret )
						&& ! empty( $instagram_redirect_uri ) ): ?>
						<?php $authorization_url = $instagram->getAuthorizationUrl(); ?>
						<a href="<?php echo $authorization_url;?>"><?php _e( 'Instagram Login', 'Easy_Instagram' );?></a>
					<?php else: ?>
						<?php _e( 'Please configure the General Settings first', 'Easy_Instagram' ); ?>
					<?php endif; ?>
				</td>
				<td>
				</td>
			</tr>
		<?php else: ?>
			<?php list( $username, $user_id ) = self::get_instagram_user_data(); ?>
				<tr>
					<td colspan='2'><h3><?php _e( 'Instagram Account', 'Easy_Instagram' ); ?></h3></td>
				</tr>
				<tr>
					<td class='labels'>
						<label><?php _e( 'Instagram Username', 'Easy_Instagram' ); ?></label>
					</td>
					<td>
						<?php echo $username; ?>
					</td>
				</tr>

				<tr>
					<td class='labels'>
						<label><?php _e( 'Instagram User ID', 'Easy_Instagram' ); ?></label>
					</td>
					<td>
						<?php echo $user_id; ?>
					</td>
				</tr>

				<tr>
					<td>
						<?php wp_nonce_field( 'ei_user_logout_nonce', 'ei_user_logout_nonce' ); ?>
					</td>
					<td>
						<input type='submit' name='instagram-logout' value="<?php _e( 'Instagram Logout', 'Easy_Instagram' );?>" />
					</td>
				</tr>
		<?php endif; ?>
			<?php if ( NULL != $instagram_exception ): ?>
			<tr>
				<td colspan='2' class='exception'>
					<?php echo $instagram_exception->getMessage(); ?>
				</td>
			</tr>				
			<?php endif; ?>
		</table>
	</form>

	</div> <?php /* ei-general-setings */ ?>

	<div id='ei-help'>
		<?php self::print_help_page(); ?>
	</div>

<?php

	}
	
	//=========================================================================

	static function print_help_page() {
		$usage_file = trailingslashit( dirname( __FILE__ ) ) . '../usage.html';
		include( $usage_file );
	}

	//=========================================================================

	static function set_instagram_user_data( $username, $id ) {
		update_option( 'easy_instagram_username', $username );
		update_option( 'easy_instagram_user_id', $id );
	}

	//=========================================================================

	static function get_instagram_user_data() {
		$username = get_option( 'easy_instagram_username' );
		$user_id = get_option( 'easy_instagram_user_id' );
		return array( $username, $user_id );
	}

	//=========================================================================

	static function set_access_token( $access_token ) {
		update_option( 'easy_instagram_access_token', $access_token );
	}

	//=========================================================================

	static function get_access_token() {
		return get_option( 'easy_instagram_access_token' );
	}

	//=========================================================================

	static function get_live_data( $instagram, $endpoint, $endpoint_type, $limit = 1 ) {
        switch ( $endpoint_type ) {
        case 'user':
            $live_data = $instagram->getUserRecent( $endpoint );
            break;

        case 'tag':
            $live_data = $instagram->getRecentTags( $endpoint );
            break;

        default:
            $live_data = NULL;
            break;
        }
		
        if ( NULL != $live_data ) {
            $recent = json_decode( $live_data );
            if ( NULL == $recent ) {
                $live_data = NULL;                
            }
            else {
                if ( $limit > self::$max_images ) {
                    $limit = self::$max_images;
                }

				if ( ! isset( $recent->data ) || ( NULL == $recent->data ) ) {
					$live_data = NULL;
				}
				
                $live_data = array_slice( $recent->data, 0, $limit );
            }
        }

		return $live_data;
	}

	//=========================================================================

	static function shortcode( $attributes ) {
		extract(
			shortcode_atts(
				array(
					'tag'					=> '',
					'user_id'				=> '',
					'limit'					=> 1,
					'caption_hashtags'		=> true,
					'caption_char_limit'	=> self::$default_caption_char_limit,
					'author_text'			=> self::$default_author_text,
					'author_full_name'      => false,
					'thumb_click'			=> self::$default_thumb_click,
					'time_text'				=> self::$default_time_text,
					'time_format'			=> self::$default_time_format,
                    'thumb_size'            => self::$default_thumb_size
				),
				$attributes
			)
		);

		//$caption_hashtags = strtolower( $caption_hashtags );
		//$author_full_name = strtolower( $author_full_name );
		
        $params = array(
            'tag'                => $tag,
            'user_id'            => $user_id,
            'limit'              => $limit,
            'caption_hashtags'   => $caption_hashtags,
            'caption_char_limit' => $caption_char_limit, 
            'author_text'        => $author_text,
			'author_full_name'   => $author_full_name,
            'thumb_click'        => $thumb_click, 
            'time_text'          => $time_text, 
            'time_format'        => $time_format,
            'thumb_size'         => $thumb_size
        );

		return self::generate_content( $params );
	}

    //================================================================

    static private function _get_data_for_user_or_tag( $instagram, $endpoint_id, $limit, $endpoint_type ) {
        if ( empty( $endpoint_id ) || empty( $endpoint_type ) ) {
            return NULL;
        }
        
        // Get cached data if available. Get live data if no cached data found.
        list( $data, $expired ) = Easy_Instagram_Cache::get_cached_data_for_user_or_tag( $endpoint_id, $limit, $endpoint_type );

        $live_data = NULL;
        if ( $expired || ( NULL == $data ) ) {
            $live_data = self::get_live_data( $instagram, $endpoint_id, $endpoint_type, $limit );
        }
    
        if ( empty( $live_data ) ) {
            if ( ! empty( $data ) ) {
                return $data['data'];
            }
            else {
                return NULL;
            }
        }

        //Cache live data
        $cache_data = Easy_Instagram_Cache::cache_live_data( $live_data, $endpoint_id, $endpoint_type );
            
        return $cache_data['data'];
    }

    //================================================================

    static function get_thumb_size_from_params( $param_thumb_size ) {
        $thumb_size = trim( $param_thumb_size );
        
        $thumb_w = 0;
        $thumb_h = 0;

        if ( preg_match( '/^([0-9]+)(?:\s*)?x(?:\s*)?([0-9]+)$/', $thumb_size, $matches ) ) {
            $w = (int) $matches[1];
            $h = (int) $matches[2];
            if ( $w >= self::$min_thumb_size && $h >= self::$min_thumb_size ) {
                $thumb_w = $w;
                $thumb_h = $h;
            }
        }
        else {
            if ( preg_match( '/^([0-9]+)(?:\s*px)?$/', $thumb_size, $matches ) ) {
                $w = (int) $matches[1];
                if ( $w >= self::$min_thumb_size ) {
                    $thumb_w = $thumb_h = $w;
                }
            }
        }
        return array( $thumb_w, $thumb_h );
    }

    //================================================================

	static function _get_render_elements_for_ajax( $params ) {
        $tag                = $params['tag'];
        $user_id            = $params['user_id'];
        $limit              = $params['limit'];
        $caption_hashtags   = $params['caption_hashtags'];
        $caption_char_limit = $params['caption_char_limit'];
        $author_text        = $params['author_text'];
		$author_full_name   = $params['author_full_name'];
        $thumb_click        = $params['thumb_click'];
        $time_text          = trim( $params['time_text'] );
        $time_format        = trim( $params['time_format'] );
		$thumb_size         = trim( $params['thumb_size'] );
		
		//Generate a unique id for the wrapper div
		$wrapper_id = 'eitw-' . md5( microtime() . rand( 1, 1000 ) );
		$loading_image = '<img src="' . plugins_url( 'images/ajax-loader.gif', dirname( __FILE__ ) ) . '" alt="' .  __( 'Loading...', 'Easy_Instagram' ) . '" />';
		$content_loading_info = apply_filters( 'easy_instagram_content_loading_info', $loading_image );
		
		$out = '';
		
		$out .= '<div class="easy-instagram-thumbnail-wrapper" id="' . $wrapper_id . '">';
		$out .= $content_loading_info;
		$out .= '<form action="" style="display:none;">';
		$out .= '<input type="hidden" name="action" value="easy_instagram_content" />';
		$out .= '<input type="hidden" name="tag" value="' . esc_attr( $tag ) .'" />';
		$out .= '<input type="hidden" name="user_id" value="' . esc_attr( $user_id ) .'" />';
		$out .= '<input type="hidden" name="limit" value="' . esc_attr( $limit ) .'" />';
		$out .= '<input type="hidden" name="caption_hashtags" value="' . esc_attr( $caption_hashtags ) .'" />';
		$out .= '<input type="hidden" name="caption_char_limit" value="' . esc_attr( $caption_char_limit ) .'" />';
		$out .= '<input type="hidden" name="author_text" value="' . esc_attr( $author_text ) .'" />';
		$out .= '<input type="hidden" name="author_full_name" value="' . esc_attr( $author_full_name ) .'" />';
		$out .= '<input type="hidden" name="thumb_click" value="' . esc_attr( $thumb_click ) .'" />';
		$out .= '<input type="hidden" name="time_text" value="' . esc_attr( $time_text ) .'" />';
		$out .= '<input type="hidden" name="time_format" value="' . esc_attr( $time_format ) .'" />';
		$out .= '<input type="hidden" name="thumb_size" value="' . esc_attr( $thumb_size ) .'" />';
		$out .= '<input type="hidden" name="easy_instagram_content_security" value="' . wp_create_nonce( 'easy-instagram-content-nonce' ) .'" />';
		$out .= '</form>';
		$out .= '</div>';
		return $out;
	}

    //================================================================
	
    static function _get_render_elements( $instagram_elements, $params ) {
        $out = '';

        if ( empty( $instagram_elements ) ) {
            return $out;
        }

        $limit              = $params['limit'];
        $caption_hashtags   = $params['caption_hashtags'];
        $caption_char_limit = $params['caption_char_limit'];
        $author_text        = $params['author_text'];
		$author_full_name   = $params['author_full_name'];
        $thumb_click        = $params['thumb_click'];
        $time_text          = trim( $params['time_text'] );
        $time_format        = trim( $params['time_format'] );

        list( $thumb_w, $thumb_h ) = self::get_thumb_size_from_params( $params['thumb_size'] );

        $crt = 0;
        foreach ( $instagram_elements as $elem ) {
            $large_image_url = $elem['standard_resolution']['url'];
            $thumbnail_url = $elem['thumbnail']['url'];
            $instagram_image_original_link = $elem['link'];

            if ( $thumb_w > 0 && $thumb_h > 0 ) {
                //TODO: generate new thumbnails
                $width = $thumb_w;
                $height = $thumb_h;
                $thumbnail_url = Easy_Instagram_Cache::get_custom_thumbnail_url( $elem, $width, $height );
            }
            else {
                $width = $elem['thumbnail']['width'];
                $height = $elem['thumbnail']['height'];
            }

			if ( '' == $caption_hashtags ) {
				$caption_hashtags = false;
			}

            $caption_text = Easy_Instagram_Utils::get_caption_text( $elem, $caption_hashtags, $caption_char_limit );
            $thickbox_caption_text = Easy_Instagram_Utils::get_caption_text( $elem, false, 100 );

            $out .= '<div class="easy-instagram-thumbnail-wrapper">';

            $has_thumb_action = FALSE;
            switch ( $thumb_click ) {
            case 'thickbox':
				$link = '<a href="' . $large_image_url . '" class="thickbox" title="' . $thickbox_caption_text . '">';
                $out .= apply_filters( 'easy_instagram_thumb_link', $link );
                $has_thumb_action = TRUE;
                break;

            case 'original':
				$link = '<a href="' . $instagram_image_original_link . '" target="_blank" title="' . $caption_text . '">';
                $out .= apply_filters( 'easy_instagram_thumb_link', $link );
                $has_thumb_action = TRUE;
                break;

            default:
                break;
            }

            $out .= '<img src="' . $thumbnail_url . '" alt="" style="width:'
                . $width. 'px; height: ' . $height . 'px;" class="easy-instagram-thumbnail" />';
            
            if ( $has_thumb_action ) {
                $out .= '</a>';
            }

            if ( '' != $elem['caption_from'] ) {
                // Make a link only from the user name, not all the 'published by' text
                if ( preg_match( '/^(.*)%s(.*)$/', $author_text, $matches ) ) {
                    $published_by = $matches[1] . '<a href="http://instagram.com/' . $elem['user_name'] . '" target="_blank">';
                    $published_by .= ( 'true' == $author_full_name ) ? $elem['caption_from'] : $elem['user_name'];
                    $published_by .= '</a>';
                    $published_by .= $matches[2];
                }
                else {
                    $published_by = $author_text;
                }

                $out .= '<div class="easy-instagram-thumbnail-author">';
                $out .= $published_by;
                $out .= '</div>';
            }

            if ( $caption_char_limit > 0 ) {
                $out .= '<div class="easy-instagram-thumbnail-caption">' . $caption_text . '</div>';
            }

            if ( NULL == $elem['caption_created_time'] ) {
                $elem_time = $elem['created_time'];
            }
            else {
                $elem_time = ( $elem['caption_created_time'] > $elem['created_time'] )
                    ? $elem['caption_created_time'] : $elem['created_time'];
            }
            
            if ( '' != $time_text ) {
                $out .= '<div class="easy-instagram-thumbnail-time">';
                
                if ( preg_match( '/^(.*)#T#(.*)$/', $time_text, $matches ) ) {
                    if ( '' != $time_format ) {
                        if ( '#R#' == $time_format ) { //Relative
                            $time_string = Easy_Instagram_Utils::relative_time( $elem_time );
                        }
                        else {
                            $time_string = strftime( $time_format, $elem_time );
                        }
                    }
                    else {
                        $time_string = '';
                    }

                    $time_string = $matches[1] . $time_string . $matches[2];
                }
                else {
                    $time_string = $time_text; //No interpolation
                }
                
                $out .= $time_string;
                
                $out .= '</div>';
            }

            $out .= '</div>';
            
            $crt++;
            if ( $crt >= $limit ) {
                break;
            }
        }

        return $out;
    }

    //================================================================

	static function generate_content( $params ) {
        $tag     = $params['tag'];
        $user_id = $params['user_id'];

		self::$load_scripts_and_styles = TRUE;

		if ( empty( $tag ) && empty( $user_id ) ) {
			return '';
		}

		$access_token = self::get_access_token();
		if ( empty( $access_token ) ) {
			return '';
		}

		$config = self::get_instagram_config();
		$instagram = new MC_Instagram_Connector( $config );
		$instagram->setAccessToken( $access_token );

        //Select which Instagram endpoint to use
		if ( ! empty( $user_id ) ) {
            $endpoint_id = $user_id;
            $endpoint_type = 'user';
		}
		else {
			if ( ! empty( $tag ) ) {
                $endpoint_id = $tag;
                $endpoint_type = 'tag';
			}
		}
		
		return self::_get_render_elements_for_ajax( $params );
	}

	//=========================================================================

	static function generate_content_ajax() {
		check_ajax_referer( 'easy-instagram-content-nonce', 'easy_instagram_content_security' );
	
        $tag     = $_GET['tag'];
        $user_id = $_GET['user_id'];
        $limit   = $_GET['limit'];

		$data = array( 'status' => 'ERROR' );

		if ( empty( $tag ) && empty( $user_id ) ) {
			echo json_encode( $data );
			exit;
		}

		$access_token = self::get_access_token();
		if ( empty( $access_token ) ) {
			echo json_encode( $data );
			exit;
		}

		$config = self::get_instagram_config();
		$instagram = new MC_Instagram_Connector( $config );
		$instagram->setAccessToken( $access_token );

        //Select which Instagram endpoint to use
		if ( ! empty( $user_id ) ) {
            $endpoint_id = $user_id;
            $endpoint_type = 'user';
		}
		else {
			if ( ! empty( $tag ) ) {
                $endpoint_id = $tag;
                $endpoint_type = 'tag';
			}
		}

        $instagram_elements = self::_get_data_for_user_or_tag( $instagram, $endpoint_id, $limit, $endpoint_type );

		$rendered = self::_get_render_elements( $instagram_elements, $_GET );
		$data['status'] = 'SUCCESS';
		$data['output'] = $rendered;
		echo json_encode( $data );
		exit;
	}

	//=====================================================================

	static function plugin_activation() {
		wp_schedule_event(
			time(),
			'hourly',//'daily',
			'easy_instagram_clear_cache_event'
		);
	}

	//=====================================================================

    static function debug_cron( $schedules ) {
        $schedules['every_five_minutes'] = array(
            'interval' => 300, // 5 min in seconds
            'display'  => __( 'Every Five Minutes', 'Easy_Instagram' ),
        );
 
        return $schedules;
    }

	//=====================================================================

	static function plugin_deactivation() {
		wp_clear_scheduled_hook( 'easy_instagram_clear_cache_event' );
	}

    //================================================================

    static function clear_cache_event_action() {
        Easy_Instagram_Cache::clear_expired_cache_action();
    }
}
