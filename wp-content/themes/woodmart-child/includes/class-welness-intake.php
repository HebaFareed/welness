<?php
/**
 * Wellness Hub — Client Intake Module.
 *
 * Hosts everything intake-related for the site: the customer_intake_form CPT,
 * admin submenu/list columns/metabox, the admin order card, the email summary
 * helper, and the thank-you (order-received) intake form + AJAX save.
 *
 * Required once from woodmart-child/functions.php. Extracted from functions.php
 * on 2026-09-08. See docs/INTAKE-THANKYOU-PLAN.md.
 *
 * @package woodmart-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// ── Phase 1: Register custom post type 'customer_intake_form' ────────────

add_action('init', 'wellness_register_intake_cpt', 20);
function wellness_register_intake_cpt()
{
	register_post_type('customer_intake_form', [
		'labels'              => [
			'name'               => __('Intake Forms', 'woodmart-child'),
			'singular_name'      => __('Intake Form', 'woodmart-child'),
			'add_new'            => __('Add New', 'woodmart-child'),
			'add_new_item'       => __('Add New Intake Form', 'woodmart-child'),
			'edit_item'          => __('View Intake Form', 'woodmart-child'),
			'search_items'       => __('Search Intake Forms', 'woodmart-child'),
			'not_found'          => __('No intake forms found.', 'woodmart-child'),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false, // added as submenu below
		'supports'            => ['title', 'custom-fields'],
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
	]);
}


// ── Phase 5: Admin submenu & CPT list table customizations ──────────────

add_action('admin_menu', 'wellness_intake_admin_submenu', 20);
function wellness_intake_admin_submenu()
{
	add_submenu_page(
		'edit.php?post_type=wc_appointment',
		__('Intake Forms', 'woodmart-child'),
		__('Intake Forms', 'woodmart-child'),
		'edit_posts', // shop_staff has this capability
		'edit.php?post_type=customer_intake_form'
	);
}

// ── Custom columns on the CPT list table ────────────────────────────────

add_filter('manage_customer_intake_form_posts_columns', 'wellness_intake_list_columns');
function wellness_intake_list_columns($columns)
{
	$new = [];
	foreach ($columns as $key => $label) {
		$new[$key] = $label;
		if ($key === 'title') {
			$new['intake_email']  = __('Email', 'woodmart-child');
			$new['intake_phone']  = __('Phone', 'woodmart-child');
			$new['intake_order']  = __('Order', 'woodmart-child');
		}
	}
	unset($new['date']);
	return $new;
}

add_action('manage_customer_intake_form_posts_custom_column', 'wellness_intake_list_column_content', 10, 2);
function wellness_intake_list_column_content($column, $post_id)
{
	switch ($column) {
		case 'intake_email':
			$email = get_post_meta($post_id, '_intake_email', true);
			echo $email ? sprintf(
				'<a href="mailto:%1$s">%1$s</a>',
				esc_html($email)
			) : '<span aria-hidden="true">—</span>';
			break;

		case 'intake_phone':
			$mobile = get_post_meta($post_id, '_intake_mobile', true);
			echo $mobile ? esc_html($mobile) : '<span aria-hidden="true">—</span>';
			break;

		case 'intake_order':
			$order_id = (int) get_post_meta($post_id, '_intake_order_id', true);
			if ($order_id) {
				$order = wc_get_order($order_id);
				if ($order) {
					printf(
						'<a href="%s">#%s</a>',
						esc_url($order->get_edit_order_url()),
						esc_html($order->get_order_number())
					);
				} else {
					echo '#' . absint($order_id);
				}
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;
	}
}

// ── Read-only metabox on single CPT edit screen ────────────────────────

add_action('add_meta_boxes', 'wellness_intake_view_metabox');
function wellness_intake_view_metabox()
{
	add_meta_box(
		'wellness_intake_details',
		__('Intake Form Details', 'woodmart-child'),
		'wellness_render_intake_metabox',
		'customer_intake_form',
		'normal',
		'high'
	);
}

function wellness_render_intake_metabox($post)
{
	$fields = [
		__('Client Name', 'woodmart-child')          => '_intake_client_name',
		__('Birth Date', 'woodmart-child')           => '_intake_birth_date',
		__('Current Address', 'woodmart-child')      => '_intake_address',
		__('Mobile', 'woodmart-child')               => '_intake_mobile',
		__('OK to text (mobile)', 'woodmart-child')   => '_intake_mobile_text',
		__('Home Phone', 'woodmart-child')           => '_intake_home_phone',
		__('OK voicemail (home)', 'woodmart-child')   => '_intake_home_voicemail',
		__('Work Phone', 'woodmart-child')           => '_intake_work_phone',
		__('OK voicemail (work)', 'woodmart-child')   => '_intake_work_voicemail',
		__('Appt reminders (work)', 'woodmart-child') => '_intake_work_reminders',
		__('Preferred Communication', 'woodmart-child') => '_intake_preferred_comm',
		__('Emergency — Name', 'woodmart-child')            => '_intake_emergency_name',
		__('Emergency — Relation', 'woodmart-child')        => '_intake_emergency_relation',
		__('Emergency — Home Phone', 'woodmart-child')      => '_intake_emergency_home_phone',
		__('Emergency — Mobile', 'woodmart-child')          => '_intake_emergency_mobile',
		__('Emergency — OK voicemail', 'woodmart-child')    => '_intake_emergency_voicemail',
		__('Emergency — OK to text', 'woodmart-child')      => '_intake_emergency_text',
		__('Currently receiving services?', 'woodmart-child') => '_intake_current_services',
		__('Past counseling?', 'woodmart-child')              => '_intake_past_counseling',
		__('Taking psychiatric meds?', 'woodmart-child')      => '_intake_medications',
		__('Expectations of therapy', 'woodmart-child')       => '_intake_expectations',
		__('Referral — Friend', 'woodmart-child')             => '_intake_referral_friend_name',
		__('Referral — Doctor/Therapist', 'woodmart-child')   => '_intake_referral_doctor_name',
		__('Referral — Family', 'woodmart-child')             => '_intake_referral_family',
		__('Referral — Location', 'woodmart-child')           => '_intake_referral_location',
		__('Referral — Online Search', 'woodmart-child')      => '_intake_referral_search',
		__('Referral — Facebook', 'woodmart-child')           => '_intake_referral_facebook',
	];

	$checkbox_fields = [
		'_intake_mobile_text',
		'_intake_home_voicemail',
		'_intake_work_voicemail',
		'_intake_work_reminders',
		'_intake_emergency_voicemail',
		'_intake_emergency_text',
		'_intake_referral_family',
		'_intake_referral_location',
		'_intake_referral_search',
		'_intake_referral_facebook',
	];

	$yn_fields = [
		'_intake_current_services',
		'_intake_past_counseling',
		'_intake_medications',
	];

	$comm_labels = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

	$order_id = (int) get_post_meta($post->ID, '_intake_order_id', true);
	$email    = get_post_meta($post->ID, '_intake_email', true);

	echo '<div style="max-width:700px;">';

	if ($order_id) {
		$order = wc_get_order($order_id);
		if ($order) {
			printf(
				'<p><strong>%s:</strong> <a href="%s">#%s</a></p>',
				esc_html__('Linked Order', 'woodmart-child'),
				esc_url($order->get_edit_order_url()),
				esc_html($order->get_order_number())
			);
		}
	}
	if ($email) {
		printf(
			'<p><strong>%s:</strong> <a href="mailto:%1$s">%1$s</a></p>',
			esc_html__('Email', 'woodmart-child'),
			esc_html($email)
		);
	}

	echo '<table class="widefat striped" style="margin-top:12px;"><tbody>';

	foreach ($fields as $label => $meta_key) {
		$raw = get_post_meta($post->ID, $meta_key, true);

		if (in_array($meta_key, $checkbox_fields, true)) {
			$display = ($raw === 'yes')
				? '<span style="color:#2e7d32;">&#10003; ' . esc_html__('Yes', 'woodmart-child') . '</span>'
				: '<span style="color:#aaa;">—</span>';
		} elseif (in_array($meta_key, $yn_fields, true)) {
			$display = ($raw === 'yes' || $raw === 'no')
				? esc_html(ucfirst($raw))
				: '<span style="color:#aaa;">—</span>';
		} elseif ($meta_key === '_intake_preferred_comm') {
			$display = isset($comm_labels[$raw])
				? esc_html($comm_labels[$raw])
				: '<span style="color:#aaa;">—</span>';
		} elseif ($meta_key === '_intake_expectations') {
			$display = $raw ? '<p style="white-space:pre-wrap;margin:4px 0;">' . esc_html($raw) . '</p>' : '<span style="color:#aaa;">—</span>';
		} else {
			$display = $raw ? esc_html($raw) : '<span style="color:#aaa;">—</span>';
		}

		printf(
			'<tr><th style="width:220px;text-align:left;padding:6px 8px;">%s</th><td style="padding:6px 8px;">%s</td></tr>',
			esc_html($label),
			$display
		);
	}

	echo '</tbody></table></div>';
}


// ── Phase 6: Show intake summary on the admin order detail page ─────────

add_action('woocommerce_admin_order_data_after_billing_address', 'wellness_show_intake_on_order');
function wellness_show_intake_on_order($order)
{
	$email = $order->get_billing_email();
	if (empty($email)) {
		return;
	}

	$intake = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
	]);

	if (empty($intake)) {
		return;
	}

	$post   = $intake[0];
	$edit_url = get_edit_post_link($post->ID, '');

	$name     = get_post_meta($post->ID, '_intake_client_name', true);
	$birth    = get_post_meta($post->ID, '_intake_birth_date', true);
	$mobile   = get_post_meta($post->ID, '_intake_mobile', true);
	$home     = get_post_meta($post->ID, '_intake_home_phone', true);
	$pref_comm = get_post_meta($post->ID, '_intake_preferred_comm', true);
	$emerg_name = get_post_meta($post->ID, '_intake_emergency_name', true);
	$emerg_mob  = get_post_meta($post->ID, '_intake_emergency_mobile', true);
	$meds        = get_post_meta($post->ID, '_intake_medications', true);
	$expectations = get_post_meta($post->ID, '_intake_expectations', true);

	$comm_labels = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

?>
	<div style="clear:both;margin-top:16px;padding:12px;background:#f9f9f9;border:1px solid #e5e5e5;border-radius:4px;">
		<h3 style="margin:0 0 8px;">
			<?php esc_html_e('Client Intake Form', 'woodmart-child'); ?>
			<?php if ($edit_url): ?>
				<a href="<?php echo esc_url($edit_url); ?>" style="font-weight:400;font-size:12px;margin-left:8px;">
					<?php esc_html_e('View full form →', 'woodmart-child'); ?>
				</a>
			<?php endif; ?>
		</h3>
		<table style="width:100%;border-collapse:collapse;">
			<?php if ($name): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;width:100px;"><?php esc_html_e('Name', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($name); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($birth): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Birth Date', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($birth); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($mobile): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Mobile', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($mobile); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($home): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Home', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($home); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($pref_comm && isset($comm_labels[$pref_comm])): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Preferred', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($comm_labels[$pref_comm]); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($emerg_name): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Emergency', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($emerg_name); ?><?php echo $emerg_mob ? ' — ' . esc_html($emerg_mob) : ''; ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($meds): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Medications', 'woodmart-child'); ?></td>
					<td><?php echo esc_html(ucfirst($meds)); ?></td>
				</tr>
			<?php endif; ?>
		</table>
		<?php if ($expectations): ?>
			<p style="margin:8px 0 0;font-style:italic;color:#555;"><?php echo esc_html(wp_trim_words($expectations, 30, '…')); ?></p>
		<?php endif; ?>
	</div>
<?php
}


/**
 * Fetch intake form data for a client email and return an HTML summary table
 * suitable for inclusion in admin/therapist notification emails.
 *
 * @param string $email  Client billing email.
 * @return string  HTML table rows, or empty string if no intake found.
 */
function wellness_get_intake_summary_for_email($email)
{
	if (empty($email)) {
		return '';
	}

	$intake = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
	]);

	if (empty($intake)) {
		return '';
	}

	$post_id = $intake[0]->ID;

	$rows = [
		__('Client Name', 'woodmart-child')            => '_intake_client_name',
		__('Birth Date', 'woodmart-child')             => '_intake_birth_date',
		__('Address', 'woodmart-child')                => '_intake_address',
		__('Mobile', 'woodmart-child')                 => '_intake_mobile',
		__('Home Phone', 'woodmart-child')             => '_intake_home_phone',
		__('Work Phone', 'woodmart-child')             => '_intake_work_phone',
		__('Preferred Comm', 'woodmart-child')          => '_intake_preferred_comm',
		__('Emergency Contact', 'woodmart-child')       => '_intake_emergency_name',
		__('Emergency Relation', 'woodmart-child')      => '_intake_emergency_relation',
		__('Emergency Mobile', 'woodmart-child')        => '_intake_emergency_mobile',
		__('Current Services', 'woodmart-child')        => '_intake_current_services',
		__('Past Counseling', 'woodmart-child')         => '_intake_past_counseling',
		__('Psychiatric Meds', 'woodmart-child')        => '_intake_medications',
	];

	$comm_labels = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

	$html = '';

	foreach ($rows as $label => $meta_key) {
		$raw = get_post_meta($post_id, $meta_key, true);
		if (empty($raw) || $raw === 'no') {
			continue;
		}

		$display = $raw;
		if ($meta_key === '_intake_preferred_comm' && isset($comm_labels[$raw])) {
			$display = $comm_labels[$raw];
		} elseif (in_array($raw, ['yes', 'no'], true)) {
			$display = ucfirst($raw);
		}

		$html .= '<tr>';
		$html .= '<th style="text-align:left;background:#f7f7f7;width:38%;padding:10px 14px;font-weight:600;">' . esc_html($label) . '</th>';
		$html .= '<td style="text-align:left;padding:10px 14px;">' . esc_html($display) . '</td>';
		$html .= '</tr>';
	}

	// Referral sources
	$referral_sources = [
		'_intake_referral_friend_name' => __('Friend (name)', 'woodmart-child'),
		'_intake_referral_doctor_name' => __('Doctor/Therapist (name)', 'woodmart-child'),
		'_intake_referral_family'      => __('Family', 'woodmart-child'),
		'_intake_referral_location'    => __('Location', 'woodmart-child'),
		'_intake_referral_search'      => __('Online Search', 'woodmart-child'),
		'_intake_referral_facebook'    => __('Facebook', 'woodmart-child'),
	];

	$referral_items = [];
	foreach ($referral_sources as $meta_key => $label) {
		$raw = get_post_meta($post_id, $meta_key, true);
		if ($meta_key === '_intake_referral_friend_name' || $meta_key === '_intake_referral_doctor_name') {
			if (! empty($raw)) {
				$referral_items[] = $label . ': ' . $raw;
			}
		} elseif ($raw === 'yes') {
			$referral_items[] = $label;
		}
	}

	if (! empty($referral_items)) {
		$html .= '<tr>';
		$html .= '<th style="text-align:left;background:#f7f7f7;width:38%;padding:10px 14px;font-weight:600;">' . esc_html__('Referral', 'woodmart-child') . '</th>';
		$html .= '<td style="text-align:left;padding:10px 14px;">' . esc_html(implode(', ', $referral_items)) . '</td>';
		$html .= '</tr>';
	}

	// Expectations (trimmed)
	$expectations = get_post_meta($post_id, '_intake_expectations', true);
	if (! empty($expectations)) {
		$html .= '<tr>';
		$html .= '<th style="text-align:left;background:#f7f7f7;width:38%;padding:10px 14px;font-weight:600;">' . esc_html__('Expectations', 'woodmart-child') . '</th>';
		$html .= '<td style="text-align:left;padding:10px 14px;font-style:italic;">' . esc_html(wp_trim_words($expectations, 40, '…')) . '</td>';
		$html .= '</tr>';
	}

	if (empty($html)) {
		return '';
	}

	return $html;
}


// ═══════════════════════════════════════════════════════════════════════════════


// Task 3b — Client Intake Form on the Thank-You (order-received) Page
// Intake moved from checkout (Task 3) to the order-received page so it no longer
// lengthens checkout. See docs/INTAKE-THANKYOU-PLAN.md.
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Canonical intake field definitions (data fields only — no checkout header rows).
 * Single source of truth for rendering + saving the thank-you intake form.
 *
 * @return array<string,array{type:string,label:string,required?:bool,placeholder?:string,options?:array}>
 */
function wellness_get_intake_field_defs()
{
	$comm_options = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];
	$yes_no = ['yes' => __('Yes', 'woodmart-child'), 'no' => __('No', 'woodmart-child')];

	return [
		'intake_client_name'          => ['type' => 'text', 'label' => __('Client Name', 'woodmart-child'), 'required' => true],
		'intake_birth_date'           => ['type' => 'text', 'label' => __('Birth Date', 'woodmart-child'), 'placeholder' => __('DD/MM/YYYY', 'woodmart-child')],
		'intake_address'              => ['type' => 'text', 'label' => __('Current Address', 'woodmart-child')],
		'intake_mobile'               => ['type' => 'text', 'label' => __('Mobile', 'woodmart-child'), 'required' => true],
		'intake_mobile_text'          => ['type' => 'checkbox', 'label' => __('OK to text message', 'woodmart-child')],
		'intake_home_phone'           => ['type' => 'text', 'label' => __('Home Phone', 'woodmart-child')],
		'intake_home_voicemail'       => ['type' => 'checkbox', 'label' => __('OK to leave detailed voice message', 'woodmart-child')],
		'intake_work_phone'           => ['type' => 'text', 'label' => __('Work Phone', 'woodmart-child')],
		'intake_work_voicemail'       => ['type' => 'checkbox', 'label' => __('OK to leave voice message', 'woodmart-child')],
		'intake_work_reminders'       => ['type' => 'checkbox', 'label' => __('Appointment reminders', 'woodmart-child')],
		'intake_preferred_comm'       => ['type' => 'select', 'label' => __('Preferred way of communication for appointment reminders', 'woodmart-child'), 'options' => $comm_options],
		'intake_emergency_name'       => ['type' => 'text', 'label' => __('Contact Name', 'woodmart-child')],
		'intake_emergency_relation'   => ['type' => 'text', 'label' => __('Relationship to Client', 'woodmart-child')],
		'intake_emergency_home_phone' => ['type' => 'text', 'label' => __('Home Phone', 'woodmart-child')],
		'intake_emergency_mobile'     => ['type' => 'text', 'label' => __('Mobile', 'woodmart-child')],
		'intake_emergency_voicemail'  => ['type' => 'checkbox', 'label' => __('OK to leave detailed voice message', 'woodmart-child')],
		'intake_emergency_text'       => ['type' => 'checkbox', 'label' => __('OK to text message', 'woodmart-child')],
		'intake_current_services'     => ['type' => 'select', 'label' => __('Are you currently receiving psychological services, professional counseling, psychiatric services, or any other mental health services?', 'woodmart-child'), 'options' => $yes_no],
		'intake_past_counseling'      => ['type' => 'select', 'label' => __('Have you had any Counseling/psychotherapy services in the past?', 'woodmart-child'), 'options' => $yes_no],
		'intake_medications'          => ['type' => 'select', 'label' => __('Are you currently taking any psychiatric prescription medications?', 'woodmart-child'), 'options' => $yes_no],
		'intake_expectations'         => ['type' => 'textarea', 'label' => __('What are your expectations of therapy?', 'woodmart-child')],
		'intake_referral_friend_name' => ['type' => 'text', 'label' => __('Friend (name)', 'woodmart-child')],
		'intake_referral_doctor_name' => ['type' => 'text', 'label' => __('Doctor/Therapist (name)', 'woodmart-child')],
		'intake_referral_family'      => ['type' => 'checkbox', 'label' => __('Family', 'woodmart-child')],
		'intake_referral_location'    => ['type' => 'checkbox', 'label' => __('Location is close to home/work', 'woodmart-child')],
		'intake_referral_search'      => ['type' => 'checkbox', 'label' => __('Online Search', 'woodmart-child')],
		'intake_referral_facebook'    => ['type' => 'checkbox', 'label' => __('Our Facebook Page', 'woodmart-child')],
	];
}

/**
 * Section groupings for the thank-you intake form (mirrors the original
 * checkout phase groupings).
 *
 * @return array<string,string[]>
 */
function wellness_thankyou_intake_field_groups()
{
	return [
		__('Personal Details', 'woodmart-child')      => ['intake_client_name', 'intake_birth_date', 'intake_address', 'intake_mobile', 'intake_mobile_text'],
		__('Phone & Preferences', 'woodmart-child')   => ['intake_home_phone', 'intake_home_voicemail', 'intake_work_phone', 'intake_work_voicemail', 'intake_work_reminders', 'intake_preferred_comm'],
		__('Emergency Contact', 'woodmart-child')     => ['intake_emergency_name', 'intake_emergency_relation', 'intake_emergency_home_phone', 'intake_emergency_mobile', 'intake_emergency_voicemail', 'intake_emergency_text'],
		__('Health Background', 'woodmart-child')     => ['intake_current_services', 'intake_past_counseling', 'intake_medications', 'intake_expectations'],
		__('How You Heard About Us', 'woodmart-child') => ['intake_referral_friend_name', 'intake_referral_doctor_name', 'intake_referral_family', 'intake_referral_location', 'intake_referral_search', 'intake_referral_facebook'],
	];
}

/**
 * Whether an intake record already exists for a billing email.
 *
 * @param string $email
 * @return bool
 */
function wellness_intake_has_record($email)
{
	if (empty($email)) {
		return false;
	}

	$existing = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	]);

	return ! empty($existing);
}

/**
 * Create a customer_intake_form record from posted data. Re-checks existence
 * internally to guard against duplicates (no DB unique index). Returns the new
 * post ID, or 0 on failure / duplicate.
 *
 * @param WC_Order $order
 * @param array    $data  Raw intake_* key => value from the form.
 * @return int
 */
function wellness_create_intake_record($order, $data)
{
	if (! $order) {
		return 0;
	}

	$email = $order->get_billing_email();
	if (empty($email) || wellness_intake_has_record($email)) {
		return 0;
	}

	$client_name = isset($data['intake_client_name']) ? sanitize_text_field(wp_unslash($data['intake_client_name'])) : '';
	if (empty($client_name)) {
		$client_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
	}

	$post_id = wp_insert_post([
		'post_type'   => 'customer_intake_form',
		'post_title'  => trim($client_name . ' — ' . wp_date('Y-m-d')),
		'post_status' => 'publish',
	]);

	if (is_wp_error($post_id) || ! $post_id) {
		return 0;
	}

	$defs = wellness_get_intake_field_defs();

	foreach ($defs as $key => $field) {
		$type = $field['type'];
		$raw  = isset($data[$key]) ? $data[$key] : '';

		if ($type === 'checkbox') {
			$value = ! empty($raw) ? 'yes' : 'no';
		} elseif ($type === 'textarea') {
			$value = sanitize_textarea_field(wp_unslash($raw));
		} else {
			$value = sanitize_text_field(wp_unslash($raw));
		}

		update_post_meta($post_id, '_' . $key, $value);
	}

	// ── Linkage meta ────────────────────────────────────────────────────
	update_post_meta($post_id, '_intake_email', $email);
	update_post_meta($post_id, '_intake_order_id', $order->get_id());
	update_post_meta($post_id, '_intake_customer_id', $order->get_customer_id());

	return $post_id;
}

/**
 * Render one intake field for the thank-you form.
 *
 * @param string $key
 * @param array  $field
 * @param string $value
 * @return void
 */
function wellness_thankyou_render_field($key, $field, $value = '')
{
	$type     = $field['type'] ?? 'text';
	$label    = $field['label'] ?? '';
	$required = ! empty($field['required']);
	$ph       = $field['placeholder'] ?? '';

	echo '<div class="wellness-intake-th-field wellness-intake-th-field--' . esc_attr($type) . '">';

	if ($type === 'checkbox') {
		echo '<label class="wellness-intake-th-check">';
		echo '<input type="checkbox" name="' . esc_attr($key) . '" id="th-' . esc_attr($key) . '" value="1" ' . checked($value, '1', false) . ' />';
		echo '<span>' . esc_html($label) . '</span>';
		echo '</label>';
		echo '</div>';
		return;
	}

	if ($label) {
		echo '<label for="th-' . esc_attr($key) . '">' . esc_html($label) . ($required ? ' <span class="wellness-intake-th-req">*</span>' : '') . '</label>';
	}

	if ($type === 'select') {
		echo '<select name="' . esc_attr($key) . '" id="th-' . esc_attr($key) . '"' . ($required ? ' required' : '') . '>';
		echo '<option value="">' . esc_html__('Select…', 'woodmart-child') . '</option>';
		foreach (($field['options'] ?? []) as $opt_val => $opt_label) {
			echo '<option value="' . esc_attr($opt_val) . '" ' . selected($value, $opt_val, false) . '>' . esc_html($opt_label) . '</option>';
		}
		echo '</select>';
	} elseif ($type === 'textarea') {
		echo '<textarea name="' . esc_attr($key) . '" id="th-' . esc_attr($key) . '" rows="3" placeholder="' . esc_attr($ph) . '">' . esc_textarea($value) . '</textarea>';
	} else {
		echo '<input type="text" name="' . esc_attr($key) . '" id="th-' . esc_attr($key) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($ph) . '"' . ($required ? ' required' : '') . ' />';
	}

	echo '</div>';
}

/**
 * Prefill values from the order billing data (client may edit them).
 *
 * @param WC_Order $order
 * @return array<string,string>
 */
function wellness_thankyou_intake_prefill($order)
{
	$name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
	if (empty($name)) {
		$name = (string) $order->get_meta('billing_full_name');
	}

	return [
		'intake_client_name' => $name,
		'intake_mobile'      => (string) $order->get_billing_phone(),
	];
}

/**
 * Cache-busting version for a child theme asset: file mtime, or '1.0.0' when
 * the file is missing.
 *
 * @param string $relative Path relative to the child theme root.
 * @return string
 */
function wellness_intake_asset_version($relative)
{
	$path = get_stylesheet_directory() . $relative;

	return file_exists($path) ? (string) filemtime($path) : '1.0.0';
}

/**
 * Enqueue the thank-you intake assets on the order-received page.
 *
 * Both files are real enqueued assets rather than inline tags so they survive
 * caching / minification layers and are versioned by mtime.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', 'wellness_enqueue_thankyou_intake_assets');
function wellness_enqueue_thankyou_intake_assets()
{
	if (! is_order_received_page()) {
		return;
	}

	$css = '/assets/css/wellness-intake-thankyou.css';
	$js  = '/assets/js/wellness-intake-thankyou.js';

	wp_enqueue_style(
		'wellness-intake-thankyou',
		get_stylesheet_directory_uri() . $css,
		[],
		wellness_intake_asset_version($css)
	);

	wp_enqueue_script(
		'wellness-intake-thankyou',
		get_stylesheet_directory_uri() . $js,
		[],
		wellness_intake_asset_version($js),
		true
	);

	wp_localize_script('wellness-intake-thankyou', 'wellnessIntake', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'action'  => 'wellness_save_thankyou_intake',
		'i18n'    => [
			'saving'        => __('Saving…', 'woodmart-child'),
			'submit'        => __('Submit Intake Form', 'woodmart-child'),
			'required'      => __('Please enter the client name and a mobile number.', 'woodmart-child'),
			'requiredField' => __('Please fill in: %s', 'woodmart-child'),
			'success'       => __('Thank you! Your intake form has been received.', 'woodmart-child'),
			'error'         => __('Something went wrong. Please try again.', 'woodmart-child'),
		],
	]);
}

/**
 * Render the full intake form markup on the order-received page.
 *
 * Five steps, one per field group, mirroring the checkout wizard. Without
 * JavaScript every step stays visible and the whole form submits at once.
 *
 * @param WC_Order $order
 * @param string   $error_message Server-side error from a native POST, if any.
 * @return void
 */
function wellness_render_thankyou_intake_form($order, $error_message = '')
{
	$defs       = wellness_get_intake_field_defs();
	$groups     = wellness_thankyou_intake_field_groups();
	$prefill    = wellness_thankyou_intake_prefill($order);
	$first      = $order->get_billing_first_name();
	$order_id   = $order->get_id();
	$order_no   = $order->get_order_number();
	$native_url = $order->get_checkout_order_received_url();
	$nonce      = wp_create_nonce('wellness_thankyou_intake');
	$total      = count($groups);

	echo '<section class="wellness-intake-th" id="wellness-thankyou-intake">';

	echo '<h2 class="wellness-intake-th__heading">' . esc_html__('Client Intake Form', 'woodmart-child') . '</h2>';
	echo '<p class="wellness-intake-th__intro">';
	if ($first) {
		echo esc_html(sprintf(
			/* translators: %s: client first name */
			__('Thank you, %s. To help your therapist prepare for your session (order #%s), please complete this short intake form. It only takes a couple of minutes.', 'woodmart-child'),
			$first,
			$order_no
		));
	} else {
		echo esc_html(sprintf(
			/* translators: %s: order number */
			__('Thank you for your booking (order #%s). To help your therapist prepare for your session, please complete this short intake form.', 'woodmart-child'),
			$order_no
		));
	}
	echo '</p>';

	// Progress dots. The step headings carry the labels, the dots carry the
	// position; the script colours them as the client moves through.
	echo '<div class="wellness-progress">';
	for ($i = 1; $i <= $total; $i++) {
		echo '<span class="wellness-progress__dot' . (1 === $i ? ' wellness-progress__dot--active' : '') . '" data-dot="' . esc_attr($i) . '">';
		echo '<span class="wellness-progress__dot-inner">' . esc_html($i) . '</span>';
		echo '</span>';
	}
	echo '</div>';

	// Above the form so a validation message stays visible in every step.
	echo '<div class="wellness-intake-th__error" id="wellness-intake-th-error" role="alert"' . ($error_message ? '' : ' hidden') . '>' . ($error_message ? esc_html($error_message) : '') . '</div>';

	// Posts to the order-received URL itself: the server-side native handler
	// saves it, so a submission still lands if the JS never runs. The JS takes
	// over and sends the same payload to admin-ajax instead.
	echo '<form class="wellness-intake-th-form" id="wellness-thankyou-intake-form" action="' . esc_url($native_url) . '" method="post" novalidate>';
	echo '<input type="hidden" name="wellness_intake_native" value="1" />';
	echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '" />';
	echo '<input type="hidden" name="order_key" value="' . esc_attr($order->get_order_key()) . '" />';
	echo '<input type="hidden" name="wellness_intake_nonce" value="' . esc_attr($nonce) . '" />';

	$step = 0;

	foreach ($groups as $heading => $keys) {
		$step++;
		$is_last = ($step === $total);

		echo '<div class="wellness-intake-th-step' . (1 === $step ? ' is-current' : '') . '" data-step="' . esc_attr($step) . '" role="group" aria-label="' . esc_attr($heading) . '">';

		echo '<div class="wellness-step-header">';
		echo '<span class="wellness-step-number" aria-hidden="true">' . wellness_step_icon($step) . '</span>';
		echo '<span class="wellness-step-title">' . esc_html($heading) . '</span>';
		echo '<span class="wellness-step-divider"></span>';
		echo '<span class="wellness-step-count">' . esc_html(sprintf(
			/* translators: 1: current step, 2: total number of steps */
			__('Step %1$d of %2$d', 'woodmart-child'),
			$step,
			$total
		)) . '</span>';
		echo '</div>';

		echo '<div class="wellness-step-body">';
		foreach ($keys as $key) {
			if (! isset($defs[$key])) {
				continue;
			}
			$value = isset($prefill[$key]) ? $prefill[$key] : '';
			wellness_thankyou_render_field($key, $defs[$key], $value);
		}
		echo '</div>';

		echo '<div class="wellness-step-actions">';

		if ($step > 1) {
			echo '<button type="button" class="wellness-btn-back" data-back="' . esc_attr($step - 1) . '">';
			echo '<svg class="wellness-btn-back__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>';
			echo '<span>' . esc_html__('Back', 'woodmart-child') . '</span>';
			echo '</button>';
		}

		if (! $is_last) {
			echo '<button type="button" class="wellness-btn-next" data-next="' . esc_attr($step + 1) . '">';
			echo '<span>' . esc_html__('Continue', 'woodmart-child') . '</span>';
			echo '<svg class="wellness-btn-next__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>';
			echo '</button>';
		} else {
			echo '<button type="submit" class="wellness-btn-next" id="wellness-intake-th-submit">' . esc_html__('Submit Intake Form', 'woodmart-child') . '</button>';
		}

		echo '</div>';

		echo '</div>';
	}

	echo '</form>';

	echo '<p class="wellness-intake-th__note">' . esc_html__('Required fields are marked with *. Your information is confidential and shared only with your therapist.', 'woodmart-child') . '</p>';
	echo '<div class="wellness-intake-th__success" id="wellness-intake-th-success" role="status" hidden></div>';
	echo '<noscript><p class="wellness-intake-th__note">' . esc_html__('JavaScript is switched off in your browser, so all steps are shown on one page. The form still submits normally.', 'woodmart-child') . '</p></noscript>';

	echo '</section>';
}

/**
 * Render the intake form on the order-received page, once per request.
 * Safe to call from multiple hooks.
 *
 * @param int $order_id
 * @return void
 */
function wellness_render_thankyou_intake($order_id)
{
	static $rendered = false;
	if ($rendered) {
		return;
	}
	$rendered = true;

	if (! $order_id) {
		return;
	}

	$order = wc_get_order($order_id);
	if (! $order) {
		return;
	}

	// A native (non-JS) POST handled earlier in this request already has its
	// answer: confirm it here instead of rendering the form again.
	$native = wellness_thankyou_intake_native_result();
	if ($native && in_array($native['state'], ['saved', 'already'], true)) {
		wellness_render_thankyou_intake_confirmation($native['message']);
		return;
	}

	if ($order->has_status(['failed', 'cancelled', 'refunded', 'trash', 'checkout-draft'])) {
		return;
	}

	$email = $order->get_billing_email();
	if (empty($email)) {
		return;
	}

	// Returning client already has an intake record — nothing to ask.
	if (wellness_intake_has_record($email)) {
		return;
	}

	wellness_render_thankyou_intake_form($order, $native ? $native['message'] : '');
}

/**
 * Render the "intake received" confirmation.
 *
 * Used when a native POST was handled in the same request — the page
 * re-renders after that form submits, so there is no AJAX callback to swap the
 * markup.
 *
 * @param string $message
 * @return void
 */
function wellness_render_thankyou_intake_confirmation($message = '')
{
	if ('' === $message) {
		$message = __('Your intake form has already been submitted. Thank you!', 'woodmart-child');
	}

	echo '<section class="wellness-intake-th" id="wellness-thankyou-intake">';
	echo '<div class="wellness-intake-th__success" role="status"><strong>' . esc_html($message) . '</strong></div>';
	echo '</section>';
}

// Primary hook — fires inside Woodmart's thankyou template when the order exists
// and the Woodmart "default content" option is enabled.
add_action('woocommerce_thankyou', 'wellness_thankyou_intake_on_thankyou', 10);
function wellness_thankyou_intake_on_thankyou($order_id)
{
	wellness_render_thankyou_intake($order_id);
}

// Fallback — if the Woodmart option is disabled (woocommerce_thankyou never
// fires), render the form on the order-received page footer instead. The
// rendered-once guard prevents duplicates.
add_action('wp_footer', 'wellness_thankyou_intake_footer_fallback', 5);
function wellness_thankyou_intake_footer_fallback()
{
	if (! is_order_received_page()) {
		return;
	}

	$order_id = absint(get_query_var('order-received'));
	if (! $order_id) {
		return;
	}

	// Only render for a valid, key-matched order (same check as WooCommerce).
	$key   = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
	$order = wc_get_order($order_id);
	if (! $order || ! hash_equals($order->get_order_key(), (string) $key)) {
		return;
	}

	wellness_render_thankyou_intake($order_id);
}

// ── Submission helpers (shared by the AJAX and native paths) ──────────────

/**
 * Result of a native (non-JS) intake submission handled in this request.
 *
 * 'saved' | 'already' | 'error', plus an optional message. Null when no native
 * submit happened. Read by wellness_render_thankyou_intake() so the same
 * request can render the confirmation instead of the form.
 *
 * @param string|null $state   Pass a state to store one.
 * @param string      $message Optional message to store with it.
 * @return array{state:string,message:string}|null
 */
function wellness_thankyou_intake_native_result($state = null, $message = '')
{
	static $result = null;

	if ($state !== null) {
		$result = ['state' => $state, 'message' => $message];
	}

	return $result;
}

/**
 * Resolve the order referenced by a posted intake form.
 *
 * The order key is the capability check: it is only exposed on the
 * order-received page (and in the reminder email link), so a matching key
 * proves the submitter is entitled to that order.
 *
 * @return array{order:WC_Order|null,error:string}
 */
function wellness_resolve_posted_intake_order()
{
	$order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
	$order    = $order_id ? wc_get_order($order_id) : false;

	if (! $order) {
		return [
			'order' => null,
			'error' => __('We could not find your order. Please contact us.', 'woodmart-child'),
		];
	}

	$key = isset($_POST['order_key']) && is_string($_POST['order_key'])
		? sanitize_text_field(wp_unslash($_POST['order_key']))
		: '';

	if (! hash_equals($order->get_order_key(), $key)) {
		return [
			'order' => null,
			'error' => __('Your session could not be verified. Please reload the page and try again.', 'woodmart-child'),
		];
	}

	return ['order' => $order, 'error' => ''];
}

/**
 * The posted intake nonce, if any. Both field names are accepted so a page
 * already open in a browser keeps working across the rename.
 *
 * @return string
 */
function wellness_posted_intake_nonce()
{
	foreach (['wellness_intake_nonce', '_ajax_nonce'] as $field) {
		if (! empty($_POST[$field]) && is_string($_POST[$field])) {
			return sanitize_text_field(wp_unslash($_POST[$field]));
		}
	}

	return '';
}

/**
 * Pull every canonical intake field out of the current POST request.
 * Non-string values (arrays / nested payloads) are dropped.
 *
 * @return array<string,string>
 */
function wellness_collect_posted_intake_fields()
{
	$posted = [];

	foreach (array_keys(wellness_get_intake_field_defs()) as $field_key) {
		$value = isset($_POST[$field_key]) ? wp_unslash($_POST[$field_key]) : '';

		$posted[$field_key] = is_string($value) ? $value : '';
	}

	return $posted;
}

/**
 * Server-side required-field checks (client name + mobile).
 *
 * @param array<string,string> $posted
 * @return string[] Error messages; empty when the submission is valid.
 */
function wellness_validate_posted_intake_fields($posted)
{
	$errors = [];

	if ('' === trim((string) ($posted['intake_client_name'] ?? ''))) {
		$errors[] = __('Please enter the client name.', 'woodmart-child');
	}

	if ('' === trim((string) ($posted['intake_mobile'] ?? ''))) {
		$errors[] = __('Please enter a mobile number.', 'woodmart-child');
	}

	return $errors;
}

// ── Native (non-JS) POST fallback ─────────────────────────────────────────

/**
 * Handle an intake form POST that reached the page itself instead of admin-ajax.
 *
 * The form's own action is the order-received URL, so a submission still lands
 * when the script never runs (blocked, cached, stripped, minified, or fetch
 * unavailable). Runs before output, so the page renders the confirmation in
 * the same request — no redirect and no extra query-string state.
 *
 * @return void
 */
add_action('wp', 'wellness_handle_native_intake_submit', 5);
function wellness_handle_native_intake_submit()
{
	if (is_admin() || empty($_POST['wellness_intake_native']) || ! is_order_received_page()) {
		return;
	}

	$resolved = wellness_resolve_posted_intake_order();

	if (! $resolved['order']) {
		wellness_thankyou_intake_native_result('error', $resolved['error']);
		return;
	}

	$order = $resolved['order'];

	// Verified but never enforced: the reminder email can bring the client
	// back more than 24 h later, when the nonce has already expired. The order
	// key checked above is the real capability.
	wp_verify_nonce(wellness_posted_intake_nonce(), 'wellness_thankyou_intake');

	$email = $order->get_billing_email();
	if (empty($email)) {
		wellness_thankyou_intake_native_result('error', __('We could not find your order. Please contact us.', 'woodmart-child'));
		return;
	}

	if (wellness_intake_has_record($email)) {
		wellness_thankyou_intake_native_result('already');
		return;
	}

	$posted = wellness_collect_posted_intake_fields();
	$errors = wellness_validate_posted_intake_fields($posted);

	if (! empty($errors)) {
		wellness_thankyou_intake_native_result('error', implode(' ', $errors));
		return;
	}

	$post_id = wellness_create_intake_record($order, $posted);

	if (! $post_id) {
		wellness_thankyou_intake_native_result('error', __('We could not save your intake form. Please try again or contact us.', 'woodmart-child'));
		return;
	}

	wellness_schedule_intake_submitted_notification($post_id);
	wellness_thankyou_intake_native_result('saved');
}

// ── AJAX: save the thank-you intake form ──────────────────────────────────

add_action('wp_ajax_wellness_save_thankyou_intake', 'wellness_save_thankyou_intake_ajax');
add_action('wp_ajax_nopriv_wellness_save_thankyou_intake', 'wellness_save_thankyou_intake_ajax');
function wellness_save_thankyou_intake_ajax()
{
	$resolved = wellness_resolve_posted_intake_order();

	if (! $resolved['order']) {
		wp_send_json_error(['message' => $resolved['error'], 'code' => 'invalid_order']);
	}

	$order = $resolved['order'];

	// Verified but never enforced — see wellness_handle_native_intake_submit().
	wp_verify_nonce(wellness_posted_intake_nonce(), 'wellness_thankyou_intake');

	$email = $order->get_billing_email();

	if (empty($email)) {
		wp_send_json_error([
			'message' => __('We could not find your order. Please contact us.', 'woodmart-child'),
			'code'    => 'invalid_order',
		]);
	}

	// Already on file: confirm calmly instead of showing an error.
	if (wellness_intake_has_record($email)) {
		wp_send_json_success([
			'message' => __('Your intake form has already been submitted. Thank you!', 'woodmart-child'),
			'already' => true,
		]);
	}

	$posted = wellness_collect_posted_intake_fields();
	$errors = wellness_validate_posted_intake_fields($posted);

	if (! empty($errors)) {
		wp_send_json_error([
			'message' => implode(' ', $errors),
			'code'    => 'invalid_fields',
		]);
	}

	$post_id = wellness_create_intake_record($order, $posted);

	if (! $post_id) {
		wp_send_json_error([
			'message' => __('We could not save your intake form. Please try again or contact us.', 'woodmart-child'),
			'code'    => 'save_failed',
		]);
	}

	// Queue the therapist notification (async, exactly once, with retries).
	wellness_schedule_intake_submitted_notification($post_id);

	wp_send_json_success(['message' => __('Thank you! Your intake form has been received.', 'woodmart-child')]);
}

/**
 * Queue the therapist intake notification as a one-shot Action Scheduler job.
 *
 * Runs ~30s after submission so the client's request stays fast. Exactly-once:
 * never queued after a successful send, and never queued twice.
 *
 * @param int $intake_id The created customer_intake_form post ID.
 * @return void
 */
function wellness_schedule_intake_submitted_notification($intake_id)
{
	$intake_id = (int) $intake_id;
	if (! $intake_id) {
		return;
	}

	if (get_post_meta($intake_id, '_intake_notified', true)) {
		return;
	}
	if (function_exists('as_has_scheduled_action')
		&& as_has_scheduled_action('wellness-intake-therapist-notification', [$intake_id], 'wca')) {
		return;
	}

	as_schedule_single_action(time() + 30, 'wellness-intake-therapist-notification', [$intake_id], 'wca');
}

/**
 * Run the therapist intake notification with bounded retries.
 *
 * At most 3 send attempts per intake record (initial + 2 retries, with 5 min /
 * 30 min / 2 h backoff), so a transient mail failure is recovered without ever
 * spamming the therapist. Gives up with a log entry after the cap.
 *
 * @param int $intake_id The customer_intake_form post ID.
 * @return void
 */
add_action('wellness-intake-therapist-notification', 'wellness_run_intake_submitted_notification', 10, 1);
function wellness_run_intake_submitted_notification($intake_id)
{
	$intake_id = (int) $intake_id;
	if (! $intake_id) {
		return;
	}

	// Already delivered — nothing to do.
	if (get_post_meta($intake_id, '_intake_notified', true)) {
		return;
	}

	$attempts = (int) get_post_meta($intake_id, '_intake_notify_attempts', true);
	if ($attempts >= 3) {
		if (function_exists('wc_get_logger')) {
			wc_get_logger()->warning(
				sprintf(
					'Therapist intake notification abandoned for intake #%d after %d attempts.',
					$intake_id,
					$attempts
				),
				['source' => 'welness-intake']
			);
		}
		return;
	}

	$attempts++;
	update_post_meta($intake_id, '_intake_notify_attempts', $attempts);

	$order_id = (int) get_post_meta($intake_id, '_intake_order_id', true);
	$order    = $order_id ? wc_get_order($order_id) : false;
	if (! $order) {
		if (function_exists('wc_get_logger')) {
			wc_get_logger()->warning(
				sprintf(
					'Therapist intake notification skipped for intake #%d: order #%d not found.',
					$intake_id,
					$order_id
				),
				['source' => 'welness-intake']
			);
		}
		return;
	}

	if (wellness_send_intake_submitted_notification($order, $intake_id)) {
		return;
	}

	// Retry with backoff: 5 min, 30 min, 2 h.
	$delays = [
		5 * MINUTE_IN_SECONDS,
		30 * MINUTE_IN_SECONDS,
		2 * HOUR_IN_SECONDS,
	];
	$delay  = isset($delays[ $attempts - 1 ]) ? $delays[ $attempts - 1 ] : 2 * HOUR_IN_SECONDS;

	as_schedule_single_action(time() + $delay, 'wellness-intake-therapist-notification', [$intake_id], 'wca');
}

/**
 * Email the therapist the completed intake form when the client submits it on
 * the thank-you page.
 *
 * Recipient: the appointment's therapist, falling back to the site admin.
 * Sent once per intake record (guarded by the _intake_notified meta).
 *
 * @param WC_Order $order     The order the intake belongs to.
 * @param int      $intake_id The created customer_intake_form post ID.
 * @return bool Whether an email was sent.
 */
function wellness_send_intake_submitted_notification($order, $intake_id)
{
	if (! $order instanceof WC_Order || ! $intake_id) {
		return false;
	}

	// Send once per intake record.
	if (get_post_meta($intake_id, '_intake_notified', true)) {
		return false;
	}

	$appointment = wellness_intake_reminder_appointment($order->get_id());

	// Therapist recipient (fallback: site admin) — same chain as other notices.
	$staff_email = '';
	if ($appointment) {
		$staff_ids = $appointment->get_staff_ids();
		if (! empty($staff_ids)) {
			$u = get_user_by('ID', (int) $staff_ids[0]);
			if ($u) {
				$staff_email = $u->user_email;
			}
		}
	}
	$to = $staff_email ?: get_option('admin_email');
	if (empty($to)) {
		return false;
	}

	$client_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
	if (empty($client_name)) {
		$client_name = (string) get_post_meta($intake_id, '_intake_client_name', true);
	}

	$subject = sprintf(
		/* translators: %s: client name */
		__('Intake form received: %s', 'woodmart-child'),
		$client_name ?: __('New client', 'woodmart-child')
	);

	$mailer = WC()->mailer();
	if (! $mailer) {
		return false;
	}

	ob_start();
	$email_obj     = new stdClass();
	$email_obj->id = 'therapist_intake_submitted';
	wc_get_template(
		'emails/therapist-intake-submitted.php',
		[
			'appointment'   => $appointment,
			'order'         => $order,
			'intake_id'     => $intake_id,
			'email_heading' => __('Client intake form submitted', 'woodmart-child'),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'         => $email_obj,
		],
		'',
		get_stylesheet_directory() . '/woocommerce/'
	);
	$message = ob_get_clean();

	$message = $mailer->wrap_message($subject, $message);
	$sent    = $mailer->send($to, $subject, $message, $mailer->get_headers(), []);

	if ($sent) {
		update_post_meta($intake_id, '_intake_notified', current_time('mysql'));
	}

	return (bool) $sent;
}


// ═══════════════════════════════════════════════════════════════════════════════
// Task 3c — One-shot intake reminder email (~24h after booking)
// Sent when the client has not completed their intake form. See
// docs/INTAKE-THANKYOU-PLAN.md.
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Reminder delay in hours. Overridable with the WELLNESS_INTAKE_REMINDER_DELAY_HOURS
 * constant or the 'wellness_intake_reminder_delay_hours' option. Defaults to 24.
 *
 * @return int
 */
function wellness_intake_reminder_delay_hours()
{
	if (defined('WELLNESS_INTAKE_REMINDER_DELAY_HOURS')) {
		$hours = (int) WELLNESS_INTAKE_REMINDER_DELAY_HOURS;
	} else {
		$hours = (int) get_option('wellness_intake_reminder_delay_hours', 24);
	}
	return $hours > 0 ? $hours : 24;
}

/**
 * Schedule the one-shot intake reminder for an order.
 *
 * Fires when the order is placed and again when it is paid (processing/completed),
 * so pay-later bookings still get a reminder once confirmed. Order meta
 * '_intake_reminder_scheduled' prevents stacking; '_intake_reminder_sent' prevents
 * a second send.
 *
 * @param int $order_id
 * @return void
 */
function wellness_schedule_intake_reminder($order_id)
{
	$order = wc_get_order($order_id);
	if (! $order) {
		return;
	}

	$email = $order->get_billing_email();
	if (empty($email)) {
		return;
	}

	// Already on file (or submitted meanwhile) — nothing to remind about.
	if (wellness_intake_has_record($email)) {
		return;
	}

	// Already sent once — never again.
	if (get_post_meta($order_id, '_intake_reminder_sent', true)) {
		return;
	}

	// A reminder is already scheduled in the future for this order — don't stack.
	$scheduled_ts = (int) get_post_meta($order_id, '_intake_reminder_scheduled', true);
	if ($scheduled_ts > time()) {
		return;
	}

	$run_at = time() + (wellness_intake_reminder_delay_hours() * HOUR_IN_SECONDS);

	update_post_meta($order_id, '_intake_reminder_scheduled', $run_at);

	as_schedule_single_action($run_at, 'wellness-intake-reminder', [$order_id], 'wca');
}

add_action('woocommerce_checkout_order_processed', 'wellness_schedule_intake_reminder', 20);
add_action('woocommerce_order_status_processing', 'wellness_schedule_intake_reminder', 10, 1);
add_action('woocommerce_order_status_completed', 'wellness_schedule_intake_reminder', 10, 1);

/**
 * First appointment belonging to an order (for email context). Null if none.
 *
 * @param int $order_id
 * @return WC_Appointment|null
 */
function wellness_intake_reminder_appointment($order_id)
{
	$appt_ids = WC_Appointment_Data_Store::get_appointment_ids_from_order_id($order_id);
	if (empty($appt_ids)) {
		return null;
	}
	$appt = get_wc_appointment($appt_ids[0]);
	return $appt ? $appt : null;
}

/**
 * Send the intake reminder email. Hooked from the Action Scheduler job scheduled
 * in wellness_schedule_intake_reminder().
 *
 * @param int $order_id
 * @return void
 */
add_action('wellness-intake-reminder', 'wellness_send_intake_reminder', 10, 1);
function wellness_send_intake_reminder($order_id)
{
	// This scheduled run is now consumed.
	delete_post_meta($order_id, '_intake_reminder_scheduled');

	$order = wc_get_order($order_id);
	if (! $order) {
		return;
	}

	// Only remind once the booking is actually paid/confirmed.
	if (! $order->is_paid()) {
		return;
	}
	if ($order->has_status(['cancelled', 'refunded', 'failed', 'trash', 'checkout-draft'])) {
		return;
	}
	if (get_post_meta($order_id, '_intake_reminder_sent', true)) {
		return;
	}

	$email = $order->get_billing_email();
	if (empty($email) || wellness_intake_has_record($email)) {
		return;
	}

	$customer_first_name = $order->get_billing_first_name();
	if (empty($customer_first_name)) {
		$_full = $order->get_meta('billing_full_name', true);
		if ($_full) {
			$_parts              = explode(' ', trim($_full));
			$customer_first_name = $_parts[0];
		}
	}

	$intake_url = $order->get_checkout_order_received_url();
	$appointment = wellness_intake_reminder_appointment($order_id);

	$mailer  = WC()->mailer();
	$subject = __('Complete your intake form — The Wellness Hub', 'woodmart-child');

	ob_start();
	$email_obj     = new stdClass();
	$email_obj->id = 'customer_appointment_intake_reminder';

	wc_get_template(
		'emails/customer-appointment-intake-reminder.php',
		[
			'appointment'         => $appointment,
			'order'               => $order,
			'intake_url'          => $intake_url,
			'customer_first_name' => $customer_first_name,
			'email_heading'       => __('One last step: your intake form', 'woodmart-child'),
			'sent_to_admin'       => false,
			'plain_text'          => false,
			'email'               => $email_obj,
		],
		'',
		get_stylesheet_directory() . '/woocommerce/'
	);
	$message = ob_get_clean();

	$message = $mailer->wrap_message($subject, $message);
	$sent    = $mailer->send($email, $subject, $message, $mailer->get_headers(), []);

	if ($sent) {
		update_post_meta($order_id, '_intake_reminder_sent', current_time('mysql'));
	}
}
