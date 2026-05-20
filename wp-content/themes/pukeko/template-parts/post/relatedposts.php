<?php
/**
 * The template for displaying Related Posts
 *
 * @subpackage Pukeko
 * @since Pukeko 1.0.0
	* @version 1.0.2
 */
?>

<?php $orig_post = $post;
global $post;

// Get the category IDs
$categories = get_the_category($post->ID);
// Get the URL of this category
if ( ! empty( $categories ) ) {
	$category_link = get_category_link($categories[0]);
}

if ($categories) {
$category_ids = array();
foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;

$args=array(
	'category__in' 				=> $category_ids,
	'post__not_in' 				=> array($post->ID),
	'posts_per_page'			=> 6,
	'ignore_sticky_posts'	=> 1
);

$rp_query = new wp_query( $args );
if( $rp_query->have_posts() ) {
echo '<div class="related-wrap">
				<div class="related cf">
					<h2 class="section-title f1"> ' . esc_html__( 'Related Posts', 'pukeko') . '<a class="related-more-link btn-naked" href="' . esc_url( $category_link ) . '">' . esc_html__( 'View more', 'pukeko') . '</a>' . '</h2>

						<div class="related-container">';

							while( $rp_query->have_posts() ) {
								$rp_query->the_post();?>
								<div class="related-post">
									<a href="<?php the_permalink(); ?>" class="related-img"><?php the_post_thumbnail('pukeko-fi-classic'); ?></a>
										<div class="related-post-text">
											<div class="related-entry-cats">
												<?php the_category(', '); ?>
											</div><!-- end .entry-cats -->
											<?php the_title( sprintf( '<h3 class="related-title f1"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>
										</div><!-- end .related-post-text -->
								</div><!-- end .related-post -->
						<?php
						}
						echo '</div>
					</div>
</div>';
}
}
$post = $orig_post;
wp_reset_query(); ?>
