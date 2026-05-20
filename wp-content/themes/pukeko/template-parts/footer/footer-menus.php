<?php
/**
 * Displays footer widgets if assigned
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */

?>

<?php
if ( has_nav_menu( 'menu-2' ) ||
	 has_nav_menu( 'social-footer' ) ) :
?>

	<div class="footer-menu-wrap cf">

		<?php
		if ( has_nav_menu( 'menu-2' ) ) : ?>
		<nav id="footer-nav" class="footer-nav" role="navigation" role="navigation" aria-label="<?php esc_attr_e( 'Footer Menu', 'pukeko' ); ?>">
			<?php wp_nav_menu( array(
				'theme_location'	=> 'menu-2',
				'container' 			=> 'false',
				'depth' 					=> -1));
			?>
		</nav><!-- end .footer-nav -->
		<?php endif; ?>

		<?php
		if ( has_nav_menu( 'social-footer' ) ) : ?>
		<nav id="social-footer-nav" class="social-footer-nav social-nav" role="navigation" aria-label="<?php esc_attr_e( 'Footer Social Links Menu', 'pukeko' ); ?>">
			<?php
				wp_nav_menu( array(
					'theme_location' => 'social-footer',
					'menu_class'     => 'social-links-menu',
					'container_class' => 'menu-social-container',
					'depth'          => 1,
					'link_before'    => '<span class="screen-reader-text">',
					'link_after'     => '</span>' . pukeko_get_svg( array( 'icon' => 'chain' ) ),
				) );
			?>
		</nav><!-- .footer-social-nav -->
		<?php endif; ?>

	</div><!-- .footer-menu-wrap -->

<?php endif; ?>
