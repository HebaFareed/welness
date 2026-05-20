<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */

?>

<main id="main" class="site-main" role="main">

<section class="no-results not-found">
	<header class="entry-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'pukeko' ); ?></h1>
	</header><!-- .page-header -->

	<div class="entry-content col cf">
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p class="intro"><?php printf( wp_kses( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'pukeko' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

		<?php elseif ( is_search() ) : ?>

			<p class="intro"><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'pukeko' ); ?></p>
			<p><?php get_search_form(); ?></p>

			<?php else : ?>

			<p class="intro"><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'pukeko' ); ?></p>
			<p><?php get_search_form(); ?></p>

		<?php endif; ?>

	</div><!-- .entry-content -->
</section><!-- .no-results -->
