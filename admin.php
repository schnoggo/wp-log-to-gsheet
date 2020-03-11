<?php
/**
 *
 *  namespace prefix gl2
 */

if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

// admin page:
// ======================================================
if ( is_admin() ){ // admin actions
	// http://codex.wordpress.org/Adding_Administration_Menus
	add_action('admin_init', 'gl2_options_init' );
	add_action( 'admin_menu', 'gl2_admin_menu' );
}


/**
 * Set up our globals and register our custom fields
 */
function gl2_options_init(){
  global $gl2_vals;

  add_settings_section( // section must always come first
    $gl2_vals['section_id'], // id (string) (required) String for use in the 'id' attribute of tags. 
    $gl2_vals['ui_strings']['options_title'], // title (string) (required) Title of the section. 
    'gl2_section_heading', // callback  (string) (required) Function that fills the section with the desired content. The function should echo its output. 
    $gl2_vals['pageslug'] // page
  );


    // Register the fields:
    foreach ($gl2_vals['option_fields'] as $id=>$field_options){
                
        add_settings_field( 
            $id, // id
            $field_options['name'], // title
            'gl2_field_callback', // callback (display)
            $gl2_vals['pageslug'] , // page (must match add_theme_page() or add_options_page in our case)
            $gl2_vals['section_id'], // section
            array(
            	'id' => $id,
            	'name' => $field_options['name'],
            	'note' => $field_options['note'],
            	'type' => $field_options['type'],
            	'default' => $field_options['default']
            ) // args 
        );
        //register_setting('plugnamepadm', $id, 'plugname_setting_sanitize');
        register_setting(
        	$gl2_vals['admin_group'], //option_group
        	$id, //option_name 
        	'gl2_setting_sanitize' //sanitize callback
        );
    }

}


/**
 * Set up our globals and register our custom fields
 */
function gl2_section_heading($args){

// section header stuff
//	echo 'section heading.';
/*
  echo "<p>id: $args[id]</p>\n";             // id: eg_setting_section
  echo "<p>title: $args[title]</p>\n";       // title: Example settings section in reading
  echo "<p>callback: $args[callback]</p>\n"; // callback: eg_setting_section_callback_function
  */
}

function gl2_setting_sanitize($input){
	global $gl2_vals;
	//$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);	

	return $input; // return validated input
}





/*
 * Draw an input field on a dashboard panel
 * 
 * @param array  https://developer.wordpress.org/reference/functions/add_settings_field/
 *
*/
function gl2_field_callback($args){
	global $gl2_vals;
	$ui = $gl2_vals['ui_strings'];
	$textwidth = 40;
	$err_msg = array(); // list of any errors encountered when processing the form
	$option_value = get_option($args['id'], $args['default']);
	
	if ( 'numeric' == $args['type'] ){$textwidth = 6;}

  switch ( $args['type'] ) {
		case 'bool':
			echo '<select id="' . $args['id'] . '" name="' . $args['id'] . '">';
			if ( 'true' == $option_value ){
				echo '<option value="true" selected>' . $ui['true'] . '</option>';
				echo '<option value="false">' . $ui['false'] . '</option>';
			} else {
				echo '<option value="true">' . $ui['true'] . '</option>';
				echo '<option value="false" selected>' . $ui['false'] . '</option>';
			}
			echo '</select>';
	
		break;
	
		case 'textarea':
		    echo '<textarea id="' . $args['id'] . '" '
			.'name="' . $args['id'] . '" '
			.'cols="' . $textwidth . '" rows="6" '
			.'>' . $option_value .  '</textarea>';
			
		break;
	
		case 'checkbox':
		//  if (is_string($option_value)){$option_value = json_decode($option_value);}
		  $possible_options = $gl2_vals['option_fields'][$args['id']]['options'];

		  echo '<fieldset>'; //<legend>' .  $args['name'] . '</legend>'
		    foreach($possible_options as $f_name => $f_label){
		      $input_dom_id = $args['id'] . '_' .  $f_name;
		      $checked = '';
		      if (in_array($f_name, $option_value)){
		         $checked = ' checked';
		      }
		      echo '<div>';
		      echo '<input type="checkbox" id="' . $input_dom_id . '"'
		      . ' name="' . $args['id'] . '[]"' // remember the [] to make it an array
		      . ' value="' . $f_name . '"'
		      . $checked .'>'
		      ;
		      echo '<label for="' . $input_dom_id. '">' . $f_label . '</label>';
          echo "</div>\n";
		    }
		  echo '</fieldset>';

			
		break;
		
		case 'secret':
		   echo '<input id="' . $args['id'] . '" '
			.'name="' . $args['id'] . '" '
			.'size="' . $textwidth . '" type="password" '
			.'value="' . $option_value . '" ' // $this_option
			.'class="secret-input" '
			.' />';
			echo '<span data-taget="' . $args['id'] .'" class="secret-toggle">show</span>';
		
		break;


		
    case 'manage_credentials':
      // First thing: handle file upload if that happened
      $json_op = array_key_exists('json_op',$_POST) ? $_POST['json_op'] : false;
    
      if ((false == $option_value) or ('false' == $option_value)){
        echo __("No credentials stored.", 'gl2');
      } else {
        echo '<p>Parse out email and display.</p>';
        echo "<pre>\n";
        echo print_r($option_value , true);
        echo "\n</pre>\n";
    
      }


      switch($json_op){
        case 'upload':
          if (!array_key_exists('upload', $_FILES)) {
            $err_msg[] = __('No file uploaded', 'gl2');
          } else {
            if ( count($_FILES) > 0 ){
              $overrides = array(
                'test_form' => false,
                'action' => $_SERVER['$REQUEST_URI'],
              );
			
              $upload_result = wp_handle_upload(
                $_FILES['upload'], // file record
                $overrides // https://codex.wordpress.org/Function_Reference/wp_handle_upload
              );            
              echo printf( __('<em>Uploading %s </em>', 'gl2'),  $_FILES['upload']['name']);
              
              if (array_key_exists('error', $upload_result)) {
                $err_msg[] = __('There was a problem uploading the file', 'gl2') . '(' . $upload_result['error'] . ')';
              } else {	
                // file has been uploaded. good to go: 
                // read contents of file:
                $json_file = fopen( $upload_info['file'], "r" );
                if (false == $json_file){
                  $err_msg[] = __('File not created on server', 'gl2');
                } else {
                  $contents  = fread( $json_file );
                  update_option($args['id'], $contents);
                }
              }              

              
            } else {
              $err_msg[] = __('No file specified', 'gl2');
            }
          }

        // no break - after processing upload draw the form
      
        default:
          echo '<div class="wrap"><em>Upload JSON credentials:</em><div>';
          echo '<form id="upload_form" action="'  . $_SERVER['REQUEST_URI'] . '" enctype="multipart/form-data" method="post" >
          <p><input name="upload" id="upload" type="file"  /></p>
          <p><input id="btnSubmit" type="submit" value="Upload JSON File" /></p>
          <input type="hidden" name="json_op" value="upload" />
          </form>
          ';
    
      }
      break;


		
		default:
		    echo '<input id="' . $args['id'] . '" '
			.'name="' . $args['id'] . '" '
			.'size="' . $textwidth . '" type="text" '
			.'value="' . $option_value . '" ' // $this_option
			.' />';
    }
    
	echo "<br />";
	echo '<p>' . $args['note'] . '</p>';
}



/*
 * Add a menu item to the Settings section of the WordPress dashboard
 * 
 * @global array $gl2_vals - all our global settings, including names of parameters
 *
*/
function gl2_admin_menu(){
	global $gl2_vals;
	$ui = $gl2_vals['ui_strings'];

	add_options_page(
		$gl2_vals['plugin_name'], // page_title
		$gl2_vals['menuname'], // menu_title
		'manage_options', // capability
		$gl2_vals['menuslug'] , // menu_slug
		'gl2_options_display' // function
	);
}



function gl2_options_display() {
	global $gl2_vals;
	$ui = $gl2_vals['ui_strings'];
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'gl2' ) );
	}
	echo '<div><h2>' . $gl2_vals['plugin_name'] . '</h2>';
	echo '<div class="wrap">';
	echo __('<p>This plugin is designed to be used with Google Data Studio.</p>', 'gl2');
	echo '</div>';

	echo '<form name="gl2_options_admin" method="post" action="options.php">';

	settings_fields($gl2_vals['admin_group'] );
	do_settings_sections($gl2_vals['pageslug'] );
	echo "<br />\n";
	submit_button(__('Save Changes', 'gl2'));
	echo '</form>';
	echo '</div>';

	
}