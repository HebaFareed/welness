<?php

add_action( 'customize_register', 'pukeko_ws_customize_register' );

function pukeko_ws_customize_register( $wp_customize ) {

	// Recomended actions
	global $pukeko_required_actions, $pukeko_recommended_plugins;

	$customizer_recommended_plugins = array();
	if ( is_array( $pukeko_recommended_plugins ) ) {
		foreach ( $pukeko_recommended_plugins as $k => $s ) {
			if ( $s['recommended'] ) {
				$customizer_recommended_plugins[ $k ] = $s;
			}
		}
	}

	$theme_slug = 'pukeko';

}

// Load the system checks ( used for notifications )
require get_template_directory() . '/inc/welcome-screen/class-mt-notify-system.php';

// Welcome screen
if ( is_admin() ) {
	global $pukeko_required_actions, $pukeko_recommended_plugins;
	$pukeko_recommended_plugins = array(
		'elementor' => array(
			'recommended' => false,
		),
		'one-click-demo-import' => array(
			'recommended' => false,
		),
		'mailchimp-for-wp' => array(
			'recommended' => false,
		),
		'contact-form-7' => array(
			'recommended' => false,
		),
		'jetpack' => array(
			'recommended' => false,
		),
	);
	/*
	 * id - unique id; required
	 * title
	 * description
	 * check - check for plugins (if installed)
	 * plugin_slug - the plugin's slug (used for installing the plugin)
	 *
	 */

	require get_template_directory() . '/inc/welcome-screen/class-pukeko-welcome.php';
}// End if().
