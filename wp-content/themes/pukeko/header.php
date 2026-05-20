<?php
/**
 * The header
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 *
 * @package Pukeko
 * @since 1.0.0
 * @version 1.0.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'pukeko' ); ?></a>

	<header id="masthead" class="site-header masthead nav-down" role="banner">
		<?php get_template_part( 'template-parts/header/header', 'content' ); ?>
	</header><!-- #masthead -->

	<?php if ( is_front_page() && ( has_header_image() ) ) : ?>
		<?php get_template_part( 'template-parts/header/header', 'image' ); ?>
	<?php endif; ?>

	<div class="content-wrap">
		<div id="content" class="site-content">
