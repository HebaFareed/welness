<?php
/**
 * Template part for displaying posts
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php get_template_part( 'template-parts/post/post', 'standard' ); ?>

</article><!-- #post-## -->
