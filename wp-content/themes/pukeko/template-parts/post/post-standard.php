<?php
/**
 * Displays standard posts on blog
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.3
 */

	$custom_post_excerpt = array(
	 'post_excerpt_length'	=> get_theme_mod( 'pukeko_post_excerpt_lengths' ),
	);
?>

<a class="entry-link" href="<?php the_permalink(); ?>">

	<?php if ( '' !== get_the_post_thumbnail() ) : ?>
		<div class="post-thumb">
				<?php the_post_thumbnail('pukeko-fi-classic'); ?>
		</div><!-- .post-thumb -->
	<?php endif; ?>

	<header class="entry-header">
		<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
		<div class="entry-summary">
			<?php if ( '15' == get_theme_mod( 'pukeko_post_excerpt_lengths' ) || '' == get_theme_mod( 'pukeko_post_excerpt_lengths' ) ) : ?>
				<?php echo pukeko_custom_excerpt_length(15); ?>
			<?php else : ?>
				<?php echo pukeko_custom_excerpt_length($custom_post_excerpt['post_excerpt_length']); ?>
			<?php endif; ?>
		</div><!-- .entry-summary -->
	</header><!-- .entry-header -->

	<footer class="entry-footer cf">

		<?php if ( get_avatar( get_the_author_meta( 'user_email' ) ) && '1' == get_theme_mod( 'pukeko_blogcards_authororcats') && '1' == get_theme_mod( 'pukeko_blogcards_authoravatar', '1' ) ): ?>
			<span class="author-avatar"><?php
			$author_bio_avatar_size = apply_filters( 'pukeko_author_bio_avatar_size', 112 );
			echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
			?></span>
		<?php endif; ?>

		<div class="entry-meta">
		<?php if ('1' != get_theme_mod( 'pukeko_blogcards_authororcats') or '1' != get_theme_mod( 'pukeko_displayauthor') ): ?>
			<span class="entry-cats"><?php pukeko_the_categories(); ?></span>
		<?php endif; ?>
		<?php if ('1' == get_theme_mod( 'pukeko_blogcards_authororcats') ): ?>
			<span class="author-name"><?php printf( get_the_author() ); ?></span>
		<?php endif; ?>
			<?php pukeko_entry_date_blog(); ?>
			<?php if ( comments_open() ) : ?>
				<span class="entry-comments">
				<?php comments_number( '<span class="leave-reply">' . esc_html__( 'Comments 0', 'pukeko' ) . '</span>', esc_html__( 'Comment 1', 'pukeko' ), esc_html__( 'Comments %', 'pukeko' ) ); ?>
				</span><!-- end .entry-comments -->
			<?php endif; // comments_open() ?>
			<?php pukeko_estimated_read_time(); ?>
	</div><!-- .entry-meta -->

	</footer><!-- .entry-footer -->
</a><!-- .entry-link -->

<?php pukeko_edit_link(); ?>
