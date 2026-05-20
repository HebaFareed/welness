<?php
/**
 * Additional features to allow styling of the templates
 *
 * @package Pukeko
 * @since 1.0.0
 * @version 1.0.4
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */


function pukeko_body_classes( $classes ) {

	// Classes activated by default

	// Adds class sidebar-show, if page template is set to "No Sidebar, no title".
	if (is_archive() || is_search() ) {
		$classes[] = 'blog-archive';
	}

	// Sidebar classes:
		// Adds class sidebar-show, if page template is set to "No Sidebar, no title".
		if ( is_page_template( 'page-templates/no-sidebar-no-title.php' ) ) {
			$classes[] = 'sidebar-show';
		}

		// Adds class sidebar-show, if Sidebar 1 has widgets and Blog Sidebar theme option is set to "blog and posts".
		if ( is_active_sidebar( 'sidebar-1') && 'blog-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) ) {
			$classes[] = 'sidebar-show';
		}

		// Adds class sidebar-show, if Sidebar 1 has widgets and Blog Sidebar theme option is set to "blog and posts".
		if ( is_active_sidebar( 'sidebar-1') && 'blog-post' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) ) {
			$classes[] = 'sidebar-show';
		}

		// Adds class sidebar-hide, if Sidebar 1 has no widgets.
		if ( ! is_active_sidebar( 'sidebar-1') ) {
			$classes[] = 'sidebar-hide';
		}

		// Adds class sidebar-hide, if Sidebar 1 has either no widgets or it has widgets and Blog Sidebar theme option is set to "blog and posts".
		if (is_home() && 'post-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) || is_archive() && 'post-only' == get_theme_mod( 'pukeko_blogsidebar' ) || is_search() && 'post-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) || ! is_active_sidebar( 'sidebar-1' ) ) {
			$classes[] = 'sidebar-hide';
			if (in_array('sidebar-show', $classes)) {
					unset( $classes[array_search('sidebar-show', $classes)] );
			}
		}

		// Adds class sidebar-show-post on single posts, if Sidebar 1 has widgets and Blog Sidebar theme option is set to "blog and posts".
		if ( is_single() && is_active_sidebar( 'sidebar-1') && 'blog-post' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) ) {
			$classes[] = 'sidebar-show-post';
		}

		// Adds class sidebar-hide-post on single posts, if Blog Sidebar theme option is set to "blog only".
		if ( is_single() && 'blog-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) ) {
			$classes[] = 'sidebar-hide-post';
			if (in_array('sidebar-show', $classes)) {
					unset( $classes[array_search('sidebar-show', $classes)] );
			}
		}

		// Adds class sidebar-hide-post on single posts, if Blog Sidebar is empty.
		if ( is_single() && ! is_active_sidebar( 'sidebar-1' ) ) {
			$classes[] = 'sidebar-hide-post';
			if (in_array('sidebar-show', $classes)) {
					unset( $classes[array_search('sidebar-show', $classes)] );
			}
		}

		// Adds class sidebar-show-post on single posts, if sidebar has widgets and Blog Sidebar theme option is set to "post only".
		if (is_single() && is_active_sidebar( 'sidebar-1') && 'post-only' == get_theme_mod( 'pukeko_blogsidebar' ) ) {
			$classes[] = 'sidebar-show-post';
		}

		// Removes class sidebar-show and sidebar-show-post, if Sidebar 1 does not have widgets.
		if ( ! is_active_sidebar( 'sidebar-1' ) ) {
			if (in_array('sidebar-show', $classes)) {
					unset( $classes[array_search('sidebar-show', $classes)] );
			}
			if (in_array('sidebar-show-post', $classes)) {
					unset( $classes[array_search('sidebar-show-post', $classes)] );
			}
		}

		// Removes class sidebar-show-post on single posts, if Blog Sidebar theme option is set to "blog only".
		if ( is_single() && 'blog-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) ) {
				if (in_array('sidebar-show-post', $classes)) {
						unset( $classes[array_search('sidebar-show-post', $classes)] );
				}
		}

		// Removes class sidebar-show on all pages except single posts, if Blog Sidebar theme option is set to "post only".
		if ( ! is_single() && 'post-only' == get_theme_mod( 'pukeko_blogsidebar', 'blog-only' ) ) {
			if (in_array('sidebar-show', $classes)) {
					unset( $classes[array_search('sidebar-show', $classes)] );
			}
		}

	// Page Template classes:
		// If its a single page.
		if ( is_page() ) {
			$classes[] = 'single-page';
		}

		// Adds class fullwidth, if page template is set to "Full Width".
		if ( is_page_template( 'page-templates/fullwidth-page.php' ) ) {
			$classes[] = 'fullwidth';
			$classes[] = 'fullwidth-page';
		}

		// Adds class fullwidth, if page template is set to "Full Width, no title".
		if ( is_page_template( 'page-templates/fullwidth-notitle-page.php' ) ) {
			$classes[] = 'fullwidth';
			$classes[] = 'fullwidth-notitle-page';
		}

		// Adds class fullwidth, if page template is set to "Full Width".
		if ( is_page_template( 'page-templates/fullscreen-page.php' ) ) {
			$classes[] = 'fullscreen-page';
		}

		// Adds class no-title, if page template is set to "No Sidebar, no title".
		if ( is_page_template( 'page-templates/nosidebar-notitle-page.php' ) ) {
			$classes[] = 'no-pagetitle';
			$classes[] = 'nosidebar-notitle-page';
		}

		// Adds class no-title, if page template is set to "Full Width, no title".
		if ( is_page_template( 'page-templates/fullwidth-notitle-page.php' ) ) {
			$classes[] = 'no-pagetitle';
			$classes[] = 'fullwidth-notitle-page';
		}

		// Adds class no-title, if page template is set to "No title".
		if ( is_page_template( 'page-templates/notitle-page.php' ) ) {
			$classes[] = 'no-pagetitle';
			$classes[] = 'notitle-page';
			$classes[] = 'page-template-default';
		}

		// Adds class no-title, if page template is set to "No sidebar".
		if ( is_page_template( 'page-templates/nosidebar-page.php' ) ) {
			$classes[] = 'nosidebar-page';
		}

	// Classes for Header:
		// Adds class headersocial, if Header Social menu is used.
		if ('' != has_nav_menu( 'social-header' ) ) {
			$classes[] = 'headersocial';
		}

		// Adds class no-headersocial, if Header Social menu is not used.
		if ('' == has_nav_menu( 'social-header' ) ) {
			$classes[] = 'no-headersocial';
		}

		// Classes for Customizer Options - Typography:
			// Choose serif or sans serif headline font
			if ('sansserif' == get_theme_mod( 'pukeko_headline_font', 'serif' ) ) {
				$classes[] = 'h-alt';
			}

			// Choose serif or sans serif headline font
			if ('bold' == get_theme_mod( 'pukeko_headline_font_weight', 'lowercase' ) ) {
				$classes[] = 'h-bold';
			}

			// Show links in uppercases font style
			if ('uppercase' == get_theme_mod( 'pukeko_links_font_style', 'lowercase' ) ) {
				$classes[] = 'uppercase';
			}

	// Classes for Customizer Options - Style:
		// Show buttons with rounded corners
		if ('smooth' == get_theme_mod( 'pukeko_btn_style', 'sharp' ) ) {
			$classes[] = 'btn-smooth';
			$classes[] = 'borders-round';
		}

		// Show buttons with round corners
		if ('round' == get_theme_mod( 'pukeko_btn_style', 'sharp' ) ) {
			$classes[] = 'btn-round';
			$classes[] = 'borders-round';
		}

		// Show avatar images with rounded corners
		if ('rounded' == get_theme_mod( 'pukeko_avatar_style', 'square' ) ) {
			$classes[] = 'avatar-rounded';
		}

		// Show round avatar images
		if ('circle' == get_theme_mod( 'pukeko_avatar_style', 'square' ) ) {
			$classes[] = 'avatar-circle';
		}

	// Classes for Customizer Options - Header:
		// Adds class headersearch, if Header Search is enabled.
		if ('' == get_theme_mod( 'pukeko_headersearch' ) ) {
			$classes[] = 'headersearch';
		}

		// Adds class no-headersearch, if Header Search is disabled.
		if ('' != get_theme_mod( 'pukeko_headersearch' ) ) {
			$classes[] = 'no-headersearch';
		}

		if ('hero-center' == get_theme_mod( 'pukeko_hero_alignment', 'hero-left' ) ) {
			$classes[] = 'hero-center';
		}

		if ('hero-right' == get_theme_mod( 'pukeko_hero_alignment', 'hero-left' ) ) {
			$classes[] = 'hero-right';
		}

	// Classes for Customizer Options - Blog:
		// Adds class blog-1-column, if Blog Column theme option is set to "1-column".
		if ('onecolumn' == get_theme_mod( 'pukeko_blogcolumns', 'twocolumn' ) ) {
			$classes[] = 'blog-1-column';
		}

		// Adds class blog-2-column, if Blog Column theme option is set to "2-column".
		if ('twocolumn' == get_theme_mod( 'pukeko_blogcolumns', 'twocolumn' ) || '' == get_theme_mod( 'pukeko_blogcolumns', 'twocolumn' )) {
			$classes[] = 'blog-2-column';
		}

		// Adds class blog-2-column, if Blog Column theme option is set to "2-column".
		if ('threecolumn' == get_theme_mod( 'pukeko_blogcolumns', 'twocolumn' ) ) {
			$classes[] = 'blog-3-column';
		}

	// Blog Display Options:
		// Adds class no-postdate
		if ('1' != get_theme_mod( 'pukeko_displaydate', '1' ) ) {
			$classes[] = 'no-postdate';
		}

		// Adds class no-postdate-blog
		if ( is_single() && '1' != get_theme_mod( 'pukeko_displaydate_singlepost', '1' ) ) {
			$classes[] = 'no-postdate-single';
		}

		// Adds class no-postcommentscount
		if ('1' != get_theme_mod( 'pukeko_displaycomments', '1' ) ) {
			$classes[] = 'no-postcommentscount';
		}

		// Adds class no-postcommentscount-blog
		if ( is_single() && '1' != get_theme_mod( 'pukeko_displaycomments_singlepost', '1' ) ) {
			$classes[] = 'no-postcommentscount-single';
		}

		// Adds class no-postreadtime
		if ('1' != get_theme_mod( 'pukeko_displayreadtime', '1' ) ) {
			$classes[] = 'no-postreadtime';
		}

		// Adds class no-postreadtime-blog
		if ( is_single() && '1' != get_theme_mod( 'pukeko_displayreadtime_singlepost', '1' ) ) {
			$classes[] = 'no-postreadtime-single';
		}

		// Adds class no-postcats
		if ('1' != get_theme_mod( 'pukeko_displaycategories', '1' ) ) {
			$classes[] = 'no-postcats';
		}

		// Adds class no-posttags
		if ('1' != get_theme_mod( 'pukeko_displaytags', '1' ) ) {
			$classes[] = 'no-posttags';
		}

		// Adds class no-postauthor
		if ('1' != get_theme_mod( 'pukeko_displayauthor', '1' ) ) {
			$classes[] = 'no-postauthor';
		}

		// Blog Cards Options:
		if ('blogcards-moveup' == get_theme_mod( 'pukeko_blogcards_hovereffect', 'blogcards-movein' ) ) {
			$classes[] = 'blogcards-moveup';
		}

		if ('blogcards-text-center' == get_theme_mod( 'pukeko_blogcards_textalignment', 'blogcards-text-left' ) ) {
			$classes[] = 'blogcards-center';
		}

		if ('blogcards-radius-s' == get_theme_mod( 'pukeko_blogcards_borderradius', 'blogcards-radius-none' ) ) {
			$classes[] = 'blogcards-radius-s';
		}

		if ('blogcards-radius-m' == get_theme_mod( 'pukeko_blogcards_borderradius', 'blogcards-radius-none' ) ) {
			$classes[] = 'blogcards-radius-m';
		}

		if ('blogcards-radius-l' == get_theme_mod( 'pukeko_blogcards_borderradius', 'blogcards-radius-none' ) ) {
			$classes[] = 'blogcards-radius-l';
		}

		if ('blogcards-shadow-s' == get_theme_mod( 'pukeko_blogcards_shadow', 'blogcards-radius-none' ) ) {
			$classes[] = 'blogcards-shadow-s';
		}

		if ('blogcards-shadow-m' == get_theme_mod( 'pukeko_blogcards_shadow', 'blogcards-radius-none' ) ) {
			$classes[] = 'blogcards-shadow-m';
		}

		if ('blogcards-shadow-l' == get_theme_mod( 'pukeko_blogcards_shadow', 'blogcards-radius-none' ) ) {
			$classes[] = 'blogcards-shadow-l';
		}

		if ('blogcards-shadow-s' == get_theme_mod( 'pukeko_blogcards_shadow_hover', 'blogcards-shadow-l' ) ) {
			$classes[] = 'blogcards-shadowhover-s';
		}

		if ('blogcards-shadow-m' == get_theme_mod( 'pukeko_blogcards_shadow_hover', 'blogcards-shadow-l' ) ) {
			$classes[] = 'blogcards-shadowhover-m';
		}

		if ('blogcards-shadow-l' == get_theme_mod( 'pukeko_blogcards_shadow_hover', 'blogcards-shadow-l' ) ) {
			$classes[] = 'blogcards-shadowhover-l';
		}

		// Disable Flexbox Post Card Stretch:
		if (get_theme_mod( 'pukeko_background_color' ) == get_theme_mod( 'pukeko_blogcards_bgcolor' ) && 'blogcards-shadow-none' == get_theme_mod( 'pukeko_blogcards_shadow' ) || '' == get_theme_mod( 'pukeko_blogcards_shadow' )) {
			$classes[] = 'blogcards-flexstart';
		}

		// Classes for Footer Widgets:
		if ( is_active_sidebar( 'footer-1' ) &&
			is_active_sidebar( 'footer-2' ) &&
			is_active_sidebar( 'footer-3' ) &&
			is_active_sidebar( 'footer-4' ) &&
			is_active_sidebar( 'footer-5' ) &&
			is_active_sidebar( 'footer-6' ) ) {
				$classes[] = 'footer-6-column';
		}

		if ( is_active_sidebar( 'footer-1' ) &&
			is_active_sidebar( 'footer-2' ) &&
			is_active_sidebar( 'footer-3' ) &&
			is_active_sidebar( 'footer-4' ) &&
			is_active_sidebar( 'footer-5' ) &&
			! is_active_sidebar( 'footer-6' ) ) {
				$classes[] = 'footer-5-column';
		}

		if ( is_active_sidebar( 'footer-1' ) &&
			is_active_sidebar( 'footer-2' ) &&
			is_active_sidebar( 'footer-3' ) &&
			is_active_sidebar( 'footer-4' ) &&
			! is_active_sidebar( 'footer-5' ) &&
			! is_active_sidebar( 'footer-6' ) ) {
				$classes[] = 'footer-4-column';
		}

		if ( is_active_sidebar( 'footer-1' ) &&
			is_active_sidebar( 'footer-2' ) &&
			is_active_sidebar( 'footer-3' ) &&
			! is_active_sidebar( 'footer-4' ) &&
			! is_active_sidebar( 'footer-5' ) &&
			! is_active_sidebar( 'footer-6' ) ) {
				$classes[] = 'footer-3-column';
		}

		if ( is_active_sidebar( 'footer-1' ) &&
			is_active_sidebar( 'footer-2' ) &&
			! is_active_sidebar( 'footer-3' ) &&
			! is_active_sidebar( 'footer-4' ) &&
			! is_active_sidebar( 'footer-5' ) &&
			! is_active_sidebar( 'footer-6' ) ) {
				$classes[] = 'footer-2-column';
		}

		if ( is_active_sidebar( 'footer-1' ) &&
			! is_active_sidebar( 'footer-2' ) &&
			! is_active_sidebar( 'footer-3' ) &&
			! is_active_sidebar( 'footer-4' ) &&
			! is_active_sidebar( 'footer-5' ) &&
			! is_active_sidebar( 'footer-6' ) ) {
				$classes[] = 'footer-1-column';
		}

	// Author classes:
	// Adds class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Additional classes:
	// Adds a class of blog, if it's a blog page.
	if ( is_home() ) {
		$classes[] = 'blog';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Add a class if there is a custom header.
	if ( has_header_image() ) {
		$classes[] = 'has-header-image';
	}

	return $classes;
}
add_filter( 'body_class', 'pukeko_body_classes' );


/**
 * Determines the estimated time to read a post, in minutes.
 */
function pukeko_get_estimated_reading_time() {
	$content = get_post_field( 'post_content', get_the_ID() );
	$count = str_word_count( strip_tags( $content ) );
	return (int) round( $count / 250 ); // Assuming 250 words per minute reading speed.
}

/**
 * Add Classes to dashboard to support theme options in editor styles.
 */
function custom_admin_body_class( $classes ) {

	if ('sansserif' == get_theme_mod( 'pukeko_headline_font', 'serif' ) ) {
		$classes .= ' h-alt ';
	}

	if ('bold' == get_theme_mod( 'pukeko_headline_font_weight', 'regular' ) ) {
		$classes .= ' h-bold ';
	}

	if ('uppercase' == get_theme_mod( 'pukeko_links_font_style', 'lowercase' ) ) {
		$classes .= ' uppercase ';
	}

 return $classes;
}
add_filter( 'admin_body_class', 'custom_admin_body_class' );


/**
 * Add Classes to posts and pages.
 */
function pukeko_post_classes( $classes, $class, $post_id ) {

		if ( comments_open( $post_id ) ) {
				$classes[] = 'comments-open';
		}

		if ( ! comments_open( $post_id ) ) {
				$classes[] = 'comments-closed';
		}

		if ( 0 == get_comments_number( $post_id ) ) {
				$classes[] = 'no-comments';
		}

		if ( 0 != get_comments_number( $post_id ) ) {
				$classes[] = 'has-comments';
		}

		return $classes;
}
add_filter( 'post_class', 'pukeko_post_classes', 10, 3 );


/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function pukeko_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'pukeko_pingback_header' );


/**
 * Display custom CSS.
 */
function pukeko_css_wrap() {
	?>
	<style type="text/css" id="custom-css">

	<?php if ('ffffff' != get_theme_mod( 'pukeko_blogcards_bgcolor', 'ffffff' ) ) { ?>
		.entry-link {background-color: <?php echo get_theme_mod('pukeko_blogcards_bgcolor'); ?>;}
	<?php } ?>

	<?php if ('ffffff' != get_theme_mod( 'pukeko_blogcards_bgcolor_hover', 'ffffff' ) ) { ?>
		.entry-link:hover {background-color: <?php echo get_theme_mod('pukeko_blogcards_bgcolor_hover'); ?>;}
	<?php } ?>

	<?php if ('000000' != get_theme_mod( 'pukeko_blogcards_textcolor', '000000' ) ) { ?>
		.entry-link {color: <?php echo get_theme_mod('pukeko_blogcards_textcolor'); ?>;}
	<?php } ?>

	<?php if ('blogcards-text-right' == get_theme_mod( 'pukeko_blogcards_textalignment', 'blogcards-text-left' ) ) { ?>
		.entry-link {text-align: right;}
	<?php } ?>

	<?php if ('ffffff' != get_theme_mod( 'pukeko_background_color', 'ffffff' ) ) { ?>
		body {background-color: <?php echo get_theme_mod('pukeko_background_color'); ?>;}
	<?php } ?>

	<?php if ('1' != get_theme_mod( 'pukeko_displayauthorbio', '1' ) ) { ?>
		.single-post .authorbox-wrap {display: none;}
	<?php } ?>

	<?php if ('1767F3' != get_theme_mod( 'pukeko_link_color', '1767F3' ) ) { ?>
		.entry-content p a,
		.entry-content li a,
		.comment-content p a,
		.comment-content li a,
		.hero-text a,
		.btn-outlined a,
		a.btn-outlined,
		.pagination .current {
			border-color: <?php echo get_theme_mod('pukeko_link_color'); ?>
		}
		.widget_mc4wp_form_widget input[type="submit"],
		.btn-primary a,
		a.btn-primary,
		input[type="submit"],
		.comment-respond input[type="submit"],
		.widget_mc4wp_form_widget .subscribe-btn,
		.widget_search .search-submit,
		.entry-content .search-submit,
		.wp-block-button__link:not(.has-background),
		.entry-content .has-primary-link-background-color {
			background-color:  <?php echo get_theme_mod('pukeko_link_color'); ?>;
		}
		h1 a:hover,
		h2 a:hover,
		h3 a:hover,
		h4 a:hover,
		h5 a:hover,
		h6 a:hover,
		.site-title a:hover,
		.single-post .navigation .entry-title:hover,
		.btn-outlined a,
		a.btn-outlined,
		.comment-list .comment-metadata a:hover,
		.comment-reply-title #cancel-comment-reply-link:hover,
		.logged-in-as a:hover,
		.side-widgets li a:hover, .side-widgets p a:hover, .comment-body .reply a:hover, .comments-area h2.comments-title span a:hover, .posts-container .hentry .entry-meta a:hover, .posts-container .sticky-container .sticky .entry-footer a:hover,
		.related-entry-cats a:hover, .single-post .byline a:hover, .single-post .entry-cats a:hover, .single-post .entry-comments:hover .bubble-icon .icon, .single-post .entry-meta a:hover, .single-post .navigation .nav-title .nav-subtitle:hover, .comment-list .comment-metadata a:hover, .comment-reply-title #cancel-comment-reply-link:hover, .logged-in-as a:hover,
		.btn-naked:hover,
		.btn-secondary a:hover,
		a.btn-secondary:hover,
		.tagcloud a:hover,
		.entry-tags a:hover,
		.comment-list b.fn a:hover,
		button:hover,
		.authorbox-content p a, .comment-content li a, .comment-content p a, .entry-content li a, .entry-content p a, .hero-text a, .wp-caption .wp-caption-text a, figcaption a,
		.teammember-social-wrap a:hover .icon,
		.entry-content .has-primary-link-color {
			color: <?php echo get_theme_mod('pukeko_link_color'); ?>;
			fill: <?php echo get_theme_mod('pukeko_link_color'); ?>;
		}
		#sharing-header .sd-social-icon .sd-content ul li[class*='share-'] a:hover,
			#sharing-footer .sd-social-icon .sd-content ul li[class*='share-'] a:hover {
			color: <?php echo get_theme_mod('pukeko_link_color'); ?> !important;
		}
		@media (min-width: 75.000em) {
		.main-navigation li a:hover,
		.main-navigation li:focus > a,
		.main-navigation li.focus > a,
		.main-navigation li:hover > a,
		.main-navigation ul ul li:focus > a,
		.main-navigation ul ul li:hover > a,
		.main-navigation ul ul a:hover,
		.main-navigation ul ul.sub-menu a:hover,
		.social-header-nav a:hover .icon {
				color: <?php echo get_theme_mod('pukeko_link_color'); ?>;
				fill: <?php echo get_theme_mod('pukeko_link_color'); ?>;
			}
		}
		.authorbox-content p a, .comment-content li a, .comment-content p a, .entry-content li a, .entry-content p a, .hero-text a, .wp-caption .wp-caption-text a, figcaption a {
			box-shadow: inset 0 -0.06em 0 <?php echo get_theme_mod('pukeko_link_color'); ?>;
			box-shadow: inset 0 -0.07em 0 <?php echo get_theme_mod('pukeko_link_color'); ?>;
		}
		.authorbox-content p a:hover, .comment-content li a:hover, .comment-content p a:hover, .entry-content li a:hover, .entry-content p a:hover, .hero-text a:hover, .wp-caption .wp-caption-text a:hover, figcaption a:hover {
				box-shadow: inset 0 -1em 0 <?php echo get_theme_mod('pukeko_link_color'); ?>;
		}
		<?php } ?>

	<?php if ('0542af' != get_theme_mod( 'pukeko_link_hover_color', '0542af' ) ) { ?>
		.btn-primary a:hover,
		a.btn-primary:hover,
		a.btn-outlined:hover,
		.btn-outlined a:hover,
		a.btn-primary:hover,
		.btn-primary a:hover,
		input[type="submit"]:hover,
		.widget_mc4wp_form_widget input[type="submit"]:hover,
		.widget_mc4wp_form_widget .subscribe-btn:hover,
		.comment-respond input[type="submit"]:hover,
		.wp-block-button__link:not(.has-background):hover,
		.widget_search .search-submit:hover,
		.entry-content .search-submit:hover,
		.entry-content .has-primary-link-hover-background-color {
			background-color:  <?php echo get_theme_mod('pukeko_link_hover_color'); ?>;
		}
		a.btn-outlined:hover,
		.btn-outlined a:hover {
			border-color:  <?php echo get_theme_mod('pukeko_link_hover_color'); ?>;
		}
		.entry-content .has-primary-link-hover-color {
			color:  <?php echo get_theme_mod('pukeko_link_hover_color'); ?>;
			fill:  <?php echo get_theme_mod('pukeko_link_hover_color'); ?>;
		}
	<?php } ?>

	<?php if ('#262626' != get_theme_mod( 'pukeko_footer_bg_color', '262626' ) ) { ?>
		#colophon {background-color: <?php echo get_theme_mod('pukeko_footer_bg_color'); ?>;}
	<?php } ?>

	<?php if ('#ffffff' == get_theme_mod( 'pukeko_footer_bg_color', 'ffffff' ) ) { ?>
		#colophon {border-top: 1px solid rgba(0, 0, 0, 0.12);}
	<?php } ?>

	<?php if ('#ffffff' != get_theme_mod( 'pukeko_footer_text_color', 'ffffff' ) ) { ?>
		#colophon,
		#colophon .textwidget p {
			color: <?php echo get_theme_mod('pukeko_footer_text_color'); ?>;
			fill: <?php echo get_theme_mod('pukeko_footer_text_color'); ?>;
		}
	<?php } ?>

	<?php if ('#ffffff' != get_theme_mod( 'pukeko_footer_link_color', 'ffffff' ) ) { ?>
		#colophon .widget_mc4wp_form_widget a,
		#colophon a,
		.social-footer-nav .icon {
			color: <?php echo get_theme_mod('pukeko_footer_link_color'); ?>;
			fill: <?php echo get_theme_mod('pukeko_footer_link_color'); ?>;
		}
	<?php } ?>

	<?php if ('#1767f3' != get_theme_mod( 'pukeko_footer_linkhover_color', '1767f3' ) ) { ?>
		#colophon a:hover,
		.site-footer a:hover,
		.social-footer-nav ul li a:hover .icon {
			color: <?php echo get_theme_mod('pukeko_footer_linkhover_color'); ?>;
			fill: <?php echo get_theme_mod('pukeko_footer_linkhover_color'); ?>;
		}
	<?php } ?>

	<?php if ('#000000' != get_theme_mod( 'pukeko_hero_text_color', '000000' ) ) { ?>
		.hero-content-wrap, .hero-title {color: <?php echo get_theme_mod('pukeko_hero_text_color'); ?>;}
	<?php } ?>

	<?php if ('#ffffff' == get_theme_mod( 'pukeko_hero_text_color' , 'ffffff' ) ) { ?>
		.hero-subtitle,
		.hero-text {color: rgba(255, 255, 255, 0.6)}
	<?php } ?>

	<?php if ('#000000' != get_theme_mod( 'pukeko_hero_overlay_color', '000000' ) ) { ?>
		.hero-container:after, .hero-container:before {background-color: <?php echo get_theme_mod('pukeko_hero_overlay_color'); ?>;}
	<?php } ?>

	<?php if ('10' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.1;}
	<?php } ?>
	<?php if ('20' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.2;}
	<?php } ?>
	<?php if ('30' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.3;}
	<?php } ?>
	<?php if ('40' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.4;}
	<?php } ?>
	<?php if ('50' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.5;}
	<?php } ?>
	<?php if ('60' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.6;}
	<?php } ?>
	<?php if ('70' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.7;}
	<?php } ?>
	<?php if ('80' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.8;}
	<?php } ?>
	<?php if ('90' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 0.9;}
	<?php } ?>
	<?php if ('100' == get_theme_mod( 'pukeko_hero_overlay_opacity' ) ) { ?>
		.hero-container:after, .hero-container:before {opacity: 1;}
	<?php } ?>
	</style>
		<?php
	}
	add_action( 'wp_head', 'pukeko_css_wrap');
