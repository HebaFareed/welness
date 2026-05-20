<?php
/**
 * Template part for displaying single posts
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.2
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header cf">
		<?php if ( has_category() ) : ?>
			<div class="entry-cats l8">
				<?php the_category(' '); ?>
			</div><!-- end .entry-cats -->
			<?php endif; // has_category() ?>
		<?php the_title( '<h1 class="entry-title f2 l8">', '</h1>' ); ?>

		<div class="entry-meta cf">
			<?php pukeko_posted_on(); ?>
			<span class="entry-comments"><?php comments_popup_link( esc_html__( 'Leave a reply', 'pukeko' ), esc_html__( 'Comment 1', 'pukeko' ), esc_html__( 'Comments %', 'pukeko' ),'comments-link' ); ?></span>
			<?php pukeko_estimated_read_time(); ?>
			<?php if ( function_exists( 'sharing_display' ) ) : ?>
				<span id="sharing-header" class="sharing">
					<?php sharing_display( '', true ); ?>
				</span>
			<?php endif; ?>

		</div><!-- .entry-meta -->

		<?php pukeko_edit_link(); ?>

	</header><!-- .entry-header -->

	<?php if ( '' !== get_the_post_thumbnail() && '1' == get_theme_mod( 'pukeko_displaythumbsposts', '1' ) ) : ?>

		<?php if ( ! is_active_sidebar( 'sidebar-1' ) || 'blog-only' == get_theme_mod( 'pukeko_blogsidebar' ) ) : ?>
			<div class="post-thumb col cf">
					<?php the_post_thumbnail('pukeko-fi-hd'); ?>
			</div><!-- .post-thumb -->
			<?php else: ?>
				<div class="post-thumb col s12 cf">
						<?php the_post_thumbnail('pukeko-fi-classic'); ?>
				</div><!-- .post-thumb -->
		<?php endif; ?>

	<?php endif; ?>

	<div class="entry-content col cf">
		<?php
			the_content( sprintf(
				/* translators: %s: Name of current post. */
				wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'pukeko' ), array( 'span' => array( 'class' => array() ) ) ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'pukeko' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer col cf">

		<?php if ( function_exists( 'sharing_display' ) ) : ?>
			<span id="sharing-footer" class="sharing">
				<?php sharing_display( '', true ); ?>
			</span>
		<?php endif; ?>

		<?php pukeko_entry_meta(); ?>

		<?php
		// Author bio.
		if ( get_the_author_meta( 'description' ) ) :
			get_template_part( 'template-parts/post/authorbox' );
		endif;
		?>

	</footer><!-- .entry-footer -->
</article><!-- #post-## -->

<?php if ( '1' == get_theme_mod( 'pukeko_displayrelatedposts', '1' ) ) : ?>
	<?php get_template_part( 'template-parts/post/relatedposts' ); ?>
<?php endif; ?>
