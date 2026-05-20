<?php
/**
 * The template for displaying all single posts
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
	<section class="elementor-section pageblock single_team_page theme-width elementor-section-boxed">
		<div class="elementor-container elementor-column-gap-default single_team_page">
			<?php
			while ( have_posts() ) : the_post(); ?>
			<div class="elementor-column elementor-col-33 elementor-inner-column elementor-element">
				<div class="team_page_left">
					<?php the_post_thumbnail('full'); ?>
					<div class="teammember-content-wrap">
						<h2 class="section-title">
							<?php the_title();?>
							<span class="teammember-role"><?php the_field('position');?></span>
						</h2>
					</div>
				</div>
			</div>
			<div class="elementor-column elementor-col-66 elementor-inner-column elementor-element">
				<div class="team_page_right">
					<?php the_content(); ?>
				</div>
			</div>
			<?php endwhile; 	// End the loop. ?>
		</div>
	</section>
</div><!-- #primary -->
<?php
get_footer();
