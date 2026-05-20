<?php
/**
 * Displays footer site info
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.2
 */
$blogname = get_bloginfo('name');
?>


<div class="site-info">
	<div class="credit" role="contentinfo">
	<?php if ( get_theme_mod( 'pukeko_credit' ) ) : ?>
		<span><?php echo wp_kses_post( get_theme_mod( 'pukeko_credit' ) ); ?></span>
	<?php else : ?>
		<span class="copyright"><?php printf(esc_html__('&copy; %1$s %2$s.', 'pukeko'), date("Y"), $blogname ); ?></span>
		<?php
			/* Include privacy policy link, if privacy policy page is set under Settings, Privacy. */
			if ( function_exists( 'the_privacy_policy_link' ) ) {
				the_privacy_policy_link( '<span role="separator" aria-hidden="true">', '</span>');
			}
		?>
	<?php endif; ?>
</div><!-- end .credit -->
</div><!-- end #site-info -->
