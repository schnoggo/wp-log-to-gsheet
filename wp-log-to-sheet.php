<?php
/*
 * Plugin Name: WP Log To Google Sheets
 * Plugin URI: https://lonmakes.com
 * Description: Log WordPress events that might effect site performance to Google Sheets
 * Version: 1.b0
 * Text Domain: gl2
 * Domain Path: /languages
 * Author: Lon Koenig 
 * Author URI: https://lonk.me/
 * License: MIT
*/

/*  
Copyright (c) 2020 Lon Koenig

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/


// define the options panel:
	$gl2_vals = array(
	  'field_prefix' => 'gl2_',
		'option_fields' => array(

      'gl2_tracked_events' => array(
        'name' => __('Events to log', 'gl2'),
        'note' => __('WordPress actions to record to the log', 'gl2'),
        'type' => 'checkbox', 
        'options' => array(
          'publish_page' => 'Publish Page',
          'publish_post' => 'Publish Post',
          'update_page' => 'Update Page',
          'update_post' => 'Update Post',          
          ),
          'default'=> array(
            'publish_page', 'update_page',
          ),
          
      
      ),
			'gl2_percentage' => array( 
				/* translators: Label for percentage value input field */
				'name' => __('Percentage Change', 'gl2'),
				'note' => __('If this value is not 0, a page/post update must change by this percentage to generate a log entry.', 'gl2'),
				'type' => 'numeric', 
				'default'=> '0',
			),
			
			'gl2_revision_comments' => array(
				/* translators: Label for true/false selector */
				'name' => __('Enable Revision Comments', 'gl2'),
				'note' => __('By enabling this option, the author/editor will be asked to provide a comment when updating a post or page.', 'gl2'),
				'type' => 'bool',
				'default' => 'false',
			),
			
      'gl2_saved_credentials' => array(
				'name' => __('Saved Credentials', 'gl2'),
				'note' => __('From the JSON file you got from Google.', 'gl2'),
				'type' => 'manage_credentials',
				'default' => 'false',
			),

			'gl2_sheet_id' => array( 
				'name' => 'Google spreadsheetID',
				'note' => __('The ID of your speadsheet (-- link to instructions --)', 'gl2'),
				'type' => 'string', 
				'default' => ''
			),
			
      'gl2_api_callback' => array( 
				'name' => 'OAUTH callback path',
				'note' => __('You will set this when you set up your Google OAUTH', 'gl2'),
				'type' => 'string', 
				'default' => 'gapi'
			),

		),
		'plugin_name' => 'WP Log To Google Sheets',
		'pageslug' => 'wp-log2s-admin',
		'menuslug' => 'wp-log2s-admin-m1',
		'menuname' => 'Log2Sheets', // shorter name for dashboard Settings menu
		'admin_group' => 'gl2_group',
		'section_id' => 'gl2_section',
		'version' => '1.b0',
		'ui_strings' => array(
			'true' => __('true', 'gl2'),
			'false' => __('false', 'gl2'),
			'options_title' => __('Options', 'gl2'),
			'show_secret' => __('Show Secret', 'gl2'),
		
		),
		'errors' => array(),
		'auth_client' => 0,
	);

/**
 * Load in any language files that we have setup
 */
function gl2_load_textdomain() {
    load_plugin_textdomain( 'gl2', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'gl2_load_textdomain' );
	



/*
 * Add any code we need to put in the <head> section
 * Output is directly echoed
 * 
 * @global array $gl2_vals - all our global settings, including names of parameters
 *
 */
function gl2_head(){
	global $gl2_vals;

  // echo out a <script> or <style> block
  echo "<!-- helpme -->\n";
  echo "<script>
  jQuery( document ).ready(function(){
  console.log('now?'); 
   
   });
</script>
";
}
add_action("wp_head", "gl2_head", 50);


/*
 * Enque (mark for loading) any external scripts or stylesheets
 * 
 */
function gl2_scripts() {

		$script_url = plugins_url( 'js/jquery-scrolldepth/jquery.scrolldepth.min.js', __FILE__ );
	//	wp_enqueue_script("jquery.scrolldepth", $script_url, array("jquery"));
	}

	add_action("wp_enqueue_scripts", "gl2_scripts");


require_once(plugin_dir_path( __FILE__ ) . 'utility.php'); // common functions

if ( is_admin() ) { // only need to load admin if we're looking at admin panel
	require_once(plugin_dir_path( __FILE__ ) . 'admin.php'); // back-end admin panels
	require_once(plugin_dir_path( __FILE__ ) . 'privacy.php'); // admin privacy information
	require_once(plugin_dir_path( __FILE__ ) . 'google.php'); // Glue for google sheets
}

