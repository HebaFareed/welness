<?php
/**
 * Pukeko functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Pukeko
 */

/*-----------------------------------------------------------------------------------*/
/* Theme update feature setup
/*-----------------------------------------------------------------------------------*/

 if ( ! function_exists( 'pukeko_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 */
	function pukeko_setup() {

	/**
	 * Make theme available for translation.
	 */
	load_theme_textdomain( 'pukeko', get_template_directory() . '/languages' );
	
	// Remove support form block widget screens.
	remove_theme_support( 'widgets-block-editor' );

	/**
	 * Add default posts and comments RSS feed links to head.
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Let WordPress manage the document title.
	 */
	add_theme_support( 'title-tag' );

	/**
	 * Enable support for Post Thumbnails on posts and pages.
	 */
	add_theme_support( 'post-thumbnails' );

	// Image Ratio 3:2 (classic film).
	add_image_size( 'pukeko-fi-classic', 2010, 1340, true );

	// Image Ratio 16:9 (Video HD).
	add_image_size( 'pukeko-fi-hd', 2000, 1125, true );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support( 'custom-logo', array(
	 'width'      	=> 960,
	 'height'     	=> 960,
	 'flex-width' 	=> true,
	 'flex-height' 	=> true,
	) );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add custom editor font sizes.
	add_theme_support( 'editor-font-sizes', array(
		array(
			'name' => __( 'small', 'pukeko' ),
			'shortName' => __( 'S', 'pukeko' ),
			'size' => 16,
			'slug' => 'small'
		),
		array(
				'name' => __( 'medium', 'pukeko' ),
				'shortName' => __( 'M', 'pukeko' ),
				'size' => 19,
				'slug' => 'regular'
		),
		array(
				'name' => __( 'large', 'pukeko' ),
				'shortName' => __( 'L', 'pukeko' ),
				'size' => 26,
				'slug' => 'large'
		),
		array(
				'name' => __( 'extralarge', 'pukeko' ),
				'shortName' => __( 'XL', 'pukeko' ),
				'size' => 32,
				'slug' => 'extralarge'
		)
	) );

	// Editor color palette.
	$primarylink = get_theme_mod( 'pukeko_link_color' );
	$primarylinkhover = get_theme_mod( 'pukeko_link_hover_color' );

	add_theme_support( 'editor-color-palette', array(
		array(
			'name'  => __( 'Primary Link', 'pukeko' ),
			'slug'  => 'primary-link',
			'color' => $primarylink,
		),
		array(
			'name'  => __( 'Primary Link Hover', 'pukeko' ),
			'slug'  => 'primary-link-hover',
			'color' => $primarylinkhover,
		),
		array(
			'name' => __( 'black', 'pukeko' ),
			'slug' => 'black',
			'color' => '#000000',
		),
		array(
			'name' => __( 'white', 'pukeko' ),
			'slug' => 'white',
			'color' => '#ffffff',
		),
	) );

	// Add styles for classic editor.
	add_editor_style( array( 'editor-style.css', pukeko_fonts_url() ) );

	// Register Navigation menu.
	register_nav_menus( array(
		'menu-1' 					=> esc_html__( 'Primary', 'pukeko' ),
		'menu-2' 					=> esc_html__( 'Footer', 'pukeko' ),
		'social-header' 	=> esc_html__( 'Header Social Icons', 'pukeko' ),
		'social-footer' 	=> esc_html__( 'Footer Social Icons', 'pukeko' ),
		'cta-header' 			=> esc_html__( 'Header CTA Buttons', 'pukeko' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	}
endif;
add_action( 'after_setup_theme', 'pukeko_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function pukeko_content_width() {
	// Fullwidth page template.
	if ( is_page_template( 'page-templates/fullwidth-page.php') || is_page_template( 'page-templates/fullwidth-notitle-page.php') ) {
		$GLOBALS['content_width'] = apply_filters( 'pukeko_content_width', 1200 );
	}
	// Fullwidth page template.
	if ( is_page_template( 'page-templates/fullscreen-page.php') ) {
		$GLOBALS['content_width'] = apply_filters( 'pukeko_content_width', 2010 );
	} else {
		$GLOBALS['content_width'] = apply_filters( 'pukeko_content_width', 784 );
	}
}
add_action( 'after_setup_theme', 'pukeko_content_width', 0 );

/**
 * Register custom fonts.
 */
 if ( ! function_exists( 'pukeko_fonts_url' ) ) :

 function pukeko_fonts_url() {
		$fonts_url = '';
		$fonts     = array();

		/* translators: If there are characters in your language that are not supported by the font, translate this to 'off'. Do not translate into your own language. */
		if ( 'off' !== esc_html_x( 'on', 'Cardo font: on or off', 'pukeko' ) ) {
			$fonts[] = 'Cardo:400,400i,700';
		}

		/* translators: If there are characters in your language that are not supported by the font, translate this to 'off'. Do not translate into your own language. */
		if ( 'off' !== esc_html_x( 'on', 'Roboto font: on or off', 'pukeko' ) ) {
			$fonts[] = 'Roboto:300,300i,400,400i,500,500i,700,700i';
		}

		if ( $fonts ) {
			$fonts_url = add_query_arg( array(
				'family' => urlencode( implode( '|', $fonts ) ),
				'subset' => urlencode( 'latin,latin-ext' ),
			), 'https://fonts.googleapis.com/css' );
		}

	return esc_url_raw( $fonts_url );
 }
 endif;

 /**
* Add preconnect for Google Fonts.
*
* @since Pukeko 1.2
*
* @param array  $urls           URLs to print for resource hints.
* @param string $relation_type  The relation type the URLs are printed.
* @return array $urls           URLs to print for resource hints.
*/
	function pukeko_resource_hints( $urls, $relation_type ) {
		if ( wp_style_is( 'pukeko-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
			$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
	}
	add_filter( 'wp_resource_hints', 'pukeko_resource_hints', 10, 2 );

	/**
	* Enqueue block editor style
	*/
	function pukeko_block_editor_styles() {
		wp_enqueue_style( 'pukeko-block-editor-styles', get_theme_file_uri( '/editor-style.css' ), false, '1.0', 'all' );
	}

	add_action( 'enqueue_block_editor_assets', 'pukeko_block_editor_styles' );


	/**
	* Register widget area.
	*/
	function pukeko_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Blog Sidebar', 'pukeko' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here to appear in your blog sidebar.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Page Sidebar', 'pukeko' ),
		'id'            => 'sidebar-2',
		'description'   => esc_html__( 'Add widgets here to appear in your page sidebar.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 1', 'pukeko' ),
		'id'            => 'footer-1',
		'description'   => esc_html__( 'Add widgets here to appear in the 1. column of your footer.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 2', 'pukeko' ),
		'id'            => 'footer-2',
		'description'   => esc_html__( 'Add widgets here to appear in the 2. column of your footer.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 3', 'pukeko' ),
		'id'            => 'footer-3',
		'description'   => esc_html__( 'Add widgets here to appear in the 3. column of your footer.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 4', 'pukeko' ),
		'id'            => 'footer-4',
		'description'   => esc_html__( 'Add widgets here to appear in the 4. column of your footer.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 5', 'pukeko' ),
		'id'            => 'footer-5',
		'description'   => esc_html__( 'Add widgets here to appear in the 5. column of your footer.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 6', 'pukeko' ),
		'id'            => 'footer-6',
		'description'   => esc_html__( 'Add widgets here to appear in the 6. column of your footer.', 'pukeko' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title f1">',
		'after_title'   => '</h2>',
	) );

	}
	add_action( 'widgets_init', 'pukeko_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
 function pukeko_scripts() {
	 wp_enqueue_style( 'pukeko-style', get_stylesheet_uri() );

	 wp_enqueue_style( 'pukeko-fonts', pukeko_fonts_url(), array(), null );

	 wp_enqueue_script( 'pukeko-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), array(), '1.0', true );

	 if ( has_nav_menu( 'menu-1' ) ) {
		 wp_enqueue_script( 'pukeko-navigation', get_theme_file_uri( '/assets/js/navigation.js' ), array( 'jquery' ), '1.0', true );
		 $pukeko_l10n['expand']         = __( 'Expand child menu', 'pukeko' );
		 $pukeko_l10n['collapse']       = __( 'Collapse child menu', 'pukeko' );
		 $pukeko_l10n['icon']           = pukeko_get_svg( array( 'icon' => 'caret-down', 'fallback' => true ) );
	 }

	 wp_enqueue_script( 'pukeko-custom', get_theme_file_uri( '/assets/js/custom.js' ), array( 'jquery' ), '1.0', true );

	 wp_localize_script( 'pukeko-skip-link-focus-fix', 'pukekoScreenReaderText', $pukeko_l10n );

	 if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		 wp_enqueue_script( 'comment-reply' );
	 }

	 global $is_IE;
	 if ( $is_IE ) {
	 	wp_enqueue_script( 'iepolyfill', get_theme_file_uri( '/assets/js/svgxuse.min.js' ), array(), '1.2.6', true );
	 }

 }
 add_action( 'wp_enqueue_scripts', 'pukeko_scripts' );

/**
 * Optimize Featured Header Image size
 */
function pukeko_header_image_sizes_attr( $html, $header, $attr ) {
		if ( isset( $attr['sizes'] ) ) {
				$html = str_replace( $attr['sizes'], '(max-aspect-ratio: 5/3) calc(100vh * (5 / 3)), 100vw', $html );
		}
		return $html;
}
add_filter( 'get_header_image_tag', 'pukeko_header_image_sizes_attr', 10 , 3 );

/**
 * Exclude sticky posts from default blog.
 */
function pukeko_exclude_sticky($sticky) {
	 if ( $sticky->is_home() && $sticky->is_main_query() )
			 $sticky->set( 'post__not_in', get_option( 'sticky_posts' ) );
}
add_action( 'pre_get_posts', 'pukeko_exclude_sticky');

/**
 * Add a custom max excerpt length.
 */
 function pukeko_custom_excerpt_length($limit) {

	 //check if its chinese character input, otherwise use the original code
	 $characterfont_output = preg_match_all("/\p{Han}+/u", get_the_content(), $matches);
	 if( $characterfont_output ) {
		 $excerpt = mb_substr( get_the_content(), 0, $limit ) . '&hellip;';
	 } else {
		 $excerpt = explode(' ', get_the_excerpt(), $limit);
		 if (count($excerpt)>=$limit) {
			 array_pop($excerpt);
			 $excerpt = implode(" ",$excerpt).'&hellip;';
		 } else {
			 $excerpt = implode(" ",$excerpt);
		 }
	 }

	 $excerpt = preg_replace('`[[^]]*]`','',$excerpt);

	 return $excerpt;
 }

/**
 * Replace "[...]" with custom read more in excerpts.
 */
function pukeko_excerpt_more($more) {
	global $post;
	return '&hellip;';
}
add_filter('excerpt_more', 'pukeko_excerpt_more');

/**
 * Customize Author Archive Title.
 */
add_filter( 'get_the_archive_title', function ( $title ) {
		if( is_author() ) {
				$title = '<span>' . esc_html__( 'All posts by', 'pukeko') . '</span>';
		}
		if( is_category() ) {
				$title = '<span>' . esc_html__( 'Filed under', 'pukeko') . '</span>' . single_cat_title( '', false);
		}
		if( is_tag() ) {
				$title = '<span>' . esc_html__( 'Filed under', 'pukeko') . '</span>' . single_tag_title( '', false);
		}
		return $title;
});

/**
 * Get list of post categories without links.
 */
function pukeko_the_categories() {
	$cats = array();
	foreach (get_the_category() as $c) {
	$cat = get_category($c);
	array_push($cats, $cat->name);
	}

	if (sizeOf($cats) > 0) {
	$post_categories = implode(', ', $cats);
	} else {
	$post_categories = '';
	}

	echo $post_categories;
}

/**
 * Callback to change just html output on a comment.
 */
function pukeko_comments_callback($comment, $args, $depth){
	 //checks if were using a div or ol|ul for our output
	 $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
?>
<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $args['has_children'] ? 'parent' : '', $comment ); ?>>
	<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
		<div class="comment-content-wrap">
			<div class="comment-author vcard">
				<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
				<?php printf( __( '%s <span class="says">says:</span>', 'pukeko' ), sprintf( '<b class="fn">%s</b>', get_comment_author_link( $comment ) ) ); ?>
			</div><!-- .comment-author -->
			<div class="comment-content">
				<?php comment_text(); ?>
			</div><!-- .comment-content -->
		</div><!-- .comment-content-wrap -->
		<footer class="comment-meta">
			<div class="comment-metadata">
				<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
					<time datetime="<?php comment_time( 'c' ); ?>">
					<?php
					/* translators: 1: comment date, 2: comment time */
					printf( __( '%1$s at %2$s', 'pukeko' ), get_comment_date( '', $comment ), get_comment_time() );
					?>
					</time>
				</a>
				<?php
				comment_reply_link( array_merge( $args, array(
				'add_below' => 'div-comment',
				'depth'     => $depth,
				'max_depth' => $args['max_depth'],
				'before'    => '<div class="reply">',
				'after'     => '</div>'
				) ) );
				?>
				<?php edit_comment_link( __( 'Edit', 'pukeko' ), '<span class="edit-link">', '</span>' ); ?>
			</div><!-- .comment-metadata -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'pukeko' ); ?></p>
			<?php endif; ?>
		</footer><!-- .comment-meta -->
	</article><!-- .comment-body -->
	 <?php
}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Additional features to allow styling of the templates.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load widget file.
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * SVG icons functions and filters.
 */
require get_parent_theme_file_path( '/inc/icon-functions.php' );

/**
 * Add Admin Welcome screen.
 */
require get_template_directory() . '/inc/welcome-screen/welcome-page-setup.php';

/**
 * Add One Click Demo Import code.
 */
require get_template_directory() . '/inc/demo-installer.php';
