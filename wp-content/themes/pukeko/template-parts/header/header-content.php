<?php
/**
 * Displays header site branding and header m
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */
?>

<div class="site-branding">

	<?php the_custom_logo(); ?>

	<?php
	if ( is_front_page() && is_home() ) : ?>
		<h1 class="site-title f1"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
	<?php else : ?>
		<p class="site-title f1"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
	<?php endif; ?>

	<?php if ( '' == get_theme_mod( 'pukeko_sitedescription' ) ) : ?>
		<p class="site-description"><?php bloginfo( 'description' ); ?></p>
	<?php
	endif; ?>
</div><!-- .site-branding -->

<div id="nav-container" class="nav-container">

<button id="hamburger" class="menu-toggle" aria-controls="main-navigation" aria-expanded="false">
		<span class="hamburger-title"><?php esc_html_e( 'Menu', 'pukeko' ); ?></span>
				<span class="lines">
					<span class="sublines"></span>
					<span class="sublines"></span>
				</span>
				<span class="thex">
					<span class="sublines"></span>
					<span class="sublines"></span>
				</span>
</button>

<div id="nav-wrap" class="nav-wrap">

	<?php if ( has_nav_menu( 'menu-1' ) ) : ?>
		<?php get_template_part( 'template-parts/navigation/navigation', 'main' ); ?>
	<?php endif; ?>

		<div class="nav-subelements cf">

		<?php if ( '' == get_theme_mod( 'pukeko_headersearch' ) ) : ?>
			<div class="search-header">
				<?php get_search_form(); ?>
			</div><!-- end .search-header -->
		<?php endif; ?>

		<?php if (has_nav_menu( 'social-header' ) ) : ?>
		<nav id="social-header-nav" class="social-header-nav social-nav" role="navigation" aria-label="<?php esc_attr_e( 'Header Social Links Menu', 'pukeko' ); ?>">
			<?php
				wp_nav_menu( array(
					'theme_location' 	=> 'social-header',
					'menu_class'     	=> 'social-links-menu',
					'container_class' => 'menu-social-container',
					'depth'          	=> 1,
					'link_before'    	=> '<span class="screen-reader-text">',
					'link_after'     	=> '</span>' . pukeko_get_svg( array( 'icon' => 'chain' ) ),
				) );
			?>
		</nav><!-- end .header-social-nav -->
		<?php endif; ?>

		<?php if (has_nav_menu( 'cta-header' ) ) : ?>
			<?php
				wp_nav_menu( array(
					'theme_location' 	=> 'cta-header',
					'menu_class'     	=> 'social-links-menu',
					'container_class' => 'header-cta-wrap',
					'depth'          	=> 1,
					'link_before'    	=> '',
					'link_after'     	=> pukeko_get_svg( array( 'icon' => 'chain' ) ),
				) );
			?>
		<?php endif; ?>

</div><!-- end .nav-subelements -->

</div><!-- end .nav-wrap -->

</div><!-- end .nav-container -->
