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
		__('Phone & Preferences', 'woodmart-child')   => ['intake_home_phone', 'intake_home_voicemail', 'intake_work_phone', 'intake_work_voicemail', 'intake_preferred_comm'],
		__('Emergency Contact', 'woodmart-child')     => ['intake_emergency_name', 'intake_emergency_relation', 'intake_emergency_home_phone', 'intake_emergency_mobile', 'intake_emergency_voicemail'],
		__('Health Background', 'woodmart-child')     => ['intake_current_services', 'intake_past_counseling', 'intake_medications', 'intake_expectations', 'intake_emergency_text'],
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
 * Render the full intake form markup (styles + form + script) on the
 * order-received page.
 *
 * @param WC_Order $order
 * @return void
 */
function wellness_render_thankyou_intake_form($order)
{
	$defs     = wellness_get_intake_field_defs();
	$groups   = wellness_thankyou_intake_field_groups();
	$prefill  = wellness_thankyou_intake_prefill($order);
	$first    = $order->get_billing_first_name();
	$order_id = $order->get_id();
	$order_no = $order->get_order_number();
	$ajax_url = admin_url('admin-ajax.php');
	$nonce    = wp_create_nonce('wellness_thankyou_intake');

	echo '<section class="wellness-intake-th" id="wellness-thankyou-intake">';
	echo '<style>' . PHP_EOL;
	echo '.wellness-intake-th{max-width:760px;margin:32px auto;padding:26px 28px;background:#fff;border:1px solid #e6e1d9;border-radius:14px;box-shadow:0 2px 14px rgba(35,52,68,.06)}' . PHP_EOL;
	echo '.wellness-intake-th__heading{font-size:1.4rem;font-weight:700;color:#2c3e4f;margin:0 0 6px}' . PHP_EOL;
	echo '.wellness-intake-th__intro{font-size:.95rem;color:#667085;margin:0 0 20px}' . PHP_EOL;
	echo '.wellness-intake-th__group{margin:0 0 24px}' . PHP_EOL;
	echo '.wellness-intake-th__group-title{font-size:1.05rem;font-weight:700;color:#2c3e4f;border-bottom:1px solid #eee;padding-bottom:8px;margin:0 0 6px}' . PHP_EOL;
	echo '.wellness-intake-th-form label{display:block;font-size:.85rem;font-weight:600;color:#374151;margin:12px 0 4px}' . PHP_EOL;
	echo '.wellness-intake-th-form input[type="text"],.wellness-intake-th-form textarea,.wellness-intake-th-form select{width:100%;box-sizing:border-box;border:1px solid #d4d8dd;border-radius:8px;padding:10px 12px;font-size:.95rem;background:#fff;color:#111827;margin:0}' . PHP_EOL;
	echo '.wellness-intake-th-form input:focus,.wellness-intake-th-form textarea:focus,.wellness-intake-th-form select:focus{outline:none;border-color:#4b7f8c;box-shadow:0 0 0 3px rgba(75,127,140,.15)}' . PHP_EOL;
	echo '.wellness-intake-th-check{display:flex;align-items:center;gap:10px;margin:10px 0;cursor:pointer;font-weight:500;font-size:.9rem;color:#374151}' . PHP_EOL;
	echo '.wellness-intake-th-check input{width:auto;margin:0}' . PHP_EOL;
	echo '.wellness-intake-th-req{color:#b3392f}' . PHP_EOL;
	echo '.wellness-intake-th__note{font-size:.8rem;color:#667085;margin:2px 0 0}' . PHP_EOL;
	echo '.wellness-intake-th__submit{margin-top:20px;text-align:center}' . PHP_EOL;
	echo '.wellness-intake-th__submit button{background:#2c3e4f;color:#fff;border:0;border-radius:999px;padding:13px 32px;font-size:1rem;font-weight:600;cursor:pointer}' . PHP_EOL;
	echo '.wellness-intake-th__submit button:hover{background:#1f2e3b}' . PHP_EOL;
	echo '.wellness-intake-th__submit button:disabled{opacity:.6;cursor:default}' . PHP_EOL;
	echo '.wellness-intake-th__error,.wellness-intake-th__success{border-radius:8px;padding:12px 14px;margin:16px 0 0;font-size:.9rem}' . PHP_EOL;
	echo '.wellness-intake-th__error{background:#fdecea;color:#b3392f;border:1px solid #f3c1bb}' . PHP_EOL;
	echo '.wellness-intake-th__success{background:#e9f6ee;color:#1e7a3c;border:1px solid #bce6cc}' . PHP_EOL;
	echo '</style>' . PHP_EOL;

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

	echo '<form class="wellness-intake-th-form" id="wellness-thankyou-intake-form" action="' . esc_url($ajax_url) . '" method="post" novalidate>';
	echo '<input type="hidden" name="action" value="wellness_save_thankyou_intake" />';
	echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '" />';
	echo '<input type="hidden" name="order_key" value="' . esc_attr($order->get_order_key()) . '" />';
	echo '<input type="hidden" name="_ajax_nonce" value="' . esc_attr($nonce) . '" />';

	foreach ($groups as $heading => $keys) {
		echo '<div class="wellness-intake-th__group">';
		echo '<h3 class="wellness-intake-th__group-title">' . esc_html($heading) . '</h3>';
		foreach ($keys as $key) {
			if (! isset($defs[$key])) {
				continue;
			}
			$value = isset($prefill[$key]) ? $prefill[$key] : '';
			wellness_thankyou_render_field($key, $defs[$key], $value);
		}
		echo '</div>';
	}

	echo '<p class="wellness-intake-th__note">' . esc_html__('Required fields are marked with *. Your information is confidential and shared only with your therapist.', 'woodmart-child') . '</p>';

	echo '<div class="wellness-intake-th__error" id="wellness-intake-th-error" hidden></div>';
	echo '<div class="wellness-intake-th__submit"><button type="submit" id="wellness-intake-th-submit">' . esc_html__('Submit Intake Form', 'woodmart-child') . '</button></div>';
	echo '</form>';

	echo '<div class="wellness-intake-th__success" id="wellness-intake-th-success" hidden></div>';
	echo '<noscript><p class="wellness-intake-th__note">' . esc_html__('Please enable JavaScript to submit your intake form.', 'woodmart-child') . '</p></noscript>';

	echo '<script>' . PHP_EOL;
	echo '(function(){' . PHP_EOL;
	echo 'var form=document.getElementById("wellness-thankyou-intake-form");' . PHP_EOL;
	echo 'if(!form){return;}' . PHP_EOL;
	echo 'var btn=document.getElementById("wellness-intake-th-submit");' . PHP_EOL;
	echo 'var err=document.getElementById("wellness-intake-th-error");' . PHP_EOL;
	echo 'var ok=document.getElementById("wellness-intake-th-success");' . PHP_EOL;
	echo 'function show(el,msg){if(!el){return;}el.hidden=false;if(msg){el.textContent=msg;}el.scrollIntoView({behavior:"smooth",block:"center"});}' . PHP_EOL;
	echo 'form.addEventListener("submit",function(e){' . PHP_EOL;
	echo 'e.preventDefault();' . PHP_EOL;
	echo 'if(err){err.hidden=true;}' . PHP_EOL;
	echo 'var n=document.getElementById("th-intake_client_name");' . PHP_EOL;
	echo 'var m=document.getElementById("th-intake_mobile");' . PHP_EOL;
	echo 'if(!n||!m||!n.value.trim()||!m.value.trim()){show(err,"' . esc_js(__('Please enter the client name and a mobile number.', 'woodmart-child')) . '");return;}' . PHP_EOL;
	echo 'if(btn){btn.disabled=true;btn.textContent="' . esc_js(__('Saving…', 'woodmart-child')) . '";}' . PHP_EOL;
	echo 'fetch(form.action,{method:"POST",credentials:"same-origin",body:new FormData(form)})' . PHP_EOL;
	echo '.then(function(r){return r.json();})' . PHP_EOL;
	echo '.then(function(res){' . PHP_EOL;
	echo 'if(btn){btn.disabled=false;btn.textContent="' . esc_js(__('Submit Intake Form', 'woodmart-child')) . '";}' . PHP_EOL;
	echo 'if(res&&res.success){form.hidden=true;show(ok,(res.data&&res.data.message)?res.data.message:"' . esc_js(__('Thank you! Your intake form has been received.', 'woodmart-child')) . '");ok.innerHTML="<strong>"+ok.textContent+"</strong>";}' . PHP_EOL;
	echo 'else{show(err,(res&&res.data&&res.data.message)?res.data.message:"' . esc_js(__('Something went wrong. Please try again.', 'woodmart-child')) . '");}' . PHP_EOL;
	echo '}).catch(function(){if(btn){btn.disabled=false;btn.textContent="' . esc_js(__('Submit Intake Form', 'woodmart-child')) . '";}show(err,"' . esc_js(__('Something went wrong. Please try again.', 'woodmart-child')) . '");});' . PHP_EOL;
	echo '});' . PHP_EOL;
	echo '})();' . PHP_EOL;
	echo '</script>';

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

	wellness_render_thankyou_intake_form($order);
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

// ── AJAX: save the thank-you intake form ──────────────────────────────────

add_action('wp_ajax_wellness_save_thankyou_intake', 'wellness_save_thankyou_intake_ajax');
add_action('wp_ajax_nopriv_wellness_save_thankyou_intake', 'wellness_save_thankyou_intake_ajax');
function wellness_save_thankyou_intake_ajax()
{
	check_ajax_referer('wellness_thankyou_intake', '_ajax_nonce');

	$order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
	$order    = $order_id ? wc_get_order($order_id) : false;

	if (! $order) {
		wp_send_json_error(['message' => __('We could not find your order. Please contact us.', 'woodmart-child')]);
	}

	$key = isset($_POST['order_key']) ? sanitize_text_field(wp_unslash($_POST['order_key'])) : '';
	if (! hash_equals($order->get_order_key(), $key)) {
		wp_send_json_error(['message' => __('Your session could not be verified. Please reload the page and try again.', 'woodmart-child')]);
	}

	$email = $order->get_billing_email();
	if (empty($email) || wellness_intake_has_record($email)) {
		wp_send_json_error(['message' => __('Your intake form has already been submitted. Thank you!', 'woodmart-child')]);
	}

	// Required: client name + mobile (server-side).
	$client_name = isset($_POST['intake_client_name']) ? sanitize_text_field(wp_unslash($_POST['intake_client_name'])) : '';
	$mobile      = isset($_POST['intake_mobile']) ? sanitize_text_field(wp_unslash($_POST['intake_mobile'])) : '';

	$errors = [];
	if ('' === $client_name) {
		$errors[] = __('Please enter the client name.', 'woodmart-child');
	}
	if ('' === $mobile) {
		$errors[] = __('Please enter a mobile number.', 'woodmart-child');
	}
	if (! empty($errors)) {
		wp_send_json_error(['message' => implode(' ', $errors)]);
	}

	$defs   = wellness_get_intake_field_defs();
	$posted = [];
	foreach (array_keys($defs) as $field_key) {
		$posted[$field_key] = isset($_POST[$field_key]) ? wp_unslash($_POST[$field_key]) : '';
	}

	$post_id = wellness_create_intake_record($order, $posted);

	if (! $post_id) {
		wp_send_json_error(['message' => __('We could not save your intake form. Please try again or contact us.', 'woodmart-child')]);
	}

	wp_send_json_success(['message' => __('Thank you! Your intake form has been received.', 'woodmart-child')]);
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
