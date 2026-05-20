<?php
/**
 * pukeko Theme Customizer
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.2
 */

/**
 * Add postMessage support for site title, description and header textcolor + remove default color section.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function pukeko_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

	$wp_customize->remove_section( 'colors' );


/**
 * Theme Panels
 */
$wp_customize->add_panel( 'pukeko_themeoptions', array(
	'priority' 	                       => 1,
	'theme_supports' 	                 => '',
	'title' 	                         => esc_html__('Theme', 'pukeko'),
) );


/**
 * Theme Sections
 */
$wp_customize->add_section( 'pukeko_typography', array(
	'title' 		                       => esc_html__( 'Typography', 'pukeko' ),
	'priority' 	                       => 1,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_styles', array(
	'title' 		                       => esc_html__( 'Styles', 'pukeko' ),
	'priority' 	                       => 2,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_colors', array(
	'title' 		                       => esc_html__( 'Colors', 'pukeko' ),
	'priority' 	                       => 3,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_header', array(
	'title' 		                       => esc_html__( 'Header', 'pukeko' ),
	'priority' 	                       => 4,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_featured_header', array(
	'title' 		                       => esc_html__( 'Featured Header', 'pukeko' ),
	'priority' 	                       => 5,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_footer', array(
	'title' 		                       => esc_html__( 'Footer', 'pukeko' ),
	'priority' 	                       => 6,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_blog', array(
	'title' 		                       => esc_html__( 'Blog', 'pukeko' ),
	'priority' 	                       => 7,
	'panel' 					                 => 'pukeko_themeoptions',
) );

$wp_customize->add_section( 'pukeko_blogcards', array(
	'title' 		                       => esc_html__( 'Blog Cards', 'pukeko' ),
	'priority' 	                       => 8,
	'panel' 					                 => 'pukeko_themeoptions',
) );


/**
 * Theme Options - Site Identity
 */
// Theme Options - Site Identity - Hide Tagline only
$wp_customize->add_setting( 'pukeko_sitedescription', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_sitedescription', array(
	'label'								             => esc_html__( 'Hide Tagline only', 'pukeko' ),
	'section' 					               => 'title_tagline',
	'type' 			                       => 'checkbox',
	'priority'						             => 55,
) );


/**
 * Theme Options - Typography
 */
 // Theme Options - Typography - Headline Font
$wp_customize->add_setting( 'pukeko_headline_font', array(
	'default' 				                 => 'serif',
	'sanitize_callback' 	             => 'pukeko_sanitize_headline_font',
) );

$wp_customize->add_control( 'pukeko_headline_font', array(
	'label' 			                     => esc_html__( 'Headline Font', 'pukeko' ),
	'section' 			                   => 'pukeko_typography',
	'priority' 			                   => 1,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'serif'								       => esc_html__( 'serif', 'pukeko' ),
				'sansserif'		 			         => esc_html__( 'sans-serif', 'pukeko' ),
	),
) );

 // Theme Options - Typography - Headline Font Weight
$wp_customize->add_setting( 'pukeko_headline_font_weight', array(
	'default' 				                 => 'regular',
	'sanitize_callback' 	             => 'pukeko_sanitize_headline_font_weight',
) );

$wp_customize->add_control( 'pukeko_headline_font_weight', array(
	'label' 			                     => esc_html__( 'Headline Font Weight', 'pukeko' ),
	'section' 			                   => 'pukeko_typography',
	'priority' 			                   => 2,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'regular'								     => esc_html__( 'regular', 'pukeko' ),
				'bold'								       => esc_html__( 'bold', 'pukeko' ),
	),
) );

// Theme Options - Typography - Button Font Style
$wp_customize->add_setting( 'pukeko_links_font_style', array(
	'default' 				                 => 'lowercase',
	'sanitize_callback' 	             => 'pukeko_sanitize_links_font_style',
) );

$wp_customize->add_control( 'pukeko_links_font_style', array(
	'label' 			                     => esc_html__( 'Button and Menu Font Style', 'pukeko' ),
	'section' 			                   => 'pukeko_typography',
	'priority' 			                   => 3,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'lowercase'								   => esc_html__( 'case sensitive', 'pukeko' ),
				'uppercase'								   => esc_html__( 'uppercase', 'pukeko' ),
	),
) );


/**
 * Theme Options - Styles
 */
// Theme Options - Styles - Button Style
$wp_customize->add_setting( 'pukeko_btn_style', array(
	'default' 				                 => 'sharp',
	'sanitize_callback' 	             => 'pukeko_sanitize_btn_style',
) );

$wp_customize->add_control( 'pukeko_btn_style', array(
	'label' 			                     => esc_html__( 'Button Style', 'pukeko' ),
	'section' 			                   => 'pukeko_styles',
	'priority' 			                   => 1,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'sharp'					 	           => esc_html__( 'sharp', 'pukeko' ),
				'smooth'		 		             => esc_html__( 'rounded', 'pukeko' ),
				'round'		 			             => esc_html__( 'round', 'pukeko' ),
	),
) );

// Theme Options - Styles - Avatar Style
$wp_customize->add_setting( 'pukeko_avatar_style', array(
	'default' 				                 => 'square',
	'sanitize_callback' 	             => 'pukeko_sanitize_avatar_style',
) );

$wp_customize->add_control( 'pukeko_avatar_style', array(
	'label' 			                     => esc_html__( 'Avatar Style', 'pukeko' ),
	'section' 			                   => 'pukeko_styles',
	'priority' 			                   => 2,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'square'					 	         => esc_html__( 'square', 'pukeko' ),
				'rounded'		 			           => esc_html__( 'rounded', 'pukeko' ),
				'circle'		 			           => esc_html__( 'circle', 'pukeko' ),
	),
) );


/**
 * Theme Options - Colors
 */
// Theme Options - Styles - Background Color
$wp_customize->add_setting( 'pukeko_background_color' , array(
		'default' 			                 => '#ffffff',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_background_color', array(
	'label'								             => esc_html__( 'Background', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 1,
) ) );

// Theme Options - Styles - Link Color
$wp_customize->add_setting( 'pukeko_link_color' , array(
		'default' 			                 => '#1767F3',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_link_color', array(
	'label'								             => esc_html__( 'Primary Link', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 2,
) ) );

// Theme Options - Styles - Link Hover Color
$wp_customize->add_setting( 'pukeko_link_hover_color' , array(
		'default' 			                 => '#0542af',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_link_hover_color', array(
	'label'								             => esc_html__( 'Primary Link Hover', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 3,
) ) );

// Theme Options - Footer - Footer Background Color
$wp_customize->add_setting( 'pukeko_footer_bg_color' , array(
	'default' 			                   => '#000000',
	'sanitize_callback'	               => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_footer_bg_color', array(
	'label'								             => esc_html__( 'Footer Background', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 4,
	'settings'						             => 'pukeko_footer_bg_color',
) ) );

// Theme Options - Footer - Footer Text Color
$wp_customize->add_setting( 'pukeko_footer_text_color' , array(
		'default' 			                 => '#ffffff',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_footer_text_color', array(
	'label'								             => esc_html__( 'Footer Text', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 5,
) ) );

// Theme Options - Footer - Footer Link Color
$wp_customize->add_setting( 'pukeko_footer_link_color' , array(
		'default' 			                 => '#ffffff',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_footer_link_color', array(
	'label'								             => esc_html__( 'Footer Link', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 6,
) ) );

// Theme Options - Footer - Footer Link Color
$wp_customize->add_setting( 'pukeko_footer_linkhover_color' , array(
		'default' 			                 => '#1767f3',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_footer_linkhover_color', array(
	'label'								             => esc_html__( 'Footer Link Hover', 'pukeko' ),
	'section'							             => 'pukeko_colors',
	'priority'						             => 7,
) ) );


/**
 * Theme Options - Header
 */
// Theme Options - Header - Hide Search in Header
$wp_customize->add_setting( 'pukeko_headersearch', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_headersearch', array(
	'label'								             => esc_html__( 'Hide Search in Header', 'pukeko' ),
	'section' 					               => 'pukeko_header',
	'type' 			                       => 'checkbox',
	'priority'						             => 1,
) );

/**
 * Theme Options - Featured Header
 */
// Theme Options - Featured Header - Description
$wp_customize->add_setting( 'pukeko_hero_content', array(
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_hero_content', array(
	'description'					             => esc_html__( 'Add a subtitle, title, text and call to action buttons to your Featured Header.', 'pukeko' ),
	'section' 					               => 'pukeko_featured_header',
	'type' 			                       => 'hidden',
	'priority'						             => 1,
) );

// Theme Options - Featured Header - Subtitle
$wp_customize->add_setting( 'pukeko_hero_subtitle', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_hero_subtitle', array(
	'label'								             => esc_html__( 'Subtitle', 'pukeko' ),
	'section' 					               => 'pukeko_featured_header',
	'priority'						             => 2,
	'type' 			                       => 'text',
) );

// Theme Options - Featured Header - Title
$wp_customize->add_setting( 'pukeko_hero_title', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_hero_title', array(
	'label'								             => esc_html__( 'Title', 'pukeko' ),
	'section' 					               => 'pukeko_featured_header',
	'priority'						             => 3,
	'type' 			                       => 'text',
) );

// Theme Options - Featured Header - Text
$wp_customize->add_setting( 'pukeko_hero_text', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_hero_text', array(
	'label'								             => esc_html__( 'Text', 'pukeko' ),
	'section' 					               => 'pukeko_featured_header',
	'priority'						             => 4,
	'type' 			                       => 'textarea',
) );

// Theme Options - Featured Header - Primary Button Label
$wp_customize->add_setting( 'pukeko_hero_btn_text', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_hero_btn_text', array(
	'label'								             => esc_html__( 'Primary Button Label', 'pukeko' ),
	'section' 					               => 'pukeko_featured_header',
	'priority'						             => 5,
	'type' 			                       => 'text',
) );

// Theme Options - Featured Header - Primary Button URL
$wp_customize->add_setting( 'pukeko_hero_btn_url', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'esc_url_raw',
) );

$wp_customize->add_control( 'pukeko_hero_btn_url', array(
	'label'								             => esc_html__( 'Primary Button URL', 'pukeko' ),
	'section' 					               => 'pukeko_featured_header',
	'priority'						             => 6,
	'type' 			                       => 'text',
) );

// Theme Options - Featured Header - Content Alignment
$wp_customize->add_setting( 'pukeko_hero_alignment', array(
	'default' 				                 => 'hero-left',
	'sanitize_callback' 	             => 'pukeko_sanitize_hero_alignment',
) );

$wp_customize->add_control( 'pukeko_hero_alignment', array(
	'label' 			                     => esc_html__( 'Content Alignment', 'pukeko' ),
	'section' 			                   => 'pukeko_featured_header',
	'priority' 			                   => 7,
	'type' 			                       => 'select',
	'choices' 								         => array(
		'hero-left'				               => esc_html__( 'left', 'pukeko' ),
		'hero-center'					           => esc_html__( 'center', 'pukeko' ),
		'hero-right'		                 => esc_html__( 'right', 'pukeko' ),
	),
) );

// Theme Options - Featured Header - Text Color
$wp_customize->add_setting( 'pukeko_hero_text_color' , array(
		'default' 			                 => '#000000',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_hero_text_color', array(
	'label'								             => esc_html__( 'Text Color', 'pukeko' ),
	'section'							             => 'pukeko_featured_header',
	'priority'						             => 8,
) ) );

// Theme Options - Featured Header - Overlay Color
$wp_customize->add_setting( 'pukeko_hero_overlay_color' , array(
		'default' 			                 => '#000000',
		'sanitize_callback'	             => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_hero_overlay_color', array(
	'label'								             => esc_html__( 'Overlay Color', 'pukeko' ),
	'section'							             => 'pukeko_featured_header',
	'priority'						             => 9,
) ) );

// Theme Options - Featured Header - Overlay Opacity
$wp_customize->add_setting( 'pukeko_hero_overlay_opacity', array(
	'default' 			                   => '0',
	'sanitize_callback' 	             => 'pukeko_sanitize_opacity',
) );

$wp_customize->add_control( 'pukeko_hero_overlay_opacity', array(
		'label' 								         => __( 'Overlay Opacity', 'pukeko' ),
		'description' 					         => __( 'Set the opacity of the header overlay in percent.', 'pukeko' ),
		'section'								         => 'pukeko_featured_header',
		'priority'							         => 10,
		'type'									         => 'number',
		'input_attrs'                    => array(
						'min'                    => 0,
						'max'                    => 100,
						'step'                   => 10,
				),
) );


/**
 * Theme Options - Footer
 */
// Theme Options - Footer - Footer credit text
$wp_customize->add_setting( 'pukeko_credit', array(
	'default' 			                   => '',
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_credit', array(
	'label'								             => esc_html__( 'Footer credit text', 'pukeko' ),
	'description'					             => esc_html__( 'Customize the footer credit text (HTML is allowed).', 'pukeko' ),
	'section' 					               => 'pukeko_footer',
	'priority'						             => 3,
	'type' 			                       => 'textarea',
) );


/**
 * Theme Options - Blog
 */
// Theme Options - Blog - Blog Layout
$wp_customize->add_setting( 'pukeko_blogcolumns', array(
	'default' 				                 => 'twocolumn',
	'sanitize_callback' 	             => 'pukeko_sanitize_bloglayout',
) );

$wp_customize->add_control( 'pukeko_blogcolumns', array(
	'label' 			                     => esc_html__( 'Blog Layout', 'pukeko' ),
	'section' 			                   => 'pukeko_blog',
	'priority' 			                   => 1,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'onecolumn'					         => esc_html__( '1-column', 'pukeko' ),
				'twocolumn'		 	             => esc_html__( '2-column', 'pukeko' ),
				'threecolumn'				         => esc_html__( '3-column', 'pukeko' ),
	),
) );

// Theme Options - Blog - Blog Sidebar
$wp_customize->add_setting( 'pukeko_blogsidebar', array(
	'default' 				                 => 'blog-only',
	'sanitize_callback' 			         => 'pukeko_sanitize_blogsidebar',
) );

$wp_customize->add_control( 'pukeko_blogsidebar', array(
	'label' 			                     => esc_html__( 'Blog Sidebar', 'pukeko' ),
	'description'					             => esc_html__( 'Choose, where to show the blog sidebar.', 'pukeko' ),
	'section' 			                   => 'pukeko_blog',
	'priority' 			                   => 2,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'blog-only'					         => esc_html__( 'blog only', 'pukeko' ),
				'post-only'		 	             => esc_html__( 'posts only', 'pukeko' ),
				'blog-post'					         => esc_html__( 'blog and posts', 'pukeko' ),
	),
) );

// Theme Options - Blog - Post Details:
$wp_customize->add_setting( 'pukeko_postdetails', array(
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_postdetails', array(
	'label'								             => esc_html__( 'Post Details', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'hidden',
	'priority'						             => 3,
) );

// Theme Options - Blog - Display categories
$wp_customize->add_setting( 'pukeko_displaycategories', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaycategories', array(
	'label'								             => esc_html__( 'Display categories', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 4,
) );

// Theme Options - Blog - Display date
$wp_customize->add_setting( 'pukeko_displaydate', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaydate', array(
	'label'								             => esc_html__( 'Display date on blog', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 5,
) );

// Theme Options - Blog - Display date single post
$wp_customize->add_setting( 'pukeko_displaydate_singlepost', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaydate_singlepost', array(
	'label'								             => esc_html__( 'Display date on single post', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 6,
) );

// Theme Options - Blog - Display comments count
$wp_customize->add_setting( 'pukeko_displaycomments', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaycomments', array(
	'label'								             => esc_html__( 'Display comments count on blog', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 7,
) );

// Theme Options - Blog - Display comments count
$wp_customize->add_setting( 'pukeko_displaycomments_singlepost', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaycomments_singlepost', array(
	'label'								             => esc_html__( 'Display comments count on single post', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 8,
) );

// Theme Options - Blog - Display reading time
$wp_customize->add_setting( 'pukeko_displayreadtime', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displayreadtime', array(
	'label'								             => esc_html__( 'Display reading time on blog', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 9,
) );

// Theme Options - Blog - Display reading time
$wp_customize->add_setting( 'pukeko_displayreadtime_singlepost', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displayreadtime_singlepost', array(
	'label'								             => esc_html__( 'Display reading time on single post', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 10,
) );

// Theme Options - Blog - Display tags
$wp_customize->add_setting( 'pukeko_displaytags', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaytags', array(
	'label'								             => esc_html__( 'Display tags', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 11,
) );

// Theme Options - Blog - Display author
$wp_customize->add_setting( 'pukeko_displayauthor', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displayauthor', array(
	'label'								             => esc_html__( 'Display author', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 12,
) );

// Theme Options - Blog - Display related posts
$wp_customize->add_setting( 'pukeko_displayrelatedposts', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displayrelatedposts', array(
	'label'								             => esc_html__( 'Display related posts', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 13,
) );

// Theme Options - Blog - Featured Images Headline
$wp_customize->add_setting( 'pukeko_thumbs', array(
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_thumbs', array(
	'label'								             => esc_html__( 'Featured Images', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'hidden',
	'priority'						             => 14,
) );

// Theme Options - Blog - Featured Images on single posts
$wp_customize->add_setting( 'pukeko_displaythumbsposts', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displaythumbsposts', array(
	'label'								             => esc_html__( 'Display on single posts', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 15,
) );

// Theme Options - Blog - Display author bio Headline
$wp_customize->add_setting( 'pukeko_authorbio', array(
	'sanitize_callback'		             => 'wp_kses_post',
) );

$wp_customize->add_control( 'pukeko_authorbio', array(
	'label'								             => esc_html__( 'Author Bio', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'hidden',
	'priority'						             => 16,
) );

// Theme Options - Blog - Display author bio on single posts
$wp_customize->add_setting( 'pukeko_displayauthorbio', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_displayauthorbio', array(
	'label'								             => esc_html__( 'Display on single posts', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'type' 			                       => 'checkbox',
	'priority'						             => 17,
) );

// Theme Options - Blog Cards - Excerpt Text Length
$wp_customize->add_setting( 'pukeko_post_excerpt_lengths', array(
	'default' 			                   => '15',
	'sanitize_callback'		             => 'pukeko_sanitize_number_absint',
) );

$wp_customize->add_control( 'pukeko_post_excerpt_lengths', array(
	'label'								             => esc_html__( 'Post Excerpt Length', 'pukeko' ),
	'description'					             => esc_html__( 'Choose number of words.', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'priority'						             => 18,
	'type'								             => 'number'
) );

// Theme Options - Blog - Sticky Post Excerpt Length
$wp_customize->add_setting( 'pukeko_sticky_excerpt_lengths', array(
	'default' 			                   => '40',
	'sanitize_callback'		             => 'pukeko_sanitize_number_absint',
) );

$wp_customize->add_control( 'pukeko_sticky_excerpt_lengths', array(
	'label'								             => esc_html__( 'Sticky Post Excerpt Length', 'pukeko' ),
	'description'					             => esc_html__( 'Choose number of words.', 'pukeko' ),
	'section' 					               => 'pukeko_blog',
	'priority'						             => 19,
	'type'								             => 'number'
) );

/**
 * Theme Options - Blog Cards
 */
 // Theme Options - Blog Cards - Text Color
 $wp_customize->add_setting( 'pukeko_blogcards_textcolor' , array(
	 'default' 			 	                 => '#000000',
	 'sanitize_callback'	 	           => 'sanitize_hex_color',
 ) );

 $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_blogcards_textcolor', array(
	 'label'								           => esc_html__( 'Text Color', 'pukeko' ),
	 'section'							           => 'pukeko_blogcards',
	 'priority'						             => 1,
	 'settings'						             => 'pukeko_blogcards_textcolor',
 ) ) );

// Theme Options - Blog Cards - Background Color
$wp_customize->add_setting( 'pukeko_blogcards_bgcolor' , array(
	'default' 			                   => '#ffffff',
	'sanitize_callback'	               => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_blogcards_bgcolor', array(
	'label'								             => esc_html__( 'Background Color', 'pukeko' ),
	'section'							             => 'pukeko_blogcards',
	'priority'						             => 2,
	'settings'						             => 'pukeko_blogcards_bgcolor',
) ) );

// Theme Options - Blog Cards - Hover Background Color
$wp_customize->add_setting( 'pukeko_blogcards_bgcolor_hover' , array(
	'default' 			                   => '#ffffff',
	'sanitize_callback'	               => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'pukeko_blogcards_bgcolor_hover', array(
	'label'								             => esc_html__( 'Background Hover Color', 'pukeko' ),
	'section'							             => 'pukeko_blogcards',
	'priority'						             => 3,
	'settings'						             => 'pukeko_blogcards_bgcolor_hover',
) ) );

// Theme Options - Blog Cards - Shadow default
$wp_customize->add_setting( 'pukeko_blogcards_shadow', array(
	'default' 				                 => 'blogcards-radius-none',
	'sanitize_callback' 	             => 'pukeko_sanitize_blogcards_shadow',
) );

$wp_customize->add_control( 'pukeko_blogcards_shadow', array(
	'label' 			                     => esc_html__( 'Shadow', 'pukeko' ),
	'section' 			                   => 'pukeko_blogcards',
	'priority' 			                   => 4,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'blogcards-shadow-none'			=> esc_html__( 'none', 'pukeko' ),
				'blogcards-shadow-s'		    => esc_html__( 'small', 'pukeko' ),
				'blogcards-shadow-m'		    => esc_html__( 'medium', 'pukeko' ),
				'blogcards-shadow-l'		    => esc_html__( 'large', 'pukeko' ),
	),
) );

// Theme Options - Blog Cards - Shadow hover
$wp_customize->add_setting( 'pukeko_blogcards_shadow_hover', array(
	'default' 				                 => 'blogcards-shadow-l',
	'sanitize_callback' 	             => 'pukeko_sanitize_blogcards_shadow',
) );

$wp_customize->add_control( 'pukeko_blogcards_shadow_hover', array(
	'label' 			                     => esc_html__( 'Shadow Hover', 'pukeko' ),
	'section' 			                   => 'pukeko_blogcards',
	'priority' 			                   => 5,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'blogcards-shadow-none'			=> esc_html__( 'none', 'pukeko' ),
				'blogcards-shadow-s'		    => esc_html__( 'small', 'pukeko' ),
				'blogcards-shadow-m'		    => esc_html__( 'medium', 'pukeko' ),
				'blogcards-shadow-l'		    => esc_html__( 'large', 'pukeko' ),
	),
) );

// Theme Options - Blog Cards - Border Radius
$wp_customize->add_setting( 'pukeko_blogcards_borderradius', array(
	'default' 				                 => 'blogcards-radius-none',
	'sanitize_callback' 	             => 'pukeko_sanitize_blogcards_borderradius',
) );

$wp_customize->add_control( 'pukeko_blogcards_borderradius', array(
	'label' 			                     => esc_html__( 'Border Radius', 'pukeko' ),
	'section' 			                   => 'pukeko_blogcards',
	'priority' 			                   => 6,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'blogcards-radius-none'			=> esc_html__( 'none', 'pukeko' ),
				'blogcards-radius-s'		    => esc_html__( 'small', 'pukeko' ),
				'blogcards-radius-m'		    => esc_html__( 'medium', 'pukeko' ),
				'blogcards-radius-l'		    => esc_html__( 'large', 'pukeko' ),
	),
) );

// Theme Options - Blog Cards - Text Alignment
$wp_customize->add_setting( 'pukeko_blogcards_textalignment', array(
	'default' 				                 => 'blogcards-text-left',
	'sanitize_callback' 	             => 'pukeko_sanitize_blogcards_textalignment',
) );

$wp_customize->add_control( 'pukeko_blogcards_textalignment', array(
	'label' 			                     => esc_html__( 'Text Alignment', 'pukeko' ),
	'section' 			                   => 'pukeko_blogcards',
	'priority' 			                   => 7,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'blogcards-text-left'		     => esc_html__( 'left', 'pukeko' ),
				'blogcards-text-center'	     => esc_html__( 'center', 'pukeko' ),
	),
) );

// Theme Options - Blog Cards - Hover Effect
$wp_customize->add_setting( 'pukeko_blogcards_hovereffect', array(
	'default' 				                 => 'blogcards-movein',
	'sanitize_callback' 	             => 'pukeko_sanitize_blogcards_hovereffect',
) );

$wp_customize->add_control( 'pukeko_blogcards_hovereffect', array(
	'label' 			                     => esc_html__( 'Hover Effect', 'pukeko' ),
	'section' 			                   => 'pukeko_blogcards',
	'priority' 			                   => 8,
	'type' 			                       => 'select',
	'choices' 								         => array(
				'blogcards-movein'				=> esc_html__( 'move in', 'pukeko' ),
				'blogcards-moveup'	     			=> esc_html__( 'move up', 'pukeko' ),
	),
) );

// Theme Options - Blog Cards - Display author instead of cats
$wp_customize->add_setting( 'pukeko_blogcards_authororcats', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_blogcards_authororcats', array(
	'label'								             => esc_html__( 'Display author instead of categories', 'pukeko' ),
	'section' 					               => 'pukeko_blogcards',
	'type' 			                       => 'checkbox',
	'priority'						             => 9,
) );

// Theme Options - Blog Cards - Display author avatar
$wp_customize->add_setting( 'pukeko_blogcards_authoravatar', array(
	'default' 			                   => '1',
	'sanitize_callback'		             => 'pukeko_sanitize_checkbox',
) );

$wp_customize->add_control( 'pukeko_blogcards_authoravatar', array(
	'label'								             => esc_html__( 'Display author avatar', 'pukeko' ),
	'section' 					               => 'pukeko_blogcards',
	'type' 			                       => 'checkbox',
	'priority'						             => 10,
) );



}
add_action( 'customize_register', 'pukeko_customize_register' );


/**
 * Sanitize Checkboxes.
 */
function pukeko_sanitize_checkbox( $input ) {
	if ( 1 == $input ) {
		return true;
	} else {
		return '';
	}
}

/**
 * Sanitize Headline Font
 */
 function pukeko_sanitize_headline_font( $input ) {
	 $valid = array( 'serif', 'sansserif' );

	 if ( in_array( $input, $valid, true ) ) {
		 return $input;
	 }

	 return 'serif';
 }

/**
	* Sanitize Headline Font Weight
*/
	function pukeko_sanitize_headline_font_weight( $input ) {
		$valid = array( 'regular', 'bold' );

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return 'regular';
	}

/**
* Sanitize Links Font Style
*/
function pukeko_sanitize_links_font_style( $input ) {
$valid = array( 'lowercase', 'uppercase' );

if ( in_array( $input, $valid, true ) ) {
return $input;
}

return 'lowercase';
}

/**
 * Sanitize Button Style
 */
 function pukeko_sanitize_btn_style( $input ) {
	 $valid = array( 'sharp', 'smooth', 'round' );

	 if ( in_array( $input, $valid, true ) ) {
		 return $input;
	 }

	 return 'sharp';
 }

/**
* Sanitize Avatar Style
*/
function pukeko_sanitize_avatar_style( $input ) {
	$valid = array( 'square', 'rounded', 'circle' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'square';
}

/**
 * Sanitize Hero Text Alignment
 */
 function pukeko_sanitize_hero_alignment( $input ) {
	 $valid = array( 'hero-left', 'hero-center', 'hero-right' );

	 if ( in_array( $input, $valid, true ) ) {
		 return $input;
	 }

	 return 'hero-left';
 }

 /**
	* Sanitize Opacity
	*/
	function pukeko_sanitize_opacity( $input ) {
		$valid = array( '0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100' );

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return '0';
	}

/**
 * Sanitize Blog Layout
 */
 function pukeko_sanitize_bloglayout( $pukeko_blogcolumns ) {
	 if ( ! in_array( $pukeko_blogcolumns, array( 'onecolumn', 'twocolumn', 'threecolumn' ) ) ) {
		 $pukeko_blogcolumns = 'twocolumn';
	 }
	 return $pukeko_blogcolumns;
 }

/**
 * Sanitize Blog Sidebar.
 */
function pukeko_sanitize_blogsidebar( $input ) {
	$valid = array( 'blog-only', 'blog-post', 'post-only' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'blog-post';
}

/**
 * Sanitize Posts Display
 */
 function pukeko_sanitize_postsdisplay( $pukeko_blogdisplay ) {
	 if ( ! in_array( $pukeko_blogdisplay, array( 'post-excerpt', 'full-post' ) ) ) {
		 $pukeko_blogdisplay = 'post-excerpt';
	 }
	 return $pukeko_blogdisplay;
 }

 /**
	* Sanitize Numbers
	*/
 function pukeko_sanitize_number_absint( $number, $setting ) {
	// Ensure $number is an absolute integer (whole number, zero or greater).
	$number = absint( $number );

	// If the input is an absolute integer, return it; otherwise, return the default
	return ( $number ? $number : $setting->default );
}

/**
* Sanitize Blog Cards Text Alignment
*/
function pukeko_sanitize_blogcards_textalignment( $input ) {
	$valid = array( 'blogcards-text-left', 'blogcards-text-center' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'blogcards-text-left';
}

/**
* Sanitize Blog Cards Hover Effect
*/
function pukeko_sanitize_blogcards_hovereffect( $input ) {
	$valid = array( 'blogcards-movein', 'blogcards-moveup' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'blogcards-movein';
}

/**
* Sanitize Blog Cards Border Radius
*/
function pukeko_sanitize_blogcards_borderradius( $input ) {
	$valid = array( 'blogcards-radius-none', 'blogcards-radius-s', 'blogcards-radius-m', 'blogcards-radius-l' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'blogcards-radius-none';
}

/**
* Sanitize Blog Cards Shadow
*/
function pukeko_sanitize_blogcards_shadow( $input ) {
	$valid = array( 'blogcards-shadow-none', 'blogcards-shadow-s', 'blogcards-shadow-m', 'blogcards-shadow-l' );

	if ( in_array( $input, $valid, true ) ) {
		return $input;
	}

	return 'blogcards-shadow-none';
}

/**
 * Bind JS handlers to instantly live-preview changes.
 */
function pukeko_customize_preview_js() {
	wp_enqueue_script( 'pukeko_customizer', get_theme_file_uri( '/assets/js/customizer.js' ), array( 'customize-preview' ), '1.0', true );
}
add_action( 'customize_preview_init', 'pukeko_customize_preview_js' );
