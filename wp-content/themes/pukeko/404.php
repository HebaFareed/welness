<?php
/**
 * The template for displaying 404 pages (not found)
 *
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<header class="entry-header cf">
					<h1 class="page-title"><?php esc_html_e( 'Oops! Page Not Found', 'pukeko' ); ?></h1>
				</header><!-- .page-header -->

				<div class="entry-content col cf">
					<p class="intro"><?php esc_html_e( 'We can&rsquo;t seem to find the page you&rsquo;re looking for. The link you clicked may be broken or the page may have been removed.', 'pukeko' ); ?></p>
					<p><?php esc_html_e( 'Maybe try a search instead?', 'pukeko' ); ?></p>

					<p><?php get_search_form(); ?></p>

				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
