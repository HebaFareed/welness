jQuery(document).ready(function () {

		/* If there are required actions, add an icon with the number of required actions in the About pukeko page -> Actions required tab */
		var pukeko_nr_actions_required = pukekoWelcomeScreenObject.nr_actions_required;

		if ((typeof pukeko_nr_actions_required !== 'undefined') && (pukeko_nr_actions_required != '0')) {
				jQuery('li.pukeko-w-red-tab a').append('<span class="pukeko-actions-count">' + pukeko_nr_actions_required + '</span>');
		}


		/* Dismiss required actions */
		jQuery(".pukeko-required-action-button").click(function () {

				var id = jQuery(this).attr('id'),
						action = jQuery(this).attr('data-action');
				jQuery.ajax({
						type      : "GET",
						data      : { action: 'pukeko_dismiss_required_action', id: id, todo: action },
						dataType  : "html",
						url       : pukekoWelcomeScreenObject.ajaxurl,
						beforeSend: function (data, settings) {
								jQuery('.pukeko-tab-pane#actions_required h1').append('<div id="temp_load" style="text-align:center"><img src="' + pukekoWelcomeScreenObject.template_directory + '/inc/admin/welcome-screen/img/ajax-loader.gif" /></div>');
						},
						success   : function (data) {
								location.reload();
								jQuery("#temp_load").remove();
								/* Remove loading gif */
						},
						error     : function (jqXHR, textStatus, errorThrown) {
								console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
						}
				});
		});

		/* Dismiss recommended plugins */
		jQuery(".pukeko-recommended-plugin-button").click(function () {

				var id = jQuery(this).attr('id'),
						action = jQuery(this).attr('data-action');
				jQuery.ajax({
						type      : "GET",
						data      : { action: 'pukeko_dismiss_recommended_plugins', id: id, todo: action },
						dataType  : "html",
						url       : pukekoWelcomeScreenObject.ajaxurl,
						beforeSend: function (data, settings) {
								jQuery('.pukeko-tab-pane#actions_required h1').append('<div id="temp_load" style="text-align:center"><img src="' + pukekoWelcomeScreenObject.template_directory + '/inc/admin/welcome-screen/img/ajax-loader.gif" /></div>');
						},
						success   : function (data) {
								location.reload();
								jQuery("#temp_load").remove();
								/* Remove loading gif */
						},
						error     : function (jqXHR, textStatus, errorThrown) {
								console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
						}
				});
		});

});
