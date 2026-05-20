<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area col s12 cf">

	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) : ?>
		<h2 class="comments-title f1">
			<?php
				printf( // WPCS: XSS OK.
					esc_html( _nx( '1 comment', '%1$s Comments', get_comments_number(), 'comments title', 'pukeko' ) ),
					number_format_i18n( get_comments_number() ),
					'<span>' . get_the_title() . '</span>'
				);
			?>
			<span><a href="#respond" class="btn-naked"><?php esc_html_e( 'Add New Comment', 'pukeko' ); ?></a></span>
		</h2><!-- .comments-title -->

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'avatar_size'	=> 56,
					'style'     	=> 'ol',
					'short_ping'	=> true,
					'format'    	=> 'html5',
					'callback' => 'pukeko_comments_callback',
				) );
			?>

		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'pukeko' ); ?></h2>
			<div class="nav-links cf">

				<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'pukeko' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'pukeko' ) ); ?></div>

			</div><!-- .nav-links -->
		</nav><!-- #comment-nav-below -->
		<?php
		endif; // Check for comment navigation.

	endif; // Check for have_comments().


	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'pukeko' ); ?></p>
	<?php
	endif;

	comment_form();
	?>

</div><!-- #comments -->
