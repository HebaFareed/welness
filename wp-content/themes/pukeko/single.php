<?php
/**
 * The template for displaying all single posts
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'template-parts/post/content', 'single' ); ?>

			<?php // If comments are open or we have at least one comment, load up the comment template.
			 if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/post/nav-single' ); ?>

		<?php endwhile; 	// End the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

	<?php if ( 'blog-only' != get_theme_mod( 'pukeko_blogsidebar', 'blog-only') ) : ?>
		<?php get_sidebar(); ?>
	<?php endif; ?>

<?php
get_footer();
