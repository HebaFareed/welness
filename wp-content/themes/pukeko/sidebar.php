<?php
/**
 * The sidebar containing the blog widget area
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.0
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="side-widgets widget-area widget-area-default" role="complementary">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside><!-- #secondary -->
