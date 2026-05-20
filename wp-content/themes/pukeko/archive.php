<?php
/**
 * The template for displaying archive pages
 *
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.0
 */

get_header(); ?>

	<div id="primary" class="content-area">

		<?php
		if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
					the_archive_title( '<h1 class="page-title f1">', '</h1>' );

					if( ! is_author() ) {
						the_archive_description( '<div class="archive-description">', '</div>' );
					}
				?>

				<?php
				// Author bio.
				if ( is_author() ) :
					get_template_part( 'template-parts/post/authorbox' );
				endif;
				?>
			</header><!-- .page-header -->

			<main id="main" class="site-main" role="main">

				<div class="posts-container" id="posts-container">

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post(); ?>

			<?php
				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				get_template_part( 'template-parts/post/content', get_post_format() ); ?>

			<?php  endwhile; ?>

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

	<?php if ( 'blog-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only') || 'blog-post' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only') ) : ?>
	<?php get_sidebar(); ?>
	<?php endif; ?>

	<?php
	get_footer();
