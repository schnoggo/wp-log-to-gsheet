<?php
// sample implementation from https://gist.github.com/danieliser/b40a232d53d85dce504a375d33b8cafa



/**
 * Return the default suggested privacy policy content.
 *
 * @return string The default policy content.
 */
function gl2_privacy_delarations() {
	return
	'<h2>' 
	. __( 'User interaction information is sent to a third party' , 'gl2') 
	. '</h2>' 
	.	'<p>' 
	. __( 'This site monitors what content is visible in a visitor\'s browser and sends that data to Google Analytics so that we can improve our content and layout. ' , 'gl2') . '</p>';
}
/**
 * Add the suggested privacy policy text to the policy postbox.
 */
function gl2_add_suggested_privacy_delarations() {
	global $gl2_vals;
	
	if ( function_exists('wp_add_privacy_policy_content')){
	
		$content = gl2_privacy_delarations();
		wp_add_privacy_policy_content( $gl2_vals['plugin_name'] , $content );
	}

}
// Not sure why but core registers their default text at priority 15, so to be after them (which I think would be the idea, you need to be 20+.
add_action( 'admin_init', 'gl2_add_suggested_privacy_delarations' );

