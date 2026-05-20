<?php
/**
 * The sidebar visible on pages.
 *
 *
 * @package Pukeko
 * @since Pukeko 1.0.2
 * @version 1.0.0
 */

if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}
?>

<aside id="secondary" class="side-widgets widget-area widget-area-default" role="complementary">
	<?php dynamic_sidebar( 'sidebar-2' ); ?>
</aside><!-- #secondary -->
