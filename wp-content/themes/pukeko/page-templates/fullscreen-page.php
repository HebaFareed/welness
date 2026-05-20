<?php
/**
* Template Name: Fullscreen
*
* Description: A page template for a Fullscreen Page (great for use with Page Builders).
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.0
 */

get_header(); ?>


		 <?php
		 while ( have_posts() ) : the_post();

			 get_template_part( 'template-parts/page/content', 'page' );

		 endwhile; // End of the loop.
		 ?>

 <?php
 get_footer();
