<?php
/**
 * Displays header media
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.0
 */

?>

<div class="custom-header">

	<div class="custom-header-media">

		<?php the_custom_header_markup(); ?>

		<?php if ( '' != get_theme_mod( 'pukeko_hero_subtitle' ) || '' != get_theme_mod( 'pukeko_hero_title' ) || '' != get_theme_mod( 'pukeko_hero_text' ) ) : ?>

			<div class="hero-container">

				<div class="hero-content">

					<div class="hero-content-wrap">

						<?php if ( '' != get_theme_mod( 'pukeko_hero_subtitle' ) ) : ?>
							<span class="hero-subtitle"><?php echo wp_kses_post( get_theme_mod( 'pukeko_hero_subtitle' ) ); ?></span>
						<?php endif; ?>

						<?php if ( '' != get_theme_mod( 'pukeko_hero_title' ) ) : ?>
							<h1 class="hero-title"><?php echo wp_kses_post( get_theme_mod( 'pukeko_hero_title' ) ); ?></h1>
						<?php endif; ?>

						<?php if ( '' != get_theme_mod( 'pukeko_hero_text' ) ) : ?>
							<p class="hero-text"><?php echo wp_kses_post( get_theme_mod( 'pukeko_hero_text' ) ); ?></p>
						<?php endif; ?>

						<?php if ( '' != get_theme_mod( 'pukeko_hero_btn_url' ) ) : ?>
							<a class="btn hero-btn btn-primary btn-xl" href="<?php echo wp_kses_post( get_theme_mod( 'pukeko_hero_btn_url' ) ); ?>"><?php echo wp_kses_post( get_theme_mod( 'pukeko_hero_btn_text' ) ); ?></a>
						<?php endif; ?>

				</div><!-- .hero-content-wrap -->
			</div><!-- .hero-content -->

				<?php if ( '' != get_theme_mod( 'pukeko_headerinfo_text_one' ) || '' != get_theme_mod( 'pukeko_headerinfo_text_two' ) || '' != get_theme_mod( 'pukeko_headerinfo_text_three' ) || '' != get_theme_mod( 'pukeko_headerinfo_text_four' ) ) : ?>

				<div class="header-infobar">

					<?php if ( '' != get_theme_mod( 'pukeko_headerinfo_text_one' ) ) : ?>
						<div class="col">
							<h3 class="header-infobar-title f1"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_title_one' ) ); ?></h3>
							<p class="header-infobar-text"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_text_one' ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( '' != get_theme_mod( 'pukeko_headerinfo_text_two' ) ) : ?>
						<div class="col">
							<h3 class="header-infobar-title f1"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_title_two' ) ); ?></h3>
							<p class="header-infobar-text"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_text_two' ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( '' != get_theme_mod( 'pukeko_headerinfo_text_three' ) ) : ?>
						<div class="col">
							<h3 class="header-infobar-title f1"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_title_three' ) ); ?></h3>
							<p class="header-infobar-text"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_text_three' ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( '' != get_theme_mod( 'pukeko_headerinfo_text_four' ) ) : ?>
						<div class="col">
							<h3 class="header-infobar-title f1"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_title_four' ) ); ?></h3>
							<p class="header-infobar-text"><?php echo wp_kses_post( get_theme_mod( 'pukeko_headerinfo_text_four' ) ); ?></p>
						</div>
					<?php endif; ?>
				</div><!-- .header-infobar -->

			<?php endif; ?>

			</div><!-- .hero-container -->

		<?php endif; ?>

	</div><!-- .custom-header-media -->

</div><!-- .custom-header -->
