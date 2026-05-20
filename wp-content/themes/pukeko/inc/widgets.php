<?php
/**
 * Custom Theme Widgets
 *
 * @package Pukeko
 * @since 1.0.0
 * @version 1.0.4
 */

 /* ----------------------------------------------------------------------------
	* Widget: Section Title
	* ------------------------------------------------------------------------- */

 class pukeko_sectiontitle_widget extends WP_Widget {

	/**
	* step 1: Register widget with WordPress.
	*/
	public function __construct() {
		parent::__construct( 'pukeko-sectiontitle-widget', esc_html__( 'Pukeko - Section Title', 'pukeko' ) , array(
			'classname'   => ( 'pukeko_widget pukeko_sectiontitle left' ),
			'description' => esc_html__( 'Section title for Pukeko.', 'pukeko' ),
		) );
	}

	/**
	* step 2: Front-end display of widget.
	*
	*/
	 public function widget( $args, $instance ) {

		 $title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		 $sectiontitle_text = empty( $instance['sectiontitle_text'] ) ? '' : $instance['sectiontitle_text'];
		 $sectiontitle_btn_label = empty( $instance['sectiontitle_btn_label'] ) ? '' : $instance['sectiontitle_btn_label'];
		 $sectiontitle_btn_link = empty( $instance['sectiontitle_btn_link'] ) ? '' : $instance['sectiontitle_btn_link'];
		 $sectiontitle_btn_style = empty( $instance['sectiontitle_btn_style'] ) ? '' : $instance['sectiontitle_btn_style'];
		 $sectiontitle_alignment = empty( $instance['sectiontitle_alignment'] ) ? '' : $instance['sectiontitle_alignment'];

		 if ( ($sectiontitle_alignment == 'center') ) {
			 $args['before_widget'] = str_replace( 'left', 'center', $args['before_widget'] );
		 }

		 if ( ($sectiontitle_btn_style == 'primary') ) {
			 $sectiontitle_btn_style_class = 'btn-primary';
		 } elseif ($sectiontitle_btn_style == 'secondary') {
			 $sectiontitle_btn_style_class = 'btn-secondary';
		 } elseif ($sectiontitle_btn_style == 'flat') {
			 $sectiontitle_btn_style_class = 'btn-naked';
		 } elseif ($sectiontitle_btn_style == 'outlined') {
			 $sectiontitle_btn_style_class = 'btn-outlined';
		 } else {
			 $sectiontitle_btn_style_class = 'btn-naked';
		 }

		 echo $args['before_widget'];

	 ?>

		<div class="pukeko-sectiontitle-wrap <?php echo ( esc_attr($sectiontitle_alignment) ); ?>">

		 <?php if ($title != '') : ?>
			 <h2 class="section-title"><?php echo ( esc_attr($title) ); ?></h2>
		 <?php endif; ?>

		 <?php if ($sectiontitle_text != '') : ?>
			 <?php echo ( wp_kses_post(wpautop($sectiontitle_text) ) ); ?>
		 <?php endif; ?>

		 <?php if ($sectiontitle_btn_label != '' && $sectiontitle_btn_link != '' ) : ?>
			<a class="sectiontitle-btn btn btn-m <?php echo ( esc_attr($sectiontitle_btn_style_class) ); ?>" href="<?php echo ( esc_url($sectiontitle_btn_link) ); ?>"><?php echo ( esc_html($sectiontitle_btn_label) ); ?></a>
		 <?php endif; ?>

	 </div>

	 <?php

		 echo $args['after_widget'];
	 }

	 /**
	 * step 3: Back-end widget form. Create the admin area widget settings form.
	 *
	 */

	 public function form( $instance ) {
		 $title = empty( $instance['title'] ) ? '' : $instance['title'];
		 $sectiontitle_text = empty( $instance['sectiontitle_text'] ) ? '' : $instance['sectiontitle_text'];
		 $sectiontitle_btn_label = empty( $instance['sectiontitle_btn_label'] ) ? '' : $instance['sectiontitle_btn_label'];
		 $sectiontitle_btn_link = empty( $instance['sectiontitle_btn_link'] ) ? '' : $instance['sectiontitle_btn_link'];
		 $sectiontitle_btn_style = empty( $instance['sectiontitle_btn_style'] ) ? '' : $instance['sectiontitle_btn_style'];
		 $sectiontitle_alignment = empty( $instance['sectiontitle_alignment'] ) ? '' : $instance['sectiontitle_alignment'];
	 ?>

	 <p>
		 <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'pukeko' ); ?></label>
		 <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
	 </p>

	 <p>
		 <label for="<?php echo $this->get_field_id('sectiontitle_text'); ?>"><?php esc_html_e('Text:','pukeko'); ?></label>
		 <textarea name="<?php echo $this->get_field_name('sectiontitle_text'); ?>" class="widefat" rows="8" cols="12" id="<?php echo $this->get_field_id('sectiontitle_text'); ?>"><?php echo( $sectiontitle_text ); ?></textarea>
	 </p>

	 <p>
	 <label for="<?php echo $this->get_field_id('sectiontitle_btn_label'); ?>"><?php esc_html_e('Button Label:','pukeko'); ?></label>
	 <input type="text" name="<?php echo $this->get_field_name('sectiontitle_btn_label'); ?>" value="<?php echo esc_attr($sectiontitle_btn_label); ?>" class="widefat" id="<?php echo $this->get_field_id('sectiontitle_btn_label'); ?>">
	 </p>

	 <p>
	 <label for="<?php echo $this->get_field_id('sectiontitle_btn_link'); ?>"><?php esc_html_e('Button Link:','pukeko'); ?></label>
	 <input type="text" name="<?php echo $this->get_field_name('sectiontitle_btn_link'); ?>" value="<?php echo esc_attr($sectiontitle_btn_link); ?>" class="widefat" id="<?php echo $this->get_field_id('sectiontitle_btn_link'); ?>">
	 </p>

	 <p>
		<label for="<?php echo $this->get_field_id('sectiontitle_btn_style'); ?>"><?php esc_html_e('Button Style', 'pukeko'); ?></label>
		<select name="<?php echo $this->get_field_name('sectiontitle_btn_style'); ?>" id="<?php echo $this->get_field_id('sectiontitle_btn_style'); ?>" class="widefat">
		<?php
		$options = array('primary', 'outlined', 'secondary', 'naked');
		foreach ($options as $option) {
		echo '<option value="' . $option . '" id="' . $option . '"', $sectiontitle_btn_style == $option ? ' selected="selected"' : '', '>', $option, '</option>';
		}
		?>
		</select>
	 </p>

	 <p>
		<label for="<?php echo $this->get_field_id('sectiontitle_alignment'); ?>"><?php esc_html_e('Alignment', 'pukeko'); ?></label>
		<select name="<?php echo $this->get_field_name('sectiontitle_alignment'); ?>" id="<?php echo $this->get_field_id('sectiontitle_alignment'); ?>" class="widefat">
		<?php
		$options = array('left', 'center');
		foreach ($options as $option) {
		echo '<option value="' . $option . '" id="' . $option . '"', $sectiontitle_alignment == $option ? ' selected="selected"' : '', '>', $option, '</option>';
		}
		?>
		</select>
	 </p>

 <?php
 }

	 /**
	 * step 4: Sanitize widget form values as they are saved.
	 *
	 */

	 public function update( $new_instance, $old_instance ) {
		 $instance = array();
		 $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		 $instance['sectiontitle_text'] = ( ! empty( $new_instance['sectiontitle_text'] ) ) ? $new_instance['sectiontitle_text'] : '';
		 $instance['sectiontitle_btn_label'] = ( ! empty( $new_instance['sectiontitle_btn_label'] ) ) ? strip_tags( $new_instance['sectiontitle_btn_label'] ) : '';
		 $instance['sectiontitle_btn_link'] = ( ! empty( $new_instance['sectiontitle_btn_link'] ) ) ? strip_tags( $new_instance['sectiontitle_btn_link'] ) : '';
		 $instance['sectiontitle_btn_style'] = ( ! empty( $new_instance['sectiontitle_btn_style'] ) ) ? strip_tags( $new_instance['sectiontitle_btn_style'] ) : '';
		 $instance['sectiontitle_alignment'] = ( ! empty( $new_instance['sectiontitle_alignment'] ) ) ? strip_tags( $new_instance['sectiontitle_alignment'] ) : '';

		return $instance;
	 }

 } // class pukeko_sectiontitle_widget


 // step 5: register the widget.
 function pukeko_register_sectiontitle_widget() {
	 register_widget( 'pukeko_sectiontitle_widget' );
 }
 add_action( 'widgets_init', 'pukeko_register_sectiontitle_widget' );



 /* ----------------------------------------------------------------------------
	* Widget: Page Title
	* ------------------------------------------------------------------------- */

 class pukeko_pagetitle_widget extends WP_Widget {

	/**
	* step 1: Register widget with WordPress.
	*/
	public function __construct() {
		parent::__construct( 'pukeko-pagetitle-widget', esc_html__( 'Pukeko - Page Title', 'pukeko' ) , array(
			'classname'   => ( 'pukeko_widget pukeko_pagetitle left' ),
			'description' => esc_html__( 'Page title for Pukeko.', 'pukeko' ),
		) );
	}

	/**
	* step 2: Front-end display of widget.
	*
	*/
	 public function widget( $args, $instance ) {

		 $title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		 $pagetitle_subtitle = empty( $instance['pagetitle_subtitle'] ) ? '' : $instance['pagetitle_subtitle'];
		 $pagetitle_text = empty( $instance['pagetitle_text'] ) ? '' : $instance['pagetitle_text'];
		 $pagetitle_alignment = empty( $instance['pagetitle_alignment'] ) ? '' : $instance['pagetitle_alignment'];

		 if ( ($pagetitle_alignment == 'center') ) {
			 $args['before_widget'] = str_replace( 'left', 'center', $args['before_widget'] );
		 }

		 echo $args['before_widget'];

	 ?>

	 <div class="pukeko-pagetitle-wrap <?php echo ( esc_attr($pagetitle_alignment) ); ?>">

		<?php if ($pagetitle_subtitle != '') : ?>
		 <span class="pagetitle-subtitle"><?php echo ( esc_attr($pagetitle_subtitle) ); ?></span>
		<?php endif; ?>

		 <?php if ($title != '') : ?>
			 <h1 class="section-title"><?php echo ( esc_attr($title) ); ?></h1>
		 <?php endif; ?>

		 <?php if ($pagetitle_text != '') : ?>
			 <?php echo ( wp_kses_post(wpautop($pagetitle_text) ) ); ?>
		 <?php endif; ?>

	 </div>

	 <?php

		 echo $args['after_widget'];
	 }

	 /**
	 * step 3: Back-end widget form. Create the admin area widget settings form.
	 *
	 */

	 public function form( $instance ) {
		 $title = empty( $instance['title'] ) ? '' : $instance['title'];
		 $pagetitle_subtitle = empty( $instance['pagetitle_subtitle'] ) ? '' : $instance['pagetitle_subtitle'];
		 $pagetitle_text = empty( $instance['pagetitle_text'] ) ? '' : $instance['pagetitle_text'];
		 $pagetitle_alignment = empty( $instance['pagetitle_alignment'] ) ? '' : $instance['pagetitle_alignment'];
	 ?>

	 <p>
		 <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'pukeko' ); ?></label>
		 <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
	 </p>

	 <p>
		 <label for="<?php echo $this->get_field_id('pagetitle_subtitle'); ?>"><?php esc_html_e('Subtitle:','pukeko'); ?></label>
		 <input name="<?php echo $this->get_field_name('pagetitle_subtitle'); ?>" class="widefat" id="<?php echo $this->get_field_id('pagetitle_subtitle'); ?>" type="text" value="<?php echo esc_attr( $pagetitle_subtitle ); ?>">
	 </p>

	 <p>
		 <label for="<?php echo $this->get_field_id('pagetitle_text'); ?>"><?php esc_html_e('Text:','pukeko'); ?></label>
		 <textarea name="<?php echo $this->get_field_name('pagetitle_text'); ?>" class="widefat" rows="8" cols="12" id="<?php echo $this->get_field_id('pagetitle_text'); ?>"><?php echo( $pagetitle_text ); ?></textarea>
	 </p>

	 <p>
		<label for="<?php echo $this->get_field_id('pagetitle_alignment'); ?>"><?php esc_html_e('Alignment', 'pukeko'); ?></label>
		<select name="<?php echo $this->get_field_name('pagetitle_alignment'); ?>" id="<?php echo $this->get_field_id('pagetitle_alignment'); ?>" class="widefat">
		<?php
		$options = array('left', 'center');
		foreach ($options as $option) {
		echo '<option value="' . $option . '" id="' . $option . '"', $pagetitle_alignment == $option ? ' selected="selected"' : '', '>', $option, '</option>';
		}
		?>
		</select>
	 </p>

 <?php
 }

	/**
	* step 4: Sanitize widget form values as they are saved.
	*
	*/
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['pagetitle_subtitle'] = ( ! empty( $new_instance['pagetitle_subtitle'] ) ) ? strip_tags( $new_instance['pagetitle_subtitle'] ) : '';
		$instance['pagetitle_text'] = ( ! empty( $new_instance['pagetitle_text'] ) ) ? strip_tags( $new_instance['pagetitle_text'] ) : '';
		$instance['pagetitle_alignment'] = ( ! empty( $new_instance['pagetitle_alignment'] ) ) ? strip_tags( $new_instance['pagetitle_alignment'] ) : '';

	return $instance;
	}

 } // class pukeko_sectiontitle_widget


	// step 5: register the widget.
	function pukeko_register_pagetitle_widget() {
		register_widget( 'pukeko_pagetitle_widget' );
	}
	add_action( 'widgets_init', 'pukeko_register_pagetitle_widget' );



/* ----------------------------------------------------------------------------
 * Widget: Product
 * ------------------------------------------------------------------------- */

 class pukeko_product_widget extends WP_Widget {

	/**
	* step 1: Register widget with WordPress.
	*/
	public function __construct() {
		parent::__construct( 'pukeko-product-widget', esc_html__( 'Pukeko - Product', 'pukeko' ) , array(
			'classname'   => ( 'pukeko_widget pukeko_product fullwidth product-left' ),
			'description' => esc_html__( 'Product widget for Pukeko.', 'pukeko' ),
		) );
	}

	/**
	* step 2: Front-end display of widget.
	*
	*/
	public function widget( $args, $instance ) {

		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$product_subtitle = empty( $instance['product_subtitle'] ) ? '' : $instance['product_subtitle'];
		$image = empty( $instance['image'] ) ? '' : $instance['image'];
		$product_text = empty( $instance['product_text'] ) ? '' : $instance['product_text'];
		$product_btn_label = empty( $instance['product_btn_label'] ) ? '' : $instance['product_btn_label'];
		$product_btn_link = empty( $instance['product_btn_link'] ) ? '' : $instance['product_btn_link'];
		$product_btn_style = empty( $instance['product_btn_style'] ) ? '' : $instance['product_btn_style'];
		$product_quote_text = empty( $instance['product_quote_text'] ) ? '' : $instance['product_quote_text'];
		$product_quote_author = empty( $instance['product_quote_author'] ) ? '' : $instance['product_quote_author'];

		$product_quote_avatar_email = empty( $instance['product_quote_avatar_email'] ) ? '' : $instance['product_quote_avatar_email'];

		$product_alignment = empty( $instance['product_alignment'] ) ? '' : $instance['product_alignment'];
		$product_layout = empty( $instance['product_layout'] ) ? '' : $instance['product_layout'];

		if ( ($product_layout == 'fullscreen') ) {
			$args['before_widget'] = str_replace( 'fullwidth', 'fullscreen', $args['before_widget'] );
		}

		if ( ($product_alignment == 'right') ) {
			$args['before_widget'] = str_replace( 'product-left', 'product-right', $args['before_widget'] );
		}

		if ( ($product_btn_style == 'primary') ) {
			$product_btn_style_class = 'btn-primary';
		}

		if ( ($product_btn_style == 'secondary') ) {
			$product_btn_style_class = 'btn-secondary';
		}

		if ( ($product_btn_style == 'outlined') ) {
			$product_btn_style_class = 'btn-outlined';
		}

		if ( ($product_btn_style == 'ghost') ) {
			$product_btn_style_class = 'btn-primary';
		}

		if ( ($product_btn_style == 'naked') || ($product_btn_style == 'flat')  ) {
			$product_btn_style_class = 'btn-naked';
		}

		echo $args['before_widget'];

	?>

	<div class="pukeko-product-wrap <?php echo ( esc_attr($product_layout) ); ?> <?php echo ( esc_attr($product_alignment) ); ?>">

		<?php if ($image != '') : ?>
		<div class="product-img-wrap">
			<img src="<?php echo ( esc_url($image) ); ?>" alt="img" class="product-img">
		</div>
		<?php endif; ?>

		<div class="product-content-wrap">

			<div class="product-content">
			<?php if ($product_subtitle != '') : ?>
				<span class="product-subtitle"><?php echo ( esc_attr($product_subtitle) ); ?></span>
			<?php endif; ?>

			<?php if ($title != '') : ?>
				<h3 class="section-title"><?php echo ( esc_attr($title) ); ?></h3>
			<?php endif; ?>

			<?php if ($product_text != '') : ?>
				<?php echo ( wp_kses_post(wpautop($product_text) ) ); ?>
			<?php endif; ?>

			<?php if ($product_btn_label != '' && $product_btn_link != '' ) : ?>
			 <a class="product-btn btn btn-m <?php echo ( esc_attr($product_btn_style_class) ); ?>" href="<?php echo ( esc_url($product_btn_link) ); ?>"><?php echo ( esc_html($product_btn_label) ); ?></a>
			<?php endif; ?>

			<?php if ($product_quote_text != '') : ?>
				<blockquote><?php echo ( wp_kses_post(wpautop($product_quote_text) ) ); ?>
					<?php if ($product_quote_author != '') : ?>
						<cite>

						<?php if ($product_quote_avatar_email != '' && is_email( $product_quote_avatar_email ) && get_avatar( $product_quote_avatar_email ) ) : ?>
							<span>
							<?php echo get_avatar( $product_quote_avatar_email , 56 ); ?>
							</span>
							<?php endif; ?>

							<?php echo ( esc_html($product_quote_author) ); ?></cite>
					<?php endif; ?>
				</blockquote>
			<?php endif; ?>

			</div>
		</div>
	</div>

	<?php

		echo $args['after_widget'];
	}

	/**
	* step 3: Back-end widget form. Create the admin area widget settings form.
	*
	*/

	public function form( $instance ) {
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		$product_subtitle = empty( $instance['product_subtitle'] ) ? '' : $instance['product_subtitle'];
		$image = empty( $instance['image'] ) ? '' : $instance['image'];
		$product_text = empty( $instance['product_text'] ) ? '' : $instance['product_text'];
		$product_btn_label = empty( $instance['product_btn_label'] ) ? '' : $instance['product_btn_label'];
		$product_btn_link = empty( $instance['product_btn_link'] ) ? '' : $instance['product_btn_link'];
		$product_btn_style = empty( $instance['product_btn_style'] ) ? '' : $instance['product_btn_style'];
		$product_quote_text = empty( $instance['product_quote_text'] ) ? '' : $instance['product_quote_text'];
		$product_quote_author = empty( $instance['product_quote_author'] ) ? '' : $instance['product_quote_author'];
		$product_quote_avatar_email = empty( $instance['product_quote_avatar_email'] ) ? '' : $instance['product_quote_avatar_email'];
		$product_alignment = empty( $instance['product_alignment'] ) ? '' : $instance['product_alignment'];
		$product_layout = empty( $instance['product_layout'] ) ? '' : $instance['product_layout'];
	?>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'pukeko' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('product_subtitle'); ?>"><?php esc_html_e('Subtitle:','pukeko'); ?></label>
		<input name="<?php echo $this->get_field_name('product_subtitle'); ?>" class="widefat" id="<?php echo $this->get_field_id('product_subtitle'); ?>" type="text" value="<?php echo esc_attr( $product_subtitle ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('product_text'); ?>"><?php esc_html_e('Text:','pukeko'); ?></label>
		<textarea name="<?php echo $this->get_field_name('product_text'); ?>" class="widefat" rows="8" cols="12" id="<?php echo $this->get_field_id('product_text'); ?>"><?php echo( $product_text ); ?></textarea>
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('product_btn_label'); ?>"><?php esc_html_e('Button Label:','pukeko'); ?></label>
	<input name="<?php echo $this->get_field_name('product_btn_label'); ?>" class="widefat" id="<?php echo $this->get_field_id('product_btn_label'); ?>" type="text" value="<?php echo esc_attr( $product_btn_label ); ?>">
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('product_btn_link'); ?>"><?php esc_html_e('Button Link:','pukeko'); ?></label>
	<input name="<?php echo $this->get_field_name('product_btn_link'); ?>" class="widefat" id="<?php echo $this->get_field_id('product_btn_link'); ?>" type="text" value="<?php echo esc_attr( $product_btn_link ); ?>">
	</p>

	<p>
	 <label for="<?php echo $this->get_field_id('product_btn_style'); ?>"><?php esc_html_e('Button Style', 'pukeko'); ?></label>
	 <select name="<?php echo $this->get_field_name('product_btn_style'); ?>" id="<?php echo $this->get_field_id('product_btn_style'); ?>" class="widefat">
	 <?php
	 $options = array('primary', 'outlined', 'secondary', 'naked' );
	 foreach ($options as $option) {
	 echo '<option value="' . $option . '" id="' . $option . '"', $product_btn_style == $option ? ' selected="selected"' : '', '>', $option, '</option>';
	 }
	 ?>
	 </select>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('product_quote_text'); ?>"><?php esc_html_e('Quote Text:','pukeko'); ?></label>
		<textarea name="<?php echo $this->get_field_name('product_quote_text'); ?>" class="widefat" rows="8" cols="12" id="<?php echo $this->get_field_id('product_quote_text'); ?>"><?php echo( $product_quote_text ); ?></textarea>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('product_quote_author'); ?>"><?php esc_html_e('Quote Author:','pukeko'); ?></label>
		<input name="<?php echo $this->get_field_name('product_quote_author'); ?>" class="widefat" id="<?php echo $this->get_field_id('product_quote_author'); ?>" type="text" value="<?php echo esc_attr( $product_quote_author ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('product_quote_avatar_email'); ?>"><?php esc_html_e('Quote Author Avatar Email:','pukeko'); ?></label>
		<input name="<?php echo $this->get_field_name('product_quote_avatar_email'); ?>" class="widefat" id="<?php echo $this->get_field_id('product_quote_avatar_email'); ?>" type="text" value="<?php echo esc_attr( $product_quote_avatar_email ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('image'); ?>"><?php esc_html_e('Image:','pukeko'); ?></label>
		<input name="<?php echo $this->get_field_name('image'); ?>" class="widefat" id="<?php echo $this->get_field_id('image'); ?>" type="text" value="<?php echo esc_attr( $image ); ?>">
	</p>

	<p>
	 <label for="<?php echo $this->get_field_id('product_alignment'); ?>"><?php esc_html_e('Image Alignment', 'pukeko'); ?></label>
	 <select name="<?php echo $this->get_field_name('product_alignment'); ?>" id="<?php echo $this->get_field_id('product_alignment'); ?>" class="widefat">
	 <?php
	 $options = array('left', 'right');
	 foreach ($options as $option) {
	 echo '<option value="' . $option . '" id="' . $option . '"', $product_alignment == $option ? ' selected="selected"' : '', '>', $option, '</option>';
	 }
	 ?>
	 </select>
	</p>

	<p>
	 <label for="<?php echo $this->get_field_id('product_layout'); ?>"><?php esc_html_e('Layout', 'pukeko'); ?></label>
	 <select name="<?php echo $this->get_field_name('product_layout'); ?>" id="<?php echo $this->get_field_id('product_layout'); ?>" class="widefat">
	 <?php
	 $options = array('fullwidth', 'fullscreen');
	 foreach ($options as $option) {
	 echo '<option value="' . $option . '" id="' . $option . '"', $product_layout == $option ? ' selected="selected"' : '', '>', $option, '</option>';
	 }
	 ?>
	 </select>
	</p>

<?php
}

	/**
	* step 4: Sanitize widget form values as they are saved.
	*
	*/

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['product_subtitle'] = ( ! empty( $new_instance['product_subtitle'] ) ) ? strip_tags( $new_instance['product_subtitle'] ) : '';
		$instance['image'] = ( ! empty( $new_instance['image'] ) ) ? strip_tags( $new_instance['image'] ) : '';
		$instance['product_text'] = ( ! empty( $new_instance['product_text'] ) ) ? strip_tags( $new_instance['product_text'] ) : '';
		$instance['product_btn_label'] = ( ! empty( $new_instance['product_btn_label'] ) ) ? strip_tags( $new_instance['product_btn_label'] ) : '';
		$instance['product_btn_link'] = ( ! empty( $new_instance['product_btn_link'] ) ) ? strip_tags( $new_instance['product_btn_link'] ) : '';
		$instance['product_btn_style'] = ( ! empty( $new_instance['product_btn_style'] ) ) ? strip_tags( $new_instance['product_btn_style'] ) : '';
		$instance['product_quote_text'] = ( ! empty( $new_instance['product_quote_text'] ) ) ? strip_tags( $new_instance['product_quote_text'] ) : '';
		$instance['product_quote_author'] = ( ! empty( $new_instance['product_quote_author'] ) ) ? strip_tags( $new_instance['product_quote_author'] ) : '';
		$instance['product_quote_avatar_email'] = ( ! empty( $new_instance['product_quote_avatar_email'] ) ) ? strip_tags( $new_instance['product_quote_avatar_email'] ) : '';
		$instance['product_alignment'] = ( ! empty( $new_instance['product_alignment'] ) ) ? strip_tags( $new_instance['product_alignment'] ) : '';
		$instance['product_layout'] = ( ! empty( $new_instance['product_layout'] ) ) ? strip_tags( $new_instance['product_layout'] ) : '';

	 return $instance;
	}

} // class pukeko_product_widget


// step 5: register the widget.
function pukeko_register_product_widget() {
	register_widget( 'pukeko_product_widget' );
}
add_action( 'widgets_init', 'pukeko_register_product_widget' );




/* ----------------------------------------------------------------------------
 * Widget: Team Member
 * ------------------------------------------------------------------------- */

class pukeko_teammember_widget extends WP_Widget {

	/**
	* step 1: Register widget with WordPress.
	*/
	public function __construct() {
		parent::__construct( 'pukeko-teammember-widget', esc_html__( 'Pukeko - Team Member', 'pukeko' ) , array(
			'classname'   => ( 'pukeko_widget pukeko_teammember' ),
			'description' => esc_html__( 'Team Member widget for Pukeko.', 'pukeko' ),
		) );
	}

	/**
	* step 2: Front-end display of widget.
	*
	*/
	public function widget( $args, $instance ) {

		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$teammember_role = empty( $instance['teammember_role'] ) ? '' : $instance['teammember_role'];
		$teammember_text = empty( $instance['teammember_text'] ) ? '' : $instance['teammember_text'];
		$teammember_social_one_label = empty( $instance['teammember_social_one_label'] ) ? '' : $instance['teammember_social_one_label'];
		$teammember_social_one_link = empty( $instance['teammember_social_one_link'] ) ? '' : $instance['teammember_social_one_link'];
		$teammember_social_two_label = empty( $instance['teammember_social_two_label'] ) ? '' : $instance['teammember_social_two_label'];
		$teammember_social_two_link = empty( $instance['teammember_social_two_link'] ) ? '' : $instance['teammember_social_two_link'];
		$teammember_social_three_label = empty( $instance['teammember_social_three_label'] ) ? '' : $instance['teammember_social_three_label'];
		$teammember_social_three_link = empty( $instance['teammember_social_three_link'] ) ? '' : $instance['teammember_social_three_link'];

		$teammember_image = empty( $instance['teammember_image'] ) ? '' : $instance['teammember_image'];

		echo $args['before_widget'];

	?>

		<?php if ($teammember_image != '') : ?>
		<div class="teammember-img-wrap">
				<img src="<?php echo ( esc_url($teammember_image) ); ?>" alt="team member" class="teammember-img">
	 </div>
		<?php endif; ?>

		<div class="teammember-content-wrap">
			<?php if ($title != '') : ?>
				<h2 class="section-title f1"><?php echo ( esc_attr($title) ); ?>
					<?php if ($teammember_role != '') : ?>
						<span class="teammember-role"><?php echo ( wp_kses_post($teammember_role) ); ?></span>
					<?php endif; ?>
				</h2>
			<?php endif; ?>

			<?php if ($teammember_text != '') : ?>
				<?php echo ( wp_kses_post(wpautop($teammember_text) ) ); ?>
			<?php endif; ?>

			<?php if ($teammember_social_one_link != '' || $teammember_social_two_link != '' || $teammember_social_three_link != '' ) : ?>
				<div class="teammember-social-wrap">

					<?php if ($teammember_social_one_link != '' ) : ?>
						<a class="teammember-social-btn" href="<?php
						$teammember_social_one_label = strtolower($teammember_social_one_label);
						echo ( esc_url($teammember_social_one_link) ); ?>">
							<span class="screen-reader-text"><?php echo ( esc_attr($teammember_social_one_label) ); ?></span>
							<?php echo pukeko_get_svg( array( 'icon' => $teammember_social_one_label ) ); ?>
						</a>
					<?php endif; ?>

					<?php if ($teammember_social_two_link != '' ) : ?>
						<a class="teammember-social-btn" href="<?php
						$teammember_social_two_label = strtolower($teammember_social_two_label);
						echo ( esc_url($teammember_social_two_link) ); ?>">
							<span class="screen-reader-text"><?php echo ( esc_attr($teammember_social_two_label) ); ?></span>
							<?php echo pukeko_get_svg( array( 'icon' => $teammember_social_two_label ) ); ?>
						</a>
					<?php endif; ?>

					<?php if ($teammember_social_three_link != '' ) : ?>
						<a class="teammember-social-btn" href="<?php
						$teammember_social_three_label = strtolower($teammember_social_three_label);
						echo ( esc_url($teammember_social_three_link) ); ?>">
							<span class="screen-reader-text"><?php echo ( esc_attr($teammember_social_three_label) ); ?></span>
							<?php echo pukeko_get_svg( array( 'icon' => $teammember_social_three_label ) ); ?>
						</a>
					<?php endif; ?>

				</div>
			<?php endif; ?>

		</div>

	<?php

		echo $args['after_widget'];
	}

	/**
	* step 3: Back-end widget form. Create the admin area widget settings form.
	*
	*/
	public function form( $instance ) {
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		$teammember_role = empty( $instance['teammember_role'] ) ? '' : $instance['teammember_role'];
		$teammember_text = empty( $instance['teammember_text'] ) ? '' : $instance['teammember_text'];
		$teammember_social_one_label = empty( $instance['teammember_social_one_label'] ) ? '' : $instance['teammember_social_one_label'];
		$teammember_social_one_link = empty( $instance['teammember_social_one_link'] ) ? '' : $instance['teammember_social_one_link'];
		$teammember_social_two_label = empty( $instance['teammember_social_two_label'] ) ? '' : $instance['teammember_social_two_label'];
		$teammember_social_two_link = empty( $instance['teammember_social_two_link'] ) ? '' : $instance['teammember_social_two_link'];
		$teammember_social_three_label = empty( $instance['teammember_social_three_label'] ) ? '' : $instance['teammember_social_three_label'];
		$teammember_social_three_link = empty( $instance['teammember_social_three_link'] ) ? '' : $instance['teammember_social_three_link'];

		$teammember_image = empty( $instance['teammember_image'] ) ? '' : $instance['teammember_image'];
	?>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Name:', 'pukeko' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_role'); ?>"><?php esc_html_e( 'Role:', 'pukeko' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'teammember_role' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'teammember_role' ) ); ?>" type="text" value="<?php echo esc_attr( $teammember_role ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_text'); ?>"><?php esc_html_e( 'Text:', 'pukeko' ); ?></label>
		<textarea name="<?php echo $this->get_field_name('teammember_text'); ?>" class="widefat" rows="8" cols="12" id="<?php echo $this->get_field_id('teammember_text'); ?>"><?php echo( $teammember_text ); ?></textarea>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'teammember_image' ) ); ?>"><?php esc_html_e( 'Image:', 'pukeko' ); ?></label>
	<input class="widefat" id=" <?php echo esc_attr( $this->get_field_id( 'teammember_image' ) ); ?> " name="<?php echo esc_attr( $this->get_field_name( 'teammember_image' ) ); ?>" type="text" value="<?php echo $teammember_image; ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_social_one_label'); ?>"><?php esc_html_e( 'Social Link One Label:', 'pukeko' ); ?></label>
		<input name="<?php echo $this->get_field_name('teammember_social_one_label'); ?>" class="widefat" id="<?php echo $this->get_field_id('teammember_social_one_label'); ?>" type="text" value="<?php echo esc_attr( $teammember_social_one_label ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_social_one_link'); ?>"><?php esc_html_e( 'Social Link One Link:', 'pukeko' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('teammember_social_one_link'); ?>" class="widefat" id="<?php echo $this->get_field_id('teammember_social_one_link'); ?>" type="text" value="<?php echo esc_attr( $teammember_social_one_link ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_social_two_label'); ?>"><?php esc_html_e( 'Social Link Two Label:', 'pukeko' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('teammember_social_two_label'); ?>" class="widefat" id="<?php echo $this->get_field_id('teammember_social_two_label'); ?>" type="text" value="<?php echo esc_attr( $teammember_social_two_label ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_social_two_link'); ?>"><?php esc_html_e( 'Social Link Two Link:', 'pukeko' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('teammember_social_two_link'); ?>" class="widefat" id="<?php echo $this->get_field_id('teammember_social_two_link'); ?>" type="text" value="<?php echo esc_attr( $teammember_social_two_link ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_social_three_label'); ?>"><?php esc_html_e( 'Social Link Three Label:', 'pukeko' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('teammember_social_three_label'); ?>" class="widefat" id="<?php echo $this->get_field_id('teammember_social_three_label'); ?>" type="text" value="<?php echo esc_attr( $teammember_social_three_label ); ?>">
	</p>

	<p>
		<label for="<?php echo $this->get_field_id('teammember_social_three_link'); ?>"><?php esc_html_e( 'Social Link Three Link:',  'pukeko' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('teammember_social_three_link'); ?>" class="widefat" id="<?php echo $this->get_field_id('teammember_social_three_link'); ?>" type="text" value="<?php echo esc_attr( $teammember_social_three_link ); ?>">
	</p>

<?php
}

	/**
	* step 4: Sanitize widget form values as they are saved.
	*
	*/

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['teammember_role'] = ( ! empty( $new_instance['teammember_role'] ) ) ? strip_tags( $new_instance['teammember_role'] ) : '';
		$instance['teammember_text'] = ( ! empty( $new_instance['teammember_text'] ) ) ? $new_instance['teammember_text'] : '';
		$instance['teammember_social_one_label'] = ( ! empty( $new_instance['teammember_social_one_label'] ) ) ? strip_tags( $new_instance['teammember_social_one_label'] ) : '';
		$instance['teammember_social_one_link'] = ( ! empty( $new_instance['teammember_social_one_link'] ) ) ? strip_tags( $new_instance['teammember_social_one_link'] ) : '';
		$instance['teammember_social_two_label'] = ( ! empty( $new_instance['teammember_social_two_label'] ) ) ? strip_tags( $new_instance['teammember_social_two_label'] ) : '';
		$instance['teammember_social_two_link'] = ( ! empty( $new_instance['teammember_social_two_link'] ) ) ? strip_tags( $new_instance['teammember_social_two_link'] ) : '';
		$instance['teammember_social_three_label'] = ( ! empty( $new_instance['teammember_social_three_label'] ) ) ? strip_tags( $new_instance['teammember_social_three_label'] ) : '';
		$instance['teammember_social_three_link'] = ( ! empty( $new_instance['teammember_social_three_link'] ) ) ? strip_tags( $new_instance['teammember_social_three_link'] ) : '';
		$instance['teammember_image'] = ( ! empty( $new_instance['teammember_image'] ) ) ? strip_tags( $new_instance['teammember_image'] ) : '';

	 return $instance;
	}

} // class pukeko_product_widget


// step 5: register the widget.
function pukeko_register_teammember_widget() {
	register_widget( 'pukeko_teammember_widget' );
}
add_action( 'widgets_init', 'pukeko_register_teammember_widget' );

?>
