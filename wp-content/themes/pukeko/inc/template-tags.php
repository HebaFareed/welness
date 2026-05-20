<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */

if ( ! function_exists( 'pukeko_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function pukeko_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$byline = sprintf(
		esc_html_x( '%s ', 'post author', 'pukeko' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	$posted_on = sprintf(
		esc_html_x( '%s', 'post date', 'pukeko' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	if ( is_single () || is_sticky () ) {

		if ( get_avatar( get_the_author_meta( 'ID' ) ) ):
			echo '<span class="author-avatar"><a class="author-avatar-link" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . get_avatar( get_the_author_meta( 'ID' ), 80 ) . '</a></span>';
		endif;
		echo '<span class="author-meta-info"><span class="byline"> ' . $byline . '</span><span class="posted-on">' .  $posted_on . '</span>';

	} else {

	echo '<span class="posted-on">' . $posted_on . '</span>';

	}

}
endif;


if ( ! function_exists( 'pukeko_entry_date_blog' ) ) :
/**
 * Prints HTML with information for the current post-date/time and number of comments.
 */
function pukeko_entry_date_blog() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( '%s', 'post date', 'pukeko' ),
		'' . $time_string . ''
	);

	echo '<span class="posted-on">' . $posted_on . '</span>';

}
endif;


if ( ! function_exists( 'pukeko_entry_meta' ) ) :
/**
 * Prints HTML with meta information for the categories and tags.
 */
function pukeko_entry_meta() {
	// Hide category and tag text for pages.
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( esc_html__( ' ', 'pukeko' ) );
		if ( $categories_list && pukeko_categorized_blog() and ! is_single() ) {
			printf( '<span class="entry-cats">' . esc_html__( '%1$s', 'pukeko' ) . '</span>', $categories_list );
		}

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', esc_html__( ' ', 'pukeko' ) );
		if ( $tags_list and  is_single() ) {
			printf( '<span class="entry-tags">' . esc_html__( '%1$s', 'pukeko' ) . '</span>', $tags_list );
		}
	}
}
endif;


if ( ! function_exists( 'pukeko_edit_link' ) ) :
/**
 * Returns an accessibility-friendly link to edit a post or page.
 *
 */
function pukeko_edit_link() {
	edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			__( esc_html__('Edit', 'pukeko') . ' <span class="pencil-icon"> '. pukeko_get_svg( array( 'icon' => 'pencil' )) . '</span>' ),
			get_the_title()
		)
	);
}
endif;


if ( ! function_exists( 'pukeko_estimated_read_time' ) ) :
	/**
	 * Prints HTML with the estimated reading time. Does not display when time to read is zero.
	 */
	function pukeko_estimated_read_time() {
		$minutes = pukeko_get_estimated_reading_time();
		if ( 0 === $minutes ) return null;
		$datetime_attr = sprintf( '%dm 0s', $minutes );
		$read_time_text = sprintf( _nx( '%s min read', '%s min read', $minutes, 'Time to read', 'pukeko' ), $minutes );



		/* translators: 1: The [datetime] attribute for the <time> tag. 2: Estimated reading time text, in minutes. */

		printf ( '<span class="reading-time"><time datetime="%1$s"></time>%2$s</span>',
			$datetime_attr,
			$read_time_text);
	}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function pukeko_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'pukeko_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'pukeko_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so pukeko_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so pukeko_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in pukeko_categorized_blog.
 */
function pukeko_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'pukeko_categories' );
}
add_action( 'edit_category', 'pukeko_category_transient_flusher' );
add_action( 'save_post',     'pukeko_category_transient_flusher' );
