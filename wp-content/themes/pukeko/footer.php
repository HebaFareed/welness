<?php
 /**
	* The template for displaying the footer
	*
	* @package Pukeko
	* @since Pukeko 1.0.0
	* @version 1.0.0
	*/
?>

	</div><!-- #content -->
</div><!-- .content-wrap -->

	<footer id="colophon" class="site-footer" role="contentinfo">

		<div class="footer-wrap cf">

				<?php get_template_part( 'template-parts/footer/footer', 'widgets' ); ?>

				<?php get_template_part( 'template-parts/footer/footer', 'menus' ); ?>

				<?php get_template_part( 'template-parts/footer/site', 'info' ); ?>

		</div><!-- .footer-container -->

	</footer><!-- #colophon -->
</div><!-- #page -->
<div class="whatsapp">
    <a href="https://wa.me/201019666330" target="_blank">
        <img src="/wp-content/uploads/2022/02/whatsapp.png" alt="Click to Chat">
    </a>
</div>
<?php wp_footer(); ?>
<script>
jQuery(document).ready(function () {
	var $nav = jQuery(".team_tab"),
		posTop = $nav.position().top + 150;
	jQuery(window).scroll(function () {
		var y = jQuery(this).scrollTop();
		if (y >= posTop) {
			$nav.css({
				position: "fixed",
				top: "0px",
				left: "0",
				margin: "0",
				padding: "",
				width: "100%",
				"z-index": "99",
			});
		} else {
			$nav.css("position", "relative");
		}
	});
});

</script>
</body>
</html>
