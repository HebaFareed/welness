<?php
/**
 * Template for displaying the standard search forms
 *
 * @package Pukeko
 * @since Pukeko 1.0.0
 * @version 1.0.1
 */
?>

<div class="searchform-wrap cf">
<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
		<label>
				<span class="screen-reader-text"><?php echo _x( 'Search', 'label', 'pukeko' ) ?></span>
				<span class="search-icon"><?php echo pukeko_get_svg( array( 'icon' => 'magnifier' ) ); ?></span>
				<input type="search" class="search-field"
						placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'pukeko' ) ?>"
						value="<?php echo get_search_query() ?>" name="s"
						title="<?php echo esc_attr_x( 'Search for:', 'label', 'pukeko' ) ?>" />
		</label>
		<button type="submit" class="search-submit"><span class="screen-reader-text"><?php echo esc_attr_x( 'Search', 'submit button', 'pukeko' ) ?></span><span class="search-icon"><?php echo pukeko_get_svg( array( 'icon' => 'magnifier' ) ); ?></span></button>
</form>
</div>
