<?php

/**
 * My Appointments
 *
 * Shows customer appointments on the My Account > Appointments page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/appointments.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @version     4.15.1
 * @since       3.4.0
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
?>
<noscript>
	<?php
	esc_html_e(
		'Your browser must support JavaScript in order to schedule an appointment.',
		'woocommerce-appointments'
	);
	?>
</noscript>
<?php
wp_enqueue_script('wc-appointments-my-account');

if (! empty($tables)) :

	$current_endpoint = WC()->query->get_current_endpoint();
	error_log('Current endpoint: ' . $current_endpoint);

	// merge upcoming and current appointments into a single table
	if (isset($tables['current']) && isset($tables['upcoming'])) {
		$tables['upcoming']['appointments'] = array_merge(
			$tables['current']['appointments'],
			$tables['upcoming']['appointments']
		);
		$tables['upcoming']['appointments_count'] = count($tables['upcoming']['appointments']);
		unset($tables['current']);
	} elseif (isset($tables['current']) && ! isset($tables['upcoming'])) {
		// if there is no upcoming appointments, but there are current appointments, rename current to upcoming
		$tables['upcoming'] = $tables['current'];
		unset($tables['current']);
	}



	// filter out cancelled appointments from upcoming appointments
	if (isset($tables['upcoming'])) {
		$cancelled_appointments = array_filter($tables['upcoming']['appointments'], function ($appointment) {
			return $appointment->get_status() === 'cancelled';
		});

		$tables['upcoming']['appointments'] = array_diff($tables['upcoming']['appointments'], $cancelled_appointments);

		// if there are no upcoming appointments, remove the table
		if (count($tables['upcoming']['appointments']) === 0) {
			unset($tables['upcoming']);
		}

		// merge cancelled appointments into past appointments
		if (is_array($cancelled_appointments) && count($cancelled_appointments) > 0) {
			if (! isset($tables['past'])) {

				$tables['past'] = array(
					'header' => esc_html__('Past & Cancelled', 'woocommerce-appointments'),
					'appointments_count' => 0,
					'appointments' => array(),
				);
			}
			$tables['past']['header'] = esc_html__('Past & Cancelled', 'woocommerce-appointments');
			$tables['past']['appointments'] = array_merge($tables['past']['appointments'], $cancelled_appointments);
			$tables['past']['appointments_count'] = count($tables['past']['appointments']);
		}

		// if current endpoint is not 0, show only upcoming appointments
		if (! isset($tables['upcoming'])) {
			// add to the top of the list
			$newTables['upcoming'] = array(
				'header' => esc_html__('Upcoming', 'woocommerce-appointments'),
				'appointments_count' => 0,
				'appointments' => array(),
			);

			$tables = array_merge($newTables, $tables);
		}

		if ($current_endpoint !== 0) {

			// unset other tables
			foreach ($tables as $table_id => $table) {
				if ($table_id !== 'upcoming') {
					unset($tables[$table_id]);
				}
			}
		}
	}

?>
	<?php
	foreach ($tables as $table_id => $table) :
		$i = 0;

		$table['header'] = $table['header']  . ' ' . esc_html__('Appointments', 'woocommerce-appointments');
	?>
		<h3><?php esc_html_e($table['header']); ?></h3>
		<?php
		// Check if the table has appointments
		if (empty($table['appointments']) && $table_id === 'upcoming') {

		?>
			<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
				<?php esc_html_e('No Upcoming appointments.', 'woocommerce-appointments'); ?>
				<a class="woocommerce-Button button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
					<?php esc_html_e('Book Now', 'woocommerce-appointments'); ?>
				</a>
			</div>
		<?php
			continue;
		}
		?>
		<table class="shop_table shop_table_responsive my_account_orders my_account_appointments <?php esc_html_e($table_id) . '_appointments'; ?>">
			<thead>
				<tr>
					<th scope="col" class="appointment-id">
						<span class="nobr"><?php esc_html_e('Appointment', 'woocommerce-appointments'); ?></span>
					</th>
					<th scope="col" class="appointment-when">
						<span class="nobr"><?php esc_html_e('When', 'woocommerce-appointments'); ?></span>
					</th>
					<th scope="col" class="scheduled-product">
						<span class="nobr"><?php esc_html_e('Scheduled', 'woocommerce-appointments'); ?></span>
					</th>
					<th scope="col" class="appointment-status">
						<span class="nobr"><?php esc_html_e('Status', 'woocommerce-appointments'); ?></span>
					</th>
					<th scope="col" class="appointment-actions"></th>
				</tr>
			</thead>
			<tbody>
				<?php

				foreach ($table['appointments'] as $appointment) :
					$i++;

					// Skip bookings which were added to detect pagination.
					if ($i > $appointments_per_page) {
						break;
					}

					// Can appointment be cancelled?
					$cancellable =
						'cancelled' !== $appointment->get_status() &&
						'completed' !== $appointment->get_status() &&
						! $appointment->passed_cancel_day();

					// Can appointment be rescheduled?
					$reschedulable =
						'cancelled' !== $appointment->get_status() &&
						'completed' !== $appointment->get_status() &&
						! $appointment->passed_reschedule_day();
				?>
					<tr>
						<td class="appointment-id anowrap" data-title="<?php esc_html_e('Appointment', 'woocommerce-appointments'); ?>">
							<?php
							printf(
								'#%d',
								esc_attr($appointment->get_id())
							);
							if ($appointment->get_order()) :
								if ('pending-confirmation' !== $appointment->get_status()) :
									printf(
										'<a href="%1$s" class="adesc">%2$s</a>',
										esc_url($appointment->get_order()->get_view_order_url()),
										esc_html__('Order', 'woocommerce-appointments')
									);
								endif;
							endif;
							?>
						</td>
						<td class="appointment-when anowrap" data-title="<?php esc_html_e('When', 'woocommerce-appointments'); ?>">
							<?php esc_attr_e($appointment->get_start_date()); ?>
							<span class="adesc"><?php esc_attr_e($appointment->get_duration()); ?></span>
						</td>
						<td class="scheduled-product" data-title="<?php esc_html_e('Scheduled', 'woocommerce-appointments'); ?>">
							<?php if ($appointment->get_product() && $appointment->get_product()->is_type('appointment')) : ?>
								<a href="<?php echo esc_url(get_permalink($appointment->get_product_id())); ?>">
									<?php esc_html_e($appointment->get_product_name()); ?>
								</a>
							<?php endif; ?>
						</td>
						<td class="appointment-status" data-title="<?php esc_html_e('Status', 'woocommerce-appointments'); ?>">
							<?php esc_html_e(wc_appointments_get_status_label($appointment->get_status())); ?>
						</td>
						<td class="appointment-actions" data-title="<?php esc_html_e('Actions', 'woocommerce-appointments'); ?>">
							<?php if ($reschedulable) : ?>
								<a href="<?php echo esc_url($appointment->get_reschedule_url()); ?>" class="woocommerce-button button single_add_to_cart_button btn btn-style-default btn-shape-rectangle anowrap reschedule_appointment">
									<?php esc_html_e('Reschedule', 'woocommerce-appointments'); ?>
								</a>
							<?php endif ?>
							<?php if ($cancellable) : ?>
								<a href="<?php echo esc_url($appointment->get_cancel_url()); ?>" class="woocommerce-button button anowrap btn single_add_to_cart_button btn-style-default btn-shape-rectangle cancel_appointment">
									<?php esc_html_e('Cancel', 'woocommerce-appointments'); ?>
								</a>
							<?php endif ?>

						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php do_action('woocommerce_before_account_appointments_pagination'); ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if (1 !== $page) : ?>
				<a href="<?php echo esc_url(wc_get_endpoint_url($endpoint, $page - 1)); ?>" class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"><?php esc_html_e('Previous', 'woocommerce-appointments'); ?></a>
			<?php endif; ?>

			<?php if (count($table['appointments']) > $appointments_per_page) : ?>
				<a href="<?php echo esc_url(wc_get_endpoint_url($endpoint, $page + 1)); ?>" class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"><?php esc_html_e('Next', 'woocommerce-appointments'); ?></a>
			<?php endif; ?>
		</div>
		<?php do_action('woocommerce_after_account_appointments_pagination'); ?>
	<?php endforeach; ?>
<?php else : ?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<?php esc_html_e('No appointments scheduled yet.', 'woocommerce-appointments'); ?>
		<a class="woocommerce-Button button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
			<?php esc_html_e('Book Now', 'woocommerce-appointments'); ?>
		</a>
	</div>
<?php endif; ?>

<!-- add script that shows alert box when clicking cancel, then when user accepts, proceed -->
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.cancel_appointment').on('click', function(e) {
			e.preventDefault();
			var cancelUrl = $(this).attr('href');

			<?php

			$separator = '</br>';

			// Get the cancellation allowance from the PHP variable
			$cancellationAllowance = get_wc_appointment_cancellation_policy_allowed_period();

			// get the fee

			$cancellationFee = get_wc_appointment_cancellation_fee();

			// get the currency symbol
			$currencySymbol = get_woocommerce_currency_symbol();

			// policy message
			$cancellationPolicyMessage =
				sprintf(
					esc_html__('You may cancel your appointment free of charge within %s %s hours %s of booking.', 'woocommerce-appointments'),
					'<strong>',
					$cancellationAllowance,
					'</strong>'

				);

			// If a cancellation fee is set, include it in the message
			$cancellationPolicyMessage .= $separator . sprintf(
				esc_html__('If you cancel after this period, a cancellation fee of %s%s%s%s will be applied.', 'woocommerce-appointments'),
				'<strong>',
				$currencySymbol,
				esc_html($cancellationFee),
				'</strong>'

			);

			// add confirm message "By clicking "I Agree, Proceed", you confirm that you understand the cancellation policy and agree to this fee."
			$cancellationPolicyMessage .= $separator . esc_html__('By clicking "I Agree, Proceed", you confirm that you understand the cancellation policy and agree to this fee.', 'woocommerce-appointments');

			?>
			// Create modal if it doesn't exist
			if (!$('#cancel-appointment-modal').length) {
				$('body').append(`
					<div id="cancel-appointment-modal" class="cancel-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
						<div class="modal-content" style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:80%; max-width:500px; border-radius:4px;">
							<h3><?php esc_html_e('Cancel Appointment', 'woocommerce-appointments'); ?></h3>
							<div class="modal-body">
								<p><?php esc_html_e('Cancellation Policy:', 'woocommerce-appointments'); ?></p>
								<p><?php echo $cancellationPolicyMessage; ?></p>
							</div>
							<div class="modal-footer" style="margin-top:15px; text-align:right;">
								<button id="cancel-disagree" class="button"><?php esc_html_e('Disregard', 'woocommerce-appointments'); ?></button>
								<button id="cancel-agree" class="button button-primary single_add_to_cart_button"><?php esc_html_e('I Agree, Proceed', 'woocommerce-appointments'); ?></button>
							</div>
						</div>
					</div>
				`);
			}

			// Show the modal with specific cancelUrl
			var modal = $('#cancel-appointment-modal');
			modal.find('#cancel-agree').data('url', cancelUrl);
			modal.css('display', 'block');

			// Handle agree button
			$('#cancel-agree').off('click').on('click', function() {
				window.location.href = $(this).data('url');
			});

			// Handle disagree button
			$('#cancel-disagree').off('click').on('click', function() {
				modal.css('display', 'none');
			});

			// Close when clicking outside the modal content
			$(window).off('click.cancelModal').on('click.cancelModal', function(event) {
				if ($(event.target).is(modal)) {
					modal.css('display', 'none');
				}
			});
		});
	});
	jQuery(document).ready(function($) {
		$('.reschedule_appointment').on('click', function(e) {
			e.preventDefault();
			var rescheduleUrl = $(this).attr('href');
			if (confirm('<?php esc_html_e('Are you sure you want to reschedule this appointment?', 'woocommerce-appointments'); ?>')) {
				window.location.href = rescheduleUrl;
			}
		});
	});
</script>