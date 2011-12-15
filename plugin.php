<?php 

/*
	Plugin Name: Foomark WordPress Widget
	Plugin URI: http://www.slash25.com
	Description: Integrates your Foomarks into a widget.
	Author: Pat Ramsey
	Author URI: http://www.slash25.com
	
	Version: 1.0
	
	License: GNU General Public License v2.0
	License URI: http://www.opensource.org/licenses/gpl-license.php
*/

define( 'FOOMARK_SETTINGS_FIELD', 'foomark-settings' );

// Loads admin settings functions via Init Hook
add_action( 'init', 'FoomarkSettingsInit', 15 );
function FoomarkSettingsInit() {

	function foomark_default_options() {
		$options = array(
			'foomark_username' 	=> '',
			'foomark_count' 	=> ''
		);
		return apply_filters( 'foomark_default_options', $options );
	}
	
	
	/* Register our settings and add the options to the database
	------------------------------------------------------------ */
	add_action( 'admin_init', 'foomark_register_settings' );
	function foomark_register_settings() {
		register_setting( FOOMARK_SETTINGS_FIELD, FOOMARK_SETTINGS_FIELD );
		add_option( FOOMARK_SETTINGS_FIELD, foomark_default_options() );
	}
	
	/* Admin notices for when options are saved/reset
	------------------------------------------------------------ */
	add_action( 'admin_notices', 'foomark_theme_settings_notice' );
	function foomark_theme_settings_notice() {
		if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != FOOMARK_SETTINGS_FIELD )
			return;
	
		if ( isset( $_REQUEST['reset'] ) && 'true' == $_REQUEST['reset'] )
			echo '<div id="message" class="updated"><p><strong>' . __( 'Settings reset.', 'foomark' ) . '</strong></p></div>';
		elseif ( isset( $_REQUEST['settings-updated'] ) && 'true' == $_REQUEST['settings-updated'] )
			echo '<div id="message" class="updated"><p><strong>' . __( 'Settings saved.', 'foomark' ) . '</strong></p></div>';
	}
	
	// Translations
	load_plugin_textdomain('foomark', false, basename( dirname( __FILE__ ) ) . '/languages' );

	/* Register our theme options page
	------------------------------------------------------------ */
	add_action( 'admin_menu', 'foomark_theme_options' );
	function foomark_theme_options() {
		global $_foomark_settings_pagehook;
		$_foomark_settings_pagehook = add_submenu_page( 'options-general.php', 'Foomark', 'Foomark', 'edit_theme_options', FOOMARK_SETTINGS_FIELD, 'foomark_theme_options_page' );
	
		//add_action( 'load-'.$_foomark_settings_pagehook, 'foomark_settings_styles' );
		add_action( 'load-'.$_foomark_settings_pagehook, 'foomark_settings_scripts' );
		add_action( 'load-'.$_foomark_settings_pagehook, 'foomark_settings_boxes' );
	}
	
	// Loads the scripts required for the settings page
	function foomark_settings_scripts() {
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
	}
	
	// Setup our metaboxes
	function foomark_settings_boxes() {
		global $_foomark_settings_pagehook;
		add_meta_box( 'foomark-username', __( 'Foomark Username', 'foomark' ), 'foomark_box', $_foomark_settings_pagehook, 'main' );
	}

	
	// Add our options metabox
	function foomark_box() {  
		$foo_vars = get_option( FOOMARK_SETTINGS_FIELD );
		//var_export($foo_vars['foomark_username']);
		?>
		<div class="form-table foomark-username">
			<p>
				<label for="<?php echo FOOMARK_SETTINGS_FIELD; ?>[foomark_username]"><?php printf( __( 'Enter your foomark username.', 'foomark' ), '<br />' ); ?></label>
			</p><p>
				<input type="text" id="<?php echo FOOMARK_SETTINGS_FIELD; ?>[foomark_username]" name="<?php echo FOOMARK_SETTINGS_FIELD; ?>[foomark_username]" value="<?php echo esc_attr ( $foo_vars['foomark_username'] ); ?>" style="width:60%;" />
			</p>
			<p>
				<label for="<?php echo FOOMARK_SETTINGS_FIELD; ?>[foomark_count]"><?php printf( __( 'Enter the number of foomarks you want to return (leave empty for all).', 'foomark' ), '<br />' ); ?></label>
			</p><p>
				<input type="text" id="<?php echo FOOMARK_SETTINGS_FIELD; ?>[foomark_count]" name="<?php echo FOOMARK_SETTINGS_FIELD; ?>[foomark_count]" value="<?php echo esc_attr ( $foo_vars['foomark_count'] ); ?>" style="width:90px;" />
			</p>
		</div>	
	<?php }
	
	// Set the screen layout to one column
	add_filter( 'screen_layout_columns', 'foomark_settings_layout_columns', 10, 2 );
	function foomark_settings_layout_columns( $columns, $screen ) {
		global $_foomark_settings_pagehook;
		if ( $screen == $_foomark_settings_pagehook ) {
			$columns[$_foomark_settings_pagehook] = 1;
		}
		return $columns;
	}
	
	// Build our options page
	function foomark_theme_options_page() { 
		global $_foomark_settings_pagehook, $screen_layout_columns;
		$width = "width: 99%;";
		$hide2 = $hide3 = " display: none;";
		?>	
		
		<div id="foomark_settings" class="wrap metaboxes">
			<form method="post" action="options.php">
			
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<?php settings_fields( FOOMARK_SETTINGS_FIELD ); ?>
				
				<?php screen_icon( 'options-general' ); ?>
				
				<h2>
					<?php _e( 'Foomark WordPress Widget Settings' ); ?>
					<input type="submit" class="button-primary" value="<?php _e( 'Save Settings' ) ?>" />
				</h2>
			
				<div class="metabox-holder">
					<div class="postbox-container" style="<?php echo $width; ?>">
						<?php do_meta_boxes( $_foomark_settings_pagehook, 'main', null ); ?>
					</div>
				</div>
			</form>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $_foomark_settings_pagehook; ?>');
			});
			//]]>
		</script>
		<?php 
	}

}

//Shorten the titles if they're too long - this is used in the widget
/*
function s25_truncate_phrase($phrase, $max_characters) {
	$phrase = trim( $phrase );
	if ( strlen($phrase) > $max_characters ) {
		// Truncate $phrase to $max_characters + 1
		$phrase = substr($phrase, 0, $max_characters + 1);
		// Truncate to the last space in the truncated string.
		$phrase = trim(substr($phrase, 0, strrpos($phrase, ' ')));
	}
	return $phrase;
}
*/

include( dirname( __FILE__ ) .'/widget.php');