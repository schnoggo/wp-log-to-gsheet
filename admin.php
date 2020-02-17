<?php
/**
 *
 *  namespace prefix gl2
 */



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
 * Draw an input fields in a dashboard panel
 * 
 * @param array  https://developer.wordpress.org/reference/functions/add_settings_field/
 *
*/
function gl2_field_callback($args){
	global $gl2_vals;
	$ui = $gl2_vals['ui_strings'];
	$textwidth = 40;
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