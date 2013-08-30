<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Easy_Instagram_Utils {

	static function get_caption_text( $element, $caption_hashtags, $caption_char_limit ) {
		$caption_text = trim( $element['caption_text'] );

		// Remove only hashtags at the end of the caption
		$failsafe_count = 100;
		if ( 'false' == $caption_hashtags ) {
			do {
				$no_hashtags_text = $caption_text;
				$caption_text = preg_replace( '/\s+#[^\\s]+\s?$/', '', $no_hashtags_text );
				$failsafe_count--;
				if ( $failsafe_count < 0 ) {
					break;
				}
			} while ( $caption_text != $no_hashtags_text );

			$caption_text = trim( $caption_text );

			if ( preg_match( '/^#[^\\s]*$/', $caption_text ) ) {
				$caption_text = '';
			}
		}

		// Truncate caption
		if ( ( $caption_char_limit > 0 ) && ( strlen( $caption_text ) > $caption_char_limit ) ) {
			$caption_text = substr( $caption_text, 0, $caption_char_limit );
			$caption_text = substr( $caption_text, 0, strrpos( $caption_text, ' ' ) );
			if ( strlen( $caption_text ) > 0 ) {
				$caption_text .= ' ...';
			}
		}

		return $caption_text;
	}
    
    //================================================================

    static function get_usename_caption( $elem ) {
        $caption_from = '';
        $user_name = '';

        if ( isset( $elem->caption ) && isset( $elem->caption->from ) && isset( $elem->caption->from->username ) ) {
            $user_name = $elem->caption->from->username;
        }

        if ( isset( $elem->caption ) ) {
            $caption_text = isset( $elem->caption->text ) ? trim( $elem->caption->text ) : '';

            if ( isset( $elem->caption->from ) ) {
                if ( isset( $elem->caption->from->username ) ) {
                    $user_name = $elem->caption->from->username;
                }

                if ( isset( $elem->caption->from->full_name ) ) {
                    $caption_from = $elem->caption->from->full_name;
                }
                else {
                    $caption_from = $user_name;
                }
            }

            if ( empty( $user_name ) ) {
                if ( isset( $elem->user ) && isset( $elem->user->username ) ) {
                    $user_name = $elem->user->username;
                }
            }

            if ( empty( $caption_from ) ) {
                if ( isset( $elem->user ) ) {
                    if ( isset( $elem->user->full_name ) ) {
                        $caption_from = $elem->user->full_name;
                    }
                    
                    if ( empty( $caption_from ) ) {
                        $caption_from = $user_name;
                    }
                }
            }
            
            $caption_created_time = $elem->caption->created_time;
        }
        else {
            $caption_text = '';
            if ( isset( $elem->user ) && isset( $elem->user->username ) ) {
                $user_name = $elem->user->username;
            }

            if ( isset( $elem->user ) ) {
                if ( isset( $elem->user->full_name ) ) {
                    $caption_from = $elem->user->full_name;
                }
                
                if ( empty( $caption_from ) ) {
                    $caption_from = $user_name;
                }
            }
            $caption_created_time = NULL;
        }
        
        return array( $user_name, $caption_from, 
                      $caption_text, $caption_created_time );
    }

    //================================================================

	static function relative_time( $timestamp ) {
		$periods = array( 
			__( '1 second ago', 'Easy_Instagram' ),
			__( '1 minute ago', 'Easy_Instagram' ),
			__( '1 hour ago', 'Easy_Instagram' ),
			__( '1 day ago', 'Easy_Instagram' ),
			__( '1 week ago', 'Easy_Instagram' ),
			__( '1 month ago', 'Easy_Instagram' ),
			__( '1 year ago', 'Easy_Instagram' ),
			__( '1 decade ago', 'Easy_Instagram' )
		);
		$periods_plural = array( 
			__( '%s seconds ago', 'Easy_Instagram' ),
			__( '%s minutes ago', 'Easy_Instagram' ),
			__( '%s hours ago', 'Easy_Instagram' ),
			__( '%s days ago', 'Easy_Instagram' ),
			__( '%s weeks ago', 'Easy_Instagram' ),
			__( '%s months ago', 'Easy_Instagram' ),
			__( '%s years ago', 'Easy_Instagram' ),
			__( '%s decades ago', 'Easy_Instagram' )
		);
		
		$difference = time() - $timestamp;		
		$lengths = array( '60', '60', '24', '7', '4.35', '12', '10' );
		
		for( $j = 0; $difference >= $lengths[$j]; $j++ ) {
			$difference /= $lengths[$j];
		}
		$difference = (int) round( $difference );

		$text = sprintf( _n( $periods[$j],	$periods_plural[$j], $difference, 'Easy_Instagram' ), $difference );
		
		return $text;
	}

}