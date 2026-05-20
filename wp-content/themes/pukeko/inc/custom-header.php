<?php
/**
 * Custom header implementation
 *
 * @link https://codex.wordpress.org/Custom_Headers
 *
 * @package Pukeko
 * @since 1.0.0
 * @version 1.0.0
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses pukeko_header_style()
 */
function pukeko_custom_header_setup() {

	/**
	 * Filter Pukeko custom-header support arguments.
	 *
	 * @since Pukeko 1.0
	 *
	 * @param array $args {
	 *     An array of custom-header support arguments.
	 *
	 *     @type string $default-image     		Default image of the header.
	 *     @type string $default_text_color     Default color of the header text.
	 *     @type int    $width                  Width in pixels of the custom header image. Default 954.
	 *     @type int    $height                 Height in pixels of the custom header image. Default 1300.
	 *     @type string $wp-head-callback       Callback function used to styles the header image and text
	 *                                          displayed on the blog.
	 *     @type string $flex-height     		Flex support for height of header.
	 * }
	 */
	add_theme_support( 'custom-header', apply_filters( 'pukeko_custom_header_args', array(
		'width'              => 2000,
		'height'             => 1200,
		'flex-height'        => true,
		'video'              => true,
		'wp-head-callback'   => 'pukeko_header_style',
	) ) );

}
add_action( 'after_setup_theme', 'pukeko_custom_header_setup' );

if ( ! function_exists( 'pukeko_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog.
 *
 * @see pukeko_custom_header_setup().
 */
function pukeko_header_style() {
	$header_text_color = get_header_textcolor();

	// If no custom options for text are set, let's bail.
	// get_header_textcolor() options: add_theme_support( 'custom-header' ) is default, hide text (returns 'blank') or any hex value.
	if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
		return;
	}

	// If we get this far, we have custom styles. Let's do this.
	?>
	<style id="pukeko-custom-header-styles" type="text/css">
	<?php
		// Has the text been hidden?
		if ( 'blank' === $header_text_color ) :
	?>
		.site-title,
		.site-description {
			position: absolute;
			clip: rect(1px, 1px, 1px, 1px);
		}
	<?php
		// If the user has set a custom color for the text use that.
		else :
	?>

	<?php endif; ?>
	</style>
	<?php
}
endif; // End of pukeko_header_style.

/**
 * Customize video play/pause button in the custom header.
 *
 * @param array $settings Video settings.
 */
function pukeko_video_controls( $settings ) {
	$settings['l10n']['play'] = '<span class="screen-reader-text">' . __( 'Play background video', 'pukeko' ) . '</span>' . pukeko_get_svg( array( 'icon' => 'play' ) );
	$settings['l10n']['pause'] = '<span class="screen-reader-text">' . __( 'Pause background video', 'pukeko' ) . '</span>' . pukeko_get_svg( array( 'icon' => 'pause' ) );
	return $settings;
}
add_filter( 'header_video_settings', 'pukeko_video_controls' );
