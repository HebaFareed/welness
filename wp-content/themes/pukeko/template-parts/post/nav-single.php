<?php
/**
 * The template for displaying The Single Post Nav
 *
 * @subpackage Pukeko
 * @since Pukeko 1.0
	* @version 1.0.2
 */
?>

<nav class="navigation post-navigation" role="navigation">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Post navigation', 'pukeko' ); ?></h2>
		<div class="nav-links cf">

<?php $prev_post = get_previous_post(); ?>
<?php if (!empty( $prev_post )): ?>
	<div class="nav-previous">

		<?php if ( has_post_thumbnail($prev_post->ID) ) : ?>
			<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev" class="nav-thumb"><?php echo get_the_post_thumbnail( $prev_post->ID, 'pukeko-fi-classic' ); ?>
			<span class="arrow-link arrow-left"><?php esc_html_e( 'Previous Post', 'pukeko' ); ?><?php echo pukeko_get_svg( array( 'icon' => 'arrow-left' ) ); ?></span></a>
		<?php endif; ?>

		<div class="nav-title">
			<span class="screen-reader-text"><?php esc_html_e( 'Previous Post', 'pukeko' ); ?></span>
			<span class="entry-cats">
				<?php
					// get post categories
					$cats = wp_get_post_categories($prev_post->ID);
					foreach ( $cats as $category ) {
							$current_cat = get_cat_name($category);
							$cat_link = get_category_link($category);
							echo "<span><a href='$cat_link'>";
							echo $current_cat;
							echo "</a></span>";
					}
			?>
			</span>
			<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev" class="entry-title"><?php echo esc_attr( $prev_post->post_title ); ?></a>
		</div>

	</div><!-- .nav-previous -->
<?php endif; ?>

<?php $next_post = get_next_post(); ?>
<?php if (!empty( $next_post )): ?>
	<div class="nav-next">

		<?php if ( has_post_thumbnail($next_post->ID) ) : ?>
			<a class="nav-thumb" href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next"><?php echo get_the_post_thumbnail( $next_post->ID, 'pukeko-fi-classic' ); ?>
	<span class="arrow-link arrow-right"><?php esc_html_e( 'Next Post', 'pukeko' ); ?><?php echo pukeko_get_svg( array( 'icon' => 'arrow-right' ) ); ?></span></a>
		<?php endif; ?>

		<div class="nav-title">
			<span class="screen-reader-text"><?php esc_html_e( 'Next Post', 'pukeko' ); ?></span>
			<span class="entry-cats">
				<?php
					// get post categories
					$cats = wp_get_post_categories($next_post->ID);
					foreach ( $cats as $category ) {
							$current_cat = get_cat_name($category);
							$cat_link = get_category_link($category);
							echo "<span><a href='$cat_link'>";
							echo $current_cat;
							echo "</a></span>";
					}
			?>
			</span>
			<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next"  class="entry-title"><?php echo esc_attr( $next_post->post_title ); ?></a>
		</div>
	</div><!-- .nav-next -->
<?php endif; ?>

	</div><!-- .nav-links -->
	</nav><!-- .post-navigation -->
