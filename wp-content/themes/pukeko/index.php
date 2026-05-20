<?php
/**
 * The main template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.2
 */

get_header(); ?>

<?php
	// Sticky Posts
	$sticky = get_option( 'sticky_posts' );
	$args_sticky = array(
		'post__in'  => $sticky
	);
	$query_sticky = new WP_Query($args_sticky);
?>

	<div id="primary" class="content-area">

		<?php if ( is_home() && ! is_front_page() ) : ?>
			<header>
				<h2 class="page-title screen-reader-text"><?php esc_html_e( 'Posts', 'pukeko' ); ?></h2>
			</header>
		<?php endif; ?>

		<main id="main" class="site-main" role="main">

		<div class="posts-container" id="posts-container">

			<?php if ( !is_paged() && $sticky ) : ?>

				<div class="sticky-container">

				<?php if ( $query_sticky->have_posts() ) :

					/* Start the Sticky Posts Loop */
					 while ( $query_sticky->have_posts() ) : $query_sticky->the_post();

						get_template_part( 'template-parts/post/content-sticky' );

					endwhile;

						/* Restore original Post Data */
						wp_reset_postdata();

				endif; ?>

					</div><!-- .sticky-container -->
				<?php endif; ?>


			<?php
			if ( have_posts() ) : ?>

			<?php
			/* Start the Standard Loop */
			while ( have_posts() ) : the_post();

				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				 get_template_part( 'template-parts/post/content' );

			endwhile; ?>

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
