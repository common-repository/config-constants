<?php
/*
Plugin Name: Config Constants
Plugin URI: http://www.wpgoplugins.com/
Description: Modify WP_DEBUG and other wp-config.php constants directly in the WordPress admin rather than manually editing them!
Version: 0.2
Author: David Gwyer
Author URI: http://www.wpgoplugins.com
*/

/*  Copyright 2009 David Gwyer (email : david@wpgoplugins.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* @todo
- Move all CSS to separate file and enqueue only on Plugin options page.
- add to class layout rather than functions
- only show the info icons on hover
- need to disable the functions to write to config file if it's not writable.
- Add some more constants from here: https://codex.wordpress.org/Editing_wp-config.php
- Could replace globals with class properties once we refactor plugin
- Add dismissable notice on plugins page that doesn't show again when dismissed.
 */

global $const_names, $const_codex_links;

/* List of constants. Re-order to change the checkbox display order. */
$const_names = array(
	'WP_DEBUG' =>				'chk_wp_debug_constant',
	'WP_DEBUG_LOG' =>			'chk_wp_debug_log_constant',
	'WP_DEBUG_DISPLAY' =>		'chk_wp_debug_display_constant',
	'SCRIPT_DEBUG' =>			'chk_script_debug_constant',
	'CONCATENATE_SCRIPTS' =>	'chk_concatenate_scripts_constant',
	'SAVEQUERIES' =>			'chk_savequeries_constant',
	'DISALLOW_FILE_MODS' =>		'chk_disallow_file_mods_constant',
	'DISALLOW_FILE_EDIT' =>		'chk_disallow_file_edit_constant',
	'WP_ALLOW_REPAIR' =>		'chk_wp_allow_repair_constant',
	'ALTERNATE_WP_CRON' =>		'chk_alternate_wp_cron_constant'
);

/* Constant Codex links. If a link isn't specified for a constant then no image/link is rendered. */
$const_codex_links = array(
	'WP_DEBUG' =>				'https://codex.wordpress.org/WP_DEBUG',
	'WP_DEBUG_LOG' =>			'https://codex.wordpress.org/Editing_wp-config.php#Debug',
	'WP_DEBUG_DISPLAY' =>		'https://codex.wordpress.org/Editing_wp-config.php#Debug',
	'SCRIPT_DEBUG' =>			'http://codex.wordpress.org/Debugging_in_WordPress#SCRIPT_DEBUG',
	'SAVEQUERIES' =>			'http://codex.wordpress.org/Editing_wp-config.php#Save_queries_for_analysis',
	'CONCATENATE_SCRIPTS' =>	'http://codex.wordpress.org/Editing_wp-config.php#Disable_Javascript_Concatenation',
	'DISALLOW_FILE_MODS' =>		'http://codex.wordpress.org/Editing_wp-config.php#Disable_Plugin_and_Theme_Update_and_Installation',
	'DISALLOW_FILE_EDIT' =>		'http://codex.wordpress.org/Editing_wp-config.php#Disable_the_Plugin_and_Theme_Editor',
	'WP_ALLOW_REPAIR' =>		'http://codex.wordpress.org/Editing_wp-config.php#Automatic_Database_Optimizing',
	'ALTERNATE_WP_CRON' =>		'https://codex.wordpress.org/Editing_wp-config.php#Alternative_Cron'
);

/* pcdm_ prefix is derived from [p]ress [c]oders [d]ebug [m]ode. */
register_activation_hook( __FILE__, 'pcdm_plugin_activated' );
register_uninstall_hook( __FILE__, 'pcdm_delete_plugin_options' );
add_action( 'admin_init', 'pcdm_init' );
add_action( 'admin_menu', 'pcdm_add_options_page' );
add_filter( 'plugin_action_links', 'pcdm_plugin_action_links', 10, 2 );

/* Delete options table entries ONLY when Plugin deactivated AND deleted. */
function pcdm_delete_plugin_options() {
	delete_option('pcdm_options');
}

function pcdm_plugin_activated() {
	pcdm_add_defaults();
	//pcdm_sync_config(); /* Sync wp-config.php with Plugin settings. */
}

function pcdm_add_defaults() {
	$tmp = get_option( 'pcdm_options' );

	if ( ! is_array( $tmp ) ) {
		delete_option( 'pcdm_options' ); // so we don't have to reset all the 'off' checkboxes
		$arr = array(
			'chk_wp_debug_constant' => '0',
			'chk_wp_debug_log_constant' => '0',
			'chk_wp_debug_display_constant' => '0',
			'chk_script_debug_constant' => '0',
			'chk_concatenate_scripts_constant' => '0',
			'chk_savequeries_constant' => '0',
			'chk_disallow_file_mods_constant' => '0',
			'chk_disallow_file_edit_constant' => '0',
			'chk_wp_allow_repair_constant' => '0',
			'chk_alternate_wp_cron_constant' => '0'
		);
		update_option( 'pcdm_options', $arr );
	}
}

/* Init Plugin options to white list our options. */
function pcdm_init(){
	register_setting( 'pcdm_plugin_options', 'pcdm_options' );
}

/* Add menu page. */
function pcdm_add_options_page() {
	add_options_page('Config Constants Options Page', 'Config Constants', 'manage_options', __FILE__, 'pcdm_render_form');
}

/* Draw the menu page itself. */
function pcdm_render_form() {
	?>
	<style>
		a:focus{ box-shadow: none;}
		.pcdm.dashicons { width: 32px; height: 32px; font-size: 32px; }
		.pcdm.dashicons-yes { color: #1cc31c; }
		.pcdm.dashicons-no { color: red; }
	</style>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Config Constants Options</h2>
		<p>Select which WordPress constants to enable in wp-config.php.</p>

		<?php
			global $pagenow, $const_names;
			/* Sync wp-config.php constants with plugin settings when plugin page visited but NOT when settings saved. */
			if(
				$pagenow == 'options-general.php'
				&& isset($_GET["page"])
				&& $_GET["page"] == "config-constants/config-constants.php"
				&& !isset($_GET["settings-updated"])
			) {
				/* Sync wp-config.php with Plugin settings in-case they have been manually updated. */
				pcdm_sync_config();
			}

			/* Sync plugin settings with wp-config.php ONLY plugin settings are updated. */
			if(
				$pagenow == 'options-general.php'
				&& isset($_GET["page"])
				&& $_GET["page"] == "config-constants/config-constants.php"
				&& isset($_GET["settings-updated"])
			) {
				/* Save the Plugin settings to wp-config.php. */
				pcdm_update_config();
			}
		?>

		<form method="post" action="options.php">
			<?php settings_fields('pcdm_plugin_options'); ?>
			<?php $options = get_option( 'pcdm_options' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Config File Write Status</th>
					<td>
						<?php
						if( pcdm_config_file_writable() ) { echo "<span style='vertical-align: middle;' class='pcdm dashicons dashicons-yes'></span>Writable. You're good to go!";
						} else {
							echo "<span style='vertical-align: middle;' class='pcdm dashicons dashicons-no'></span>File not accessible!";
						}
						?>
						<p class="description">Shows the writable status of <code>wp-config.php</code>.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row">Toggle WordPress Constants</th>
					<td>
						<?php /* Loop to output the options form check boxes. */ ?>
						<?php foreach( $const_names as $const_name => $chkbox_name ) : ?>

						<?php
							if ( !pcdm_config_file_writable() ) {
								$lbl_disable = 'style="opacity:0.5;cursor:default;"';
								$input_disable = "disabled=disabled";
							} else {
								$lbl_disable = '';
								$input_disable = '';
							}
						?>

						<label <?php echo $lbl_disable; ?>><input <?php echo $input_disable; ?>name="pcdm_options[<?php echo $chkbox_name; ?>]" value="1" type="checkbox" <?php if (isset($options[$chkbox_name])) { checked('1', $options[$chkbox_name]); } ?> /> <span><?php echo $const_name; ?></span></label><?php echo pcdm_codex_link($const_name); ?><br />

						<?php endforeach; ?>

						<p class="description">Changes to the active WordPress constants above will be reflected in <code>wp-config.php</code> automatically after saving.</p>
					</td>
				</tr>
			</table>
			<div style="margin-bottom:20px;">Click <a href="https://wpgoplugins.com/contact" target="_blank">here</a> to report any issues with the plugin, or to let us know if you'd like additional constants included.</div>
			<p style="margin-top:0;" class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>

		<div style="margin-top:15px;">
			<p style="margin-bottom:10px;">If you use this FREE Plugin on your website <b><em>please</em></b> consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7MUSN2J3HTRSJ" target="_blank">donation</a> to support continued development. Thank you.<span style="margin-left:5px;" class="dashicons dashicons-smiley"></span></p>
		</div>

		<div style="clear:both;">
			<span><a href="http://www.twitter.com/dgwyer" title="Follow us on Twitter" target="_blank"><img src="<?php echo plugins_url(); ?>/config-constants/images/twitter.png" /></a></span>
			<span><a href="https://www.facebook.com/wpgoplugins/" title="Our Facebook page" target="_blank"><img src="<?php echo plugins_url(); ?>/config-constants/images/facebook.png" /></a></span>
			<span><a href="https://www.youtube.com/channel/UCWzjTLWoyMgtIfpDgJavrTg" title="View our YouTube channel" target="_blank"><img src="<?php echo plugins_url(); ?>/config-constants/images/yt.png" /></a></span>
			<span><a style="text-decoration:none;" title="Need help with ANY aspect of WordPress? We're here to help!" href="https://wpgoplugins.com/need-help-with-wordpress/" target="_blank"><span style="margin-left:-2px;color:#d41515;font-size:39px;line-height:32px;width:39px;height:39px;" class="dashicons dashicons-sos"></span></a></span>
			<span style="margin-left:20px;"><input class="button" style="vertical-align:12px;" type="button" value="Visit Our Site" onClick="window.open('http://www.wpgoplugins.com')"></span>
			<span style="margin-left:3px;"><input class="button" style="vertical-align:12px;" type="button" value="Subscribe (free)" title="Signup today for all the latest plugin news and updates!" onClick="window.open('http://eepurl.com/bXZmmD')"></span>
		</div>

	</div>
	<?php
}

// Update wp-config.php file with plugin settings.
function pcdm_update_config(){

	$config_file = ABSPATH.'wp-config.php';

	if(  file_exists($config_file) ) {

		global $const_names;
		$options = get_option( 'pcdm_options' );
		$config_contents_arr = file($config_file);

		/* If Plugin options saved. */
		if( isset($_GET["settings-updated"]) && ($_GET["settings-updated"] == "true") )  {

			/* Initialize with null flags. */
			$const_flag_arr = array();
			foreach( $const_names as $const_name => $chkbox_name ) {
				$const_flag_arr[ $chkbox_name ] = null;
			}

			$added = array();
			foreach( $const_names as $const_name => $chkbox_name ) {

				$i = 0;
				$found = false;
				foreach( $config_contents_arr as $line ) {

					// Update wp-config.php constant if line begins with 'define' and contains the WordPress constant
					if ( substr( trim($line), 0, 6 ) === "define" && strpos( $line, $const_name ) !== false ) {

						// Update entry in wp-config.php
						$updated_constant_value = isset($options[$chkbox_name]) ? 'true' : 'false';
						$updated_constant = str_replace( array( 'true', 'false' ), $updated_constant_value, trim( $line ) );
						$config_contents_arr[$i] = $updated_constant . "\n";
						$const_flag_arr[$chkbox_name] = '1';
						$found = true;
					}
					$i++; // current index
				}

				// Add constant to wp-config.php
				if( false === $found ) {
					$updated_constant_value = isset($options[$chkbox_name]) ? 'true' : 'false';
					array_push( $added, "define('" . $const_name . "', " . $updated_constant_value . " );\n" );
					$const_flag_arr[ $chkbox_name ] = '1';
				}
			}

			// Add extra newline after the inserted constants (for aesthetics)
			if( !empty( $added ) ) array_push( $added, "\n" );

			// Find the line containing 'stop editing!' as an entry point to insert constants.
			$j = 0;
			$entry_point = 0;
			foreach( $config_contents_arr as $ln ) {
				if ( strpos( $ln, 'stop editing!' )  ) {
					$entry_point = $j;
					break;
				}
				$j++;
			}

			array_splice($config_contents_arr, $entry_point, 0, $added);

			/* Update wp-config.php. */
			$config_contents = implode( '', $config_contents_arr);
			file_put_contents( $config_file, $config_contents );
		}
	}
}

// Sync wp-config.php against plugin settings to update the settings from wp-config.php in-case they have been manually altered.
function pcdm_sync_config(){

	$config_file = ABSPATH.'wp-config.php';

	if(  file_exists($config_file) ) {

		global $const_names;
		$options = get_option( 'pcdm_options' );
		$config_contents = file_get_contents($config_file);

		/* Initialize with null flags. */
		$const_flag_arr = array();
		foreach( $const_names as $const_name => $chkbox_name ) {
			$const_flag_arr[$chkbox_name] = null;
			//$options[$chkbox_name] = '0';
		}

		/* Return all lines from wp-config.php containing 'define' statements. */
		preg_match_all( '/^.*\bdefine\b.*$/im', $config_contents, $matches );

		/* Turn $matches array into string for further preg_match() calls. */
		$matches_str = implode( '', $matches[0] );

		foreach( $const_names as $const_name => $chkbox_name ) {
			if( preg_match( '/\b'.$const_name.'\b/', $matches_str ) ) {
				$res = pcdm_array_find( $const_name, $matches[0] );

				if($res !== false) {
					$options[$chkbox_name] = preg_match( '/\btrue\b/', ($matches[0][$res]) ) ? '1' : '0';
					$const_flag_arr[$chkbox_name] = true;
				}
			}
		}
		update_option( 'pcdm_options', $options );
	}
}

function pcdm_config_file_writable() {
	$config_file = ABSPATH.'wp-config.php';
	if ( is_writable( $config_file ) ) {
		return true;
	} else {
		return false;
	}
}

/* Display a Settings link on the main Plugins page. */
function pcdm_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$posk_links = '<a href="'.get_admin_url().'options-general.php?page=config-constants/config-constants.php">'.__('Settings').'</a>';
		/* Make the 'Settings' link appear first. */
		array_unshift( $links, $posk_links );
	}

	return $links;
}

/* Custom version of the PHP function array_search() that allows partial array key matches. */
function pcdm_array_find($needle, $haystack, $search_keys = false) {

		if(!is_array($haystack)) return false;
        foreach($haystack as $key=>$value) {
            $what = ($search_keys) ? $key : $value;
            if(strpos($what, $needle)!==false) return $key;
        }
        return false;
}

/* Show icon linking*/
function pcdm_codex_link($const_name) {
	
	global $const_codex_links;

	$url = array_key_exists( $const_name, $const_codex_links ) ? $const_codex_links[$const_name] : '';

	if( !empty($url) )
		return '&nbsp;&nbsp;<a href="'.$url.'" target="_blank"><img title="WordPress Codex information" style="width:12px;height:12px;display:inline;vertical-align:text-bottom;" src="'.plugins_url().'/config-constants/images/info.png" /></a>';
	else
		return '';
}