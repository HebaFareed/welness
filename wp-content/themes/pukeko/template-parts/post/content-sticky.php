<?php
/**
 * Template part for displaying sticky posts
 *
 * @package Pukeko
 * @since Pukeko 1.0.4
 * @version 1.0.1
 */

?>

	<article id="post-<?php the_ID(); ?>" <?php post_class("cf"); ?>>

		<?php get_template_part( 'template-parts/post/post', 'sticky' ); ?>

	</article><!-- #post-## -->
