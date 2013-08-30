<?php
/*
 * Easy Instagram Widget
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Easy_Instagram_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'easy_instagram_widget_base',
			'Easy Instagram',
			array(
				'description' => __( 'Display one or more images from Instagram based on a tag or Instagram user id', 'Easy_Instagram' ),
				'class' => 'easy-instagram-widget'
			)
		);
	}

	//==========================================================================

 	public function form( $instance ) {
		list( $username, $current_user_id ) = Easy_Instagram::get_instagram_user_data(); 
	
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		else {
			$title = '';
		}

		if ( isset( $instance['type'] ) ) {
			$type = $instance['type'];
		}
		else {
			$type = 'tag';
		}

		if ( isset( $instance['value'] ) ) {
			$value = $instance['value'];
		}
		else {
			$value = '';
		}

		if ( isset( $instance['limit'] ) ) {
			$limit = $instance['limit'];
		}
		else {
			$limit = 1;
		}

		if ( $limit > Easy_Instagram::$max_images ) {
			$limit = Easy_Instagram::$max_images;
		}


		if ( isset( $instance['caption_hashtags'] ) ) {
			$caption_hashtags = $instance['caption_hashtags'];
		}
		else {
			$caption_hashtags = 'true';
		}

		if ( isset( $instance['caption_char_limit'] ) ) {
			$caption_char_limit = $instance['caption_char_limit'];
		}
		else {
			$caption_char_limit = Easy_Instagram::$default_caption_char_limit;
		}

		if ( isset( $instance['author_text'] ) ) {
			$author_text = $instance['author_text'];
		}
		else {
			$author_text = Easy_Instagram::$default_author_text;
		}

		if ( isset( $instance['author_full_name'] ) ) {
			$author_full_name = $instance['author_full_name'];
		}
		else {
			$author_full_name = 'false';
		}

		if ( isset( $instance['thumb_click'] ) ) {
			$thumb_click = $instance['thumb_click'];
		}
		else {
			$thumb_click = Easy_Instagram::$default_thumb_click;
		}

		if ( isset( $instance['time_text'] ) ) {
			$time_text = $instance['time_text'];
		}
		else {
			$time_text = Easy_Instagram::$default_time_text;
		}


		if ( isset( $instance['time_format'] ) ) {
			$time_format = $instance['time_format'];
		}
		else {
			$time_format = Easy_Instagram::$default_time_format;
		}

		if ( isset( $instance['thumb_size'] ) ) {
            list( $w, $h ) = Easy_Instagram::get_thumb_size_from_params( $instance['thumb_size'] );
            if ( $w < Easy_Instagram::$min_thumb_size || $h < Easy_Instagram::$min_thumb_size ) {
                $thumb_size = Easy_Instagram::$default_thumb_size;
            }
            else {
                $thumb_size = trim( $instance['thumb_size'] );
            }
        }
		else {
			$thumb_size = Easy_Instagram::$default_thumb_size;
		}

?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php _e( $title ); ?> " />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Type:', 'Easy_Instagram' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
			<?php $selected = ( 'tag' == $type ) ? 'selected="selected"' : ''; ?>
			<option value="tag" <?php echo $selected;?>><?php _e( 'Tag', 'Easy_Instagram' ); ?></option>

			<?php $selected = ( 'user_id' == $type ) ? 'selected="selected"' : ''; ?>
			<option value="user_id" <?php echo $selected;?>><?php _e( 'User ID', 'Easy_Instagram' ); ?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'value' ); ?>"><?php _e( 'ID/Hashtag Value:', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'value' ); ?>" name="<?php echo $this->get_field_name( 'value' ); ?>" value="<?php _e( $value ); ?> " />
		<span class='ei-field-info'><?php printf( __( 'Your User ID is: %s', 'Easy_Instagram' ), $current_user_id );?></span>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Images Count:', 'Easy_Instagram' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>">
		<?php for ( $i=1; $i<= Easy_instagram::$max_images; $i++ ): ?>

		<?php printf(
			'<option value="%s"%s>%s</option>',
        		$i,
        		selected( $limit, $i, false ),
        		$i );
		?>

		<?php endfor; ?>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'thumb_size' ); ?>"><?php _e( 'Thumbnail Size (in pixels, leave blank for default):', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'thumb_size' ); ?>" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>" value="<?php _e( $thumb_size ); ?> " />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'caption_hashtags' ); ?>"><?php _e( 'Show Caption Hashtags:', 'Easy_Instagram' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'caption_hashtags' ); ?>" name="<?php echo $this->get_field_name( 'caption_hashtags' ); ?>">
			<?php $selected = ( 'true' == $caption_hashtags ) ? 'selected="selected"' : ''; ?>
			<option value="true" <?php echo $selected;?>><?php _e( 'Yes', 'Easy_Instagram' ); ?></option>

			<?php $selected = ( 'false' == $caption_hashtags ) ? 'selected="selected"' : ''; ?>
			<option value="false" <?php echo $selected;?>><?php _e( 'No', 'Easy_Instagram' ); ?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'caption_char_limit' ); ?>"><?php _e( 'Caption Character Limit (0 for no caption):', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'caption_char_limit' ); ?>" name="<?php echo $this->get_field_name( 'caption_char_limit' ); ?>" value="<?php _e( $caption_char_limit ); ?> " />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'author_text' ); ?>"><?php _e( 'Author Text:', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'author_text' ); ?>" name="<?php echo $this->get_field_name( 'author_text' ); ?>" value="<?php _e( $author_text ); ?> " />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'author_full_name' ); ?>"><?php _e( 'Show Author\'s Full Name:', 'Easy_Instagram' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'author_full_name' ); ?>" name="<?php echo $this->get_field_name( 'author_full_name' ); ?>">
			<?php $selected = ( 'true' == $author_full_name ) ? 'selected="selected"' : ''; ?>
			<option value="true" <?php echo $selected;?>><?php _e( 'Yes', 'Easy_Instagram' ); ?></option>

			<?php $selected = ( 'false' == $author_full_name ) ? 'selected="selected"' : ''; ?>
			<option value="false" <?php echo $selected;?>><?php _e( 'No', 'Easy_Instagram' ); ?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'thumb_click' ); ?>"><?php _e( 'On Thumbnail Click:', 'Easy_Instagram' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'thumb_click' ); ?>" name="<?php echo $this->get_field_name( 'thumb_click' ); ?>">
		<?php foreach ( Easy_Instagram::get_thumb_click_options() as $key => $value ): ?>
			<?php $selected = ( $key == $thumb_click ) ? 'selected="selected"' : ''; ?>
			<option value="<?php echo $key;?>" <?php echo $selected;?>><?php echo $value; ?></option>
		<?php endforeach; ?>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'time_text' ); ?>"><?php _e( 'Time Text:', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'time_text' ); ?>" name="<?php echo $this->get_field_name( 'time_text' ); ?>" value="<?php _e( $time_text ); ?> " />
		</p>


		<p>
		<label for="<?php echo $this->get_field_id( 'time_format' ); ?>"><?php _e( 'Time Format:', 'Easy_Instagram' ); ?></label>
		<input type='text' class="widefat" id="<?php echo $this->get_field_id( 'time_format' ); ?>" name="<?php echo $this->get_field_name( 'time_format' ); ?>" value="<?php _e( $time_format ); ?> " />
		</p>
<?php

	}

	//==========================================================================

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']				= strip_tags( $new_instance['title'] );
		$instance['type']				= strip_tags( $new_instance['type'] );
		$instance['value']				= trim( strip_tags( $new_instance['value'] ) );
		$instance['limit']				= strip_tags( $new_instance['limit'] );
		$instance['caption_hashtags'] 	= $new_instance['caption_hashtags'];
		$instance['caption_char_limit'] = (int) $new_instance['caption_char_limit'];
		$instance['author_text']		= strip_tags( $new_instance['author_text'] );
		$instance['author_full_name']	= $new_instance['author_full_name'];
		$instance['thumb_click']		= $new_instance['thumb_click'];
		$instance['time_text']			= strip_tags( $new_instance['time_text'] );
		$instance['time_format']		= strip_tags( $new_instance['time_format'] );
        $instance['thumb_size']		    = strip_tags( $new_instance['thumb_size'] );
		return $instance;
	}

	//==========================================================================

	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		$tag = '';
		$user_id = '';
		$limit = 1;
		$caption_hashtags = 'true';
		$caption_char_limit = Easy_Instagram::$default_caption_char_limit;
		$author_text = Easy_Instagram::$default_author_text;
		$author_full_name = 'false';
		$thumb_click = '';
		$time_text = Easy_Instagram::$default_time_text;
		$time_format = Easy_Instagram::$default_time_format;
        $thumb_size = Easy_Instagram::$default_thumb_size;

		if ( 'tag' == $instance['type'] ) {
			$tag = trim( $instance['value'] );
			$user_id = '';
		}
		else {
			$tag = '';
			$user_id = $instance['value'];
		}

		if ( isset( $instance['limit'] ) ) {
			$limit = (int) $instance['limit'];
			if ( $limit > Easy_Instagram::$max_images ) {
				$limit = Easy_Instagram::$max_images;
			}
		}

		if ( isset( $instance['caption_hashtags'] ) ) {
			$caption_hashtags = $instance['caption_hashtags'];
		}

		if ( isset( $instance['caption_char_limit'] ) ) {
			$caption_char_limit = (int) $instance['caption_char_limit'];
		}

		if ( isset( $instance['author_text'] ) ) {
			$author_text = $instance['author_text'];
		}

		if ( isset( $instance['author_full_name'] ) ) {
			$author_full_name = $instance['author_full_name'];
		}

		if ( isset( $instance['thumb_click'] ) ) {
			$thumb_click = $instance['thumb_click'];
		}

		if ( isset( $instance['time_text'] ) ) {
			$time_text = $instance['time_text'];
		}

		if ( isset( $instance['time_format'] ) ) {
			$time_format = $instance['time_format'];
		}

		if ( isset( $instance['thumb_size'] ) ) {
			$thumb_size = $instance['thumb_size'];
		}

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

		$content = Easy_Instagram::generate_content( $params );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		echo $content;

		echo $after_widget;
	}

	//==========================================================================
}
