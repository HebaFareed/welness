<?php
/**
 * The template for displaying search results pages
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.2
 */

get_header(); ?>

	<div id="primary" class="content-area">

		<?php
		if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title f1"><span><?php esc_html_e( 'Search Results for:', 'pukeko' ); ?></span><?php echo get_search_query(); ?></h1>
			</header><!-- .page-header -->

			<main id="main" class="site-main" role="main">

			<div id="posts-container" class="posts-container cf">

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post(); ?>


			<?php	/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part( 'template-parts/post/content', get_post_format() ); ?>

			<?php endwhile; ?>


		<?php else : ?>

			<?php get_template_part( 'template-parts/post/content', 'none' ); ?>

		<?php endif; ?>

		</div><!-- .posts-container -->

		<?php the_posts_pagination( array(
			'next_text' => pukeko_get_svg( array( 'icon' => 'arrow-right' ) ) . '<span class="meta-nav">' . esc_html__( 'Older posts', 'pukeko' ) . '</span> ' .
			'<span class="screen-reader-text">' . esc_html__( 'Older posts', 'pukeko' ) . '</span> ',
			'prev_text' => pukeko_get_svg( array( 'icon' => 'arrow-left' ) ) . '<span class="meta-nav">' . esc_html__( 'Newer posts', 'pukeko' ) . '</span> ' .
			'<span class="screen-reader-text">' . esc_html__( 'Newer posts', 'pukeko' ) . '</span> ',
			'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'pukeko' ) . ' </span>',
		) ); ?>

		</main><!-- #main -->
	</div><!-- #primary -->

	<?php if ( have_posts() && 'blog-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only') || have_posts() && 'blog-post' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only') ) : ?>
	<?php get_sidebar(); ?>
	<?php endif; ?>

	<?php
	get_footer();
