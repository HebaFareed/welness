<?php
/**
 * Displays sticky posts on blog
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.2
 */

	$custom_sticky_post_excerpt = array(
	 'sticky_post_excerpt_length'	=> get_theme_mod( 'pukeko_sticky_excerpt_lengths' ),
	);
?>

<header class="entry-header">
	<div class="entry-meta">
			<?php pukeko_entry_meta(); ?>
	</div><!-- .entry-meta -->

	<?php the_title( '<h2 class="entry-title f2"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

	<div class="entry-summary">

		<?php if ( '40' == get_theme_mod( 'pukeko_sticky_excerpt_lengths' ) || '' == get_theme_mod( 'pukeko_sticky_excerpt_lengths' ) ) : ?>
			<?php echo pukeko_custom_excerpt_length(40); ?>
		<?php else: ?>
			<?php echo pukeko_custom_excerpt_length($custom_sticky_post_excerpt['sticky_post_excerpt_length']); ?>
		<?php endif; ?>

	</div><!-- .entry-summary -->

</header><!-- .entry-header -->

	<div class="post-thumb">
		<a href="<?php the_permalink(); ?>" class="post-thumb-img">
			<?php the_post_thumbnail('pukeko-fi-classic'); ?>
			<span class="sticky-more"><?php esc_html_e( 'Read More', 'pukeko' ); ?><?php echo pukeko_get_svg( array( 'icon' => 'arrow-right' ) ); ?></span>
		</a>
		<?php pukeko_edit_link(); ?>
	</div><!-- .post-thumb -->

	<footer class="entry-footer cf">

	<?php if ( get_avatar( get_the_author_meta( 'user_email' ) ) && '1' == get_theme_mod( 'pukeko_blogcards_authororcats') && '1' == get_theme_mod( 'pukeko_blogcards_authoravatar', '1' ) ): ?>
		<a class="author-avatar" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) ); ?>" rel="author">
		<?php
		$author_bio_avatar_size = apply_filters( 'pukeko_author_bio_avatar_size', 112 );
		echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
		?>
		</a>
	<?php endif; ?>

	<div class="entry-meta">
		<?php if ('1' != get_theme_mod( 'pukeko_blogcards_authororcats') ): ?>
			<span class="entry-cats"><?php pukeko_the_categories(); ?></span>
		<?php endif; ?>
		<?php if ('1' == get_theme_mod( 'pukeko_blogcards_authororcats') ): ?>
				<?php printf( "<a href='" .  esc_url(get_author_posts_url( get_the_author_meta( 'ID' ) )) . "' class='author-name' rel='author'>" . get_the_author() . "</a>" ); ?>
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
