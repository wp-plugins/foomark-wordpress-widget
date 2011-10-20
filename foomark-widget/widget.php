<?php 

add_action( 'widgets_init', 'FoomarksRegister' );

// Registers the Foomark widget
function FoomarksRegister() {
	register_widget( 'FoomarkWidget' );
}

// Foomark Widget Class
class FoomarkWidget extends WP_Widget {
	function FoomarkWidget() {
		$this->WP_Widget( 'foomark-widget', __( 'Foomarks', 'foomark' ), 
		array( 'classname' => 'foomarks', 'description' => __( 'Displays a list of your foomarks inside a widget area', 'foomark' ) ),
		array( 'width' => 200, 'height' => 250, 'id_base' => 'foomark-widget' )
		);
	}

	function save_settings( $settings ) {
		$settings['_multiwidget'] = 0;
		update_option( $this->option_name, $settings );
	}

	// display widget
	function widget( $args, $instance ) {
		$foo_vars = get_option( FOOMARK_SETTINGS_FIELD );
		extract( $args );
		$instance = wp_parse_args( (array) $instance, array('title' => __('Foomark', 'foomark' ) ) );
		
		echo $before_widget;
        $title = apply_filters('widget_title', $instance['title'] );

 		if ( !empty($instance['title']) )
				echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
		$username = esc_attr ( $foo_vars['foomark_username'] );
		$foocount = esc_attr ( $foo_vars['foomark_count'] );

		// Create the stream context
		$context = stream_context_create(array(
			'http' => array(
				'timeout' => 3      // Timeout in seconds
			)
		));
		// Fetch the URL's contents
		$string = file_get_contents('http://api.foomark.com/urls/list/?username=' . $username . '&sort=recent&count=' . $foocount . '&format=json', 0, $context);
		//var_export($string);die;
		// Check for empties
		if (!empty($string)) {
			$json_a = json_decode($string, true);
			echo '<ul class="foomark_list">';
				$i = 1; while ( $i <= $foocount ) {
					foreach ($json_a as $json_a[$i]) {
						$fmark_title = $json_a[$i]['title'];
						$fmark_url = $json_a[$i]['url'];
						$fmark_tags = $json_a[$i]['tags'];
						//if (strlen($fmark_title) > 42 ) {
							//$ftitle = s25_truncate_phrase($fmark_title, 38) . ' &#x2026';
						//} else {
							//$ftitle = $ftitle; 
						//}
						echo '<li><a href="' . $fmark_url . '">' . $fmark_title . '</a></li>';
						$i++;
					}
				}
			echo '</ul>';
			$file = dirname(__FILE__) . '/widget.php';
			$plugin_url = plugin_dir_url($file);
			echo '<p style="background:url(' . $plugin_url .'flag.png) no-repeat left top;padding:0 0 5px 20px;line-height: 16px;margin-top:1em;">';
			echo '<a href="http://www.foomark.com/' . $username . '">Follow me on Foomark</a></p>';
		} else {
			echo 'Something went horribly, horribly wrong. The little Foomarks will return when they can.'; // A terrible, terrible disaster occurs
		}

		echo $after_widget;

		function update($new_instance, $old_instance) {
			$new_instance['title'] = strip_tags( $new_instance['title'] );
			return $new_instance;
		}
	}
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array('title' => __('Foomark', 'foomark' ) ) ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		
	<?php 
	}

}
