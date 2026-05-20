<?php
/**
 * The template for displaying Author bios
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */
?>

<div class="authorbox-wrap cf">
	<div class="authorbox cf">
		<?php if ( get_avatar( get_the_author_meta( 'user_email' ) ) ): ?>
			<div class="author-pic">
				<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) ); ?>" class="author-pic-link" rel="author">
				<?php
				$author_bio_avatar_size = apply_filters( 'pukeko_author_bio_avatar_size', 320 );
				echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
				?>
				</a>
			</div><!-- .author-pic -->
		<?php endif; ?>
		<div class="authorbox-content">
			<h3 class="author-name f1"><?php esc_html_e( 'Author', 'pukeko' ); ?><span><?php printf( "<a href='" .  esc_url(get_author_posts_url( get_the_author_meta( 'ID' ) )) . "' rel='author'>" . get_the_author() . "</a>" ); ?></span></h3>
			<p class="author-bio"><?php the_author_meta( 'description' ); ?></p>
		</div><!-- end .authorbox-content -->
	</div><!-- end .authorbox -->
</div><!-- end .authorbox-wrap -->
