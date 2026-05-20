<?php
/**
 * Demo Installer content, One Click Demo Import plugin required
 * See: https://wordpress.org/plugins/one-click-demo-import/
 *
 * @package Pukeko
 * @since Pukeko 1.0.2
 * @version 1.0.0
 */

function ocdi_import_files() {
	return array(

		array(
			'import_file_name'          		=> 'Demo Pukeko',
			'categories'                 		=> array( 'Blog', 'Business' ),
			'local_import_file'            	=> trailingslashit( get_template_directory() ) . 'assets/demos/pukeko-demo-content.xml',
			'local_import_widget_file'     	=> trailingslashit( get_template_directory() ) . 'assets/demos/pukeko-widgets.wie',
			'local_import_customizer_file'	=> trailingslashit( get_template_directory() ) . 'assets/demos/pukeko-customizer.dat',
			'import_notice'									=> esc_html__( 'Make sure you have the Elementor Page Builder plugin installed, before importing this demo!', 'pukeko' ),
		),
	);
}

add_filter( 'pt-ocdi/import_files', 'ocdi_import_files' );
