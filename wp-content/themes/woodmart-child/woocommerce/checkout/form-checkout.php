<?php

/**
 * Checkout Form — Wellness Hub Custom Template
 *
 * Overrides Woodmart's form-checkout.php to place step wrappers directly
 * in the template, eliminating hook-priority guesswork. Step 1 wraps
 * billing + account registration + shipping. Steps 2-6 (intake) render
 * via hooks on woocommerce_checkout_after_customer_details.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 9.4.0
 */

if (! defined('ABSPATH')) {
    exit;
}

//WC 3.5.0
if (function_exists('WC') && version_compare(WC()->version, '3.5.0', '<')) {
    wc_print_notices();
}

do_action('woocommerce_before_checkout_form', $checkout);

// ═══════════════════════════════════════════════════════════════════════════════
// Premium Wellness Intake Form Styles — "Calm Sanctuary"
// Uses the Woodmart theme's --wd-primary-color for brand consistency.
// ═══════════════════════════════════════════════════════════════════════════════
?>
<style>
    /* ── CSS Custom Properties (primary colour from Woodmart theme) ── */
    :root {
        --wellness-primary: var(--wd-primary-color);
        --wellness-primary-deep: color-mix(in srgb, var(--wd-primary-color) 80%, #000 20%);
        --wellness-primary-soft: color-mix(in srgb, var(--wd-primary-color) 12%, #fff 88%);
        --wellness-primary-mist: color-mix(in srgb, var(--wd-primary-color) 5%, #fff 95%);
        --wellness-cream: #FDFBF7;
        --wellness-warm: #F5F0E8;
        --wellness-charcoal: #2C2416;
        --wellness-muted: #6B5E4F;
        --wellness-border: #E0D9CF;
        --wellness-white: #FFFFFF;
        --wellness-radius: 12px;
        --wellness-radius-sm: 8px;
        --wellness-shadow: 0 1px 3px rgba(44, 36, 22, 0.04), 0 4px 24px rgba(44, 36, 22, 0.05);
        --wellness-shadow-lg: 0 2px 8px rgba(44, 36, 22, 0.04), 0 8px 40px rgba(44, 36, 22, 0.08);
        --wellness-transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Page-level atmosphere ────────────────────────────── */
    body.woocommerce-checkout {
        background: linear-gradient(180deg, var(--wellness-cream) 0%, var(--wellness-warm) 100%);
    }

    /* ── Two-column layout ────────────────────────────────── */
    form.checkout.woocommerce-checkout {
        display: flex !important;
        flex-direction: row !important;
        gap: 32px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    form.checkout .customer-details {
        flex: 1 1 58% !important;
        min-width: 0;
        width: auto !important;
        float: none !important;
    }

    form.checkout .checkout-order-review {
        flex: 0 0 380px !important;
        width: 380px !important;
        max-width: none !important;
        position: sticky !important;
        top: 32px;
        align-self: flex-start;
        background: var(--wellness-white) !important;
        border-radius: var(--wellness-radius);
        padding: 28px !important;
        box-shadow: var(--wellness-shadow-lg);
        border: 1px solid var(--wellness-border);
    }

    /* Kill Woodmart scalloped-edge pseudo‑elements on the order review */
    form.checkout .checkout-order-review::before,
    form.checkout .checkout-order-review::after {
        display: none !important;
    }

    form.checkout .checkout-order-review #order_review_heading {
        margin-top: 0;
        font-size: 1.15em;
        color: var(--wellness-charcoal);
    }

    @media (max-width: 900px) {
        form.checkout.woocommerce-checkout {
            flex-direction: column !important;
        }

        form.checkout .checkout-order-review {
            flex: 1 1 auto !important;
            width: 100% !important;
            position: static;
        }
    }

    /* ── Progress indicator ──────────────────────────────── */
    .wellness-progress {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
        padding: 0 12px;
    }

    .wellness-progress__dot {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--wellness-white);
        border: 2px solid var(--wellness-border);
        position: relative;
        transition: all var(--wellness-transition);
        cursor: default;
    }

    .wellness-progress__dot::after {
        content: '';
        position: absolute;
        left: calc(100% + 20px);
        top: 50%;
        width: 20px;
        height: 1px;
        background: var(--wellness-border);
        transform: translateY(-50%);
        transition: background var(--wellness-transition);
    }

    .wellness-progress__dot:last-child::after {
        display: none;
    }

    .wellness-progress__dot--active {
        border-color: var(--wellness-primary);
        background: var(--wellness-primary-soft);
        box-shadow: 0 0 0 6px color-mix(in srgb, var(--wd-primary-color) 10%, transparent 90%);
    }

    .wellness-progress__dot--active::after {
        background: var(--wellness-primary);
    }

    .wellness-progress__dot--done {
        border-color: var(--wellness-primary);
        background: var(--wellness-primary);
    }

    .wellness-progress__dot--done::after {
        background: var(--wellness-primary);
    }

    .wellness-progress__dot-inner {
        font-size: 0.7em;
        font-weight: 600;
        color: var(--wellness-muted);
        transition: color var(--wellness-transition);
    }

    .wellness-progress__dot--active .wellness-progress__dot-inner {
        color: var(--wellness-primary-deep);
    }

    .wellness-progress__dot--done .wellness-progress__dot-inner::after {
        content: '✓';
        color: var(--wellness-white);
        font-size: 0.85em;
    }

    /* ── Step card ────────────────────────────────────────── */
    .wellness-checkout-step {
        margin-bottom: 0;
        border-radius: var(--wellness-radius);
        overflow: hidden;
        background: var(--wellness-white);
        border: 1px solid var(--wellness-border);
        box-shadow: var(--wellness-shadow);
        transition: box-shadow var(--wellness-transition), transform var(--wellness-transition);
    }

    .wellness-checkout-step:hover {
        box-shadow: var(--wellness-shadow-lg);
    }

    .wellness-checkout-step+.wellness-checkout-step {
        margin-top: 20px;
    }

    /* ── Step header ─────────────────────────────────────── */
    .wellness-step-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 28px;
        background: linear-gradient(135deg, var(--wellness-primary-mist) 0%, var(--wellness-cream) 100%);
        border-bottom: 1px solid var(--wellness-border);
    }

    .wellness-step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--wellness-primary);
        color: #fff;
        font-size: 0.8em;
        font-weight: 700;
        flex-shrink: 0;
    }

    .wellness-step-number svg {
        display: block;
        width: 16px;
        height: 16px;
    }

    .wellness-step-title {
        font-size: 1.1em;
        font-weight: 600;
        color: var(--wellness-charcoal);
        letter-spacing: -0.01em;
    }

    .wellness-step-divider {
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, var(--wellness-primary) 0%, transparent 100%);
        opacity: 0.3;
    }

    /* ── Step body ───────────────────────────────────────── */
    .wellness-step-body {
        padding: 28px;
    }

    /* ── Step 1: Override WooCommerce billing field styles ── */
    #wellness-step-1 .woocommerce-billing-fields h3 {
        font-size: 1em;
        margin: 0 0 16px;
        color: var(--wellness-muted);
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
    }

    #wellness-step-1 .woocommerce-billing-fields__field-wrapper .form-row {
        margin-bottom: 16px;
    }

    #wellness-step-1 .woocommerce-billing-fields__field-wrapper .form-row label {
        font-size: 0.85em;
        font-weight: 500;
        color: var(--wellness-muted);
        margin-bottom: 4px;
        display: block;
    }

    #wellness-step-1 .woocommerce-billing-fields__field-wrapper .form-row .input-text {
        border: 1.5px solid var(--wellness-border);
        border-radius: var(--wellness-radius-sm);
        padding: 12px 16px;
        font-size: 0.95em;
        color: var(--wellness-charcoal);
        background: var(--wellness-cream);
        transition: border-color var(--wellness-transition), box-shadow var(--wellness-transition), background var(--wellness-transition);
        width: 100%;
    }

    #wellness-step-1 .woocommerce-billing-fields__field-wrapper .form-row .input-text:focus {
        border-color: var(--wellness-primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--wd-primary-color) 12%, transparent 88%);
        background: var(--wellness-white);
        outline: none;
    }

    /* ── Standard label (above input) ───────────────────── */
    .wellness-label {
        display: block;
        margin-bottom: 4px;
        font-size: 0.82em;
        font-weight: 600;
        color: var(--wellness-charcoal);
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    /* ── Base input / textarea / select styling ──────────── */
    .wellness-input,
    .wellness-textarea,
    .wellness-select {
        width: 100%;
        border: 1.5px solid var(--wellness-border);
        border-radius: var(--wellness-radius-sm);
        padding: 10px 16px;
        font-size: 0.95em;
        line-height: 1.5;
        color: var(--wellness-charcoal);
        background: var(--wellness-cream);
        transition: border-color var(--wellness-transition), box-shadow var(--wellness-transition), background var(--wellness-transition);
        font-family: inherit;
        appearance: none;
        -webkit-appearance: none;
    }

    .wellness-input::placeholder,
    .wellness-textarea::placeholder {
        color: #C4B8A8;
    }

    .wellness-select {
        padding-right: 40px;
        cursor: pointer;
        background-image: none;
    }

    .wellness-textarea {
        resize: vertical;
        min-height: 90px;
    }

    /* ── Focus state ─────────────────────────────────────── */
    .wellness-input:focus,
    .wellness-textarea:focus,
    .wellness-select:focus {
        outline: none;
        border-color: var(--wellness-primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--wd-primary-color) 12%, transparent 88%);
        background: var(--wellness-white);
    }

    /* ── Select wrapper + chevron ────────────────────────── */
    .wellness-select-wrapper {
        position: relative;
    }
    .wellness-select-wrapper .wellness-select {
        width: 100%;
    }
    .wellness-select-chevron {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--wellness-muted);
        pointer-events: none;
    }
    .wellness-select:focus + .wellness-select-chevron {
        color: var(--wellness-primary);
    }

    /* ── Required star ───────────────────────────────────── */
    .wellness-star {
        color: #C76B5A;
        font-weight: 700;
    }

    /* ── Field wrapper spacing ───────────────────────────── */
    .wellness-field {
        margin-bottom: 18px;
    }

    .wellness-field--checkbox {
        margin-bottom: 14px;
    }

    /* ── Toggle switch (checkbox) ────────────────────────── */
    .wellness-toggle {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }

    .wellness-toggle input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .wellness-toggle__track {
        position: relative;
        width: 44px;
        height: 24px;
        border-radius: 12px;
        background: #D9D2C5;
        transition: background var(--wellness-transition);
        flex-shrink: 0;
    }

    .wellness-toggle__thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--wellness-white);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .wellness-toggle input:checked+.wellness-toggle__track {
        background: var(--wellness-primary);
    }

    .wellness-toggle input:checked+.wellness-toggle__track .wellness-toggle__thumb {
        transform: translateX(20px);
    }

    .wellness-toggle input:focus-visible+.wellness-toggle__track {
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--wd-primary-color) 25%, transparent 75%);
    }

    .wellness-toggle__label {
        font-size: 0.9em;
        color: var(--wellness-charcoal);
        line-height: 1.4;
    }

    /* ── Subsection headings ─────────────────────────────── */
    .wellness-intake-subsection-heading {
        font-size: 0.85em;
        font-weight: 600;
        color: var(--wellness-primary-deep);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin: 20px 0 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--wellness-primary-soft);
    }

    /* ── Field group wrapper ─────────────────────────────── */
    .wellness-intake-fields {
        margin-bottom: 4px;
    }

    /* ── Action buttons ──────────────────────────────────── */
    .wellness-step-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 28px;
        border-top: 1px solid var(--wellness-border);
        background: var(--wellness-primary-mist);
        gap: 12px;
    }

    .wellness-btn-next {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: var(--wellness-primary);
        color: #fff;
        border: none;
        border-radius: var(--wellness-radius-sm);
        font-size: 0.95em;
        font-weight: 600;
        cursor: pointer;
        transition: background var(--wellness-transition), transform var(--wellness-transition), box-shadow var(--wellness-transition);
        font-family: inherit;
        letter-spacing: 0.01em;
        margin-left: auto;
    }

    .wellness-btn-next:hover {
        background: #fff;
        color: var(--wellness-primary);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px color-mix(in srgb, var(--wd-primary-color) 30%, transparent 70%);
    }

    .wellness-btn-next:active {
        transform: translateY(0);
    }

    .wellness-btn-next__icon {
        transition: transform var(--wellness-transition);
    }

    .wellness-btn-next:hover .wellness-btn-next__icon {
        transform: translateX(3px);
    }

    .wellness-btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 20px;
        background: transparent;
        color: var(--wellness-muted);
        border: 1.5px solid var(--wellness-border);
        border-radius: var(--wellness-radius-sm);
        font-size: 0.9em;
        font-weight: 500;
        cursor: pointer;
        transition: color var(--wellness-transition), border-color var(--wellness-transition), background var(--wellness-transition);
        font-family: inherit;
    }

    .wellness-btn-back:hover {
        color: var(--wellness-charcoal);
        border-color: var(--wellness-charcoal);
        background: var(--wellness-white);
    }

    .wellness-btn-back__icon {
        transition: transform var(--wellness-transition);
        flex-shrink: 0;
    }

    .wellness-btn-back:hover .wellness-btn-back__icon {
        transform: translateX(-2px);
    }

    /* ── Loading state ───────────────────────────────────── */
    .wellness-btn-next.wellness-loading {
        pointer-events: none;
        opacity: 0.8;
        position: relative;
        color: transparent;
    }

    .wellness-btn-next.wellness-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 18px;
        height: 18px;
        margin: -9px 0 0 -9px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: wellness-spin 0.7s linear infinite;
    }

    @keyframes wellness-spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ── Step visibility ─────────────────────────────────── */
    .wellness-checkout-step {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .wellness-checkout-step.wellness-step-hidden {
        display: none !important;
    }

    /* ── Step entrance animation ─────────────────────────── */
    .wellness-checkout-step.wellness-step-entering {
        animation: wellness-fadeSlideIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes wellness-fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ── Subtle noise texture on step cards ──────────────── */
    .wellness-checkout-step::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.015;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.5'/%3E%3C/svg%3E");
        background-size: 128px;
        z-index: 0;
        border-radius: var(--wellness-radius);
    }

    .wellness-checkout-step>* {
        position: relative;
        z-index: 1;
    }

    /* ── Accessibility: reduced motion ───────────────────── */
    @media (prefers-reduced-motion: reduce) {

        .wellness-checkout-step,
        .wellness-checkout-step.wellness-step-entering,
        .wellness-btn-next,
        .wellness-btn-next:hover,
        .wellness-btn-next__icon,
        .wellness-btn-back__icon,
        .wellness-toggle__thumb,
        .wellness-input,
        .wellness-textarea,
        .wellness-select {
            transition: none !important;
            animation: none !important;
        }
    }

    /* ── Touch device optimizations ──────────────────────── */
    @media (hover: none) and (pointer: coarse) {
        .wellness-btn-next:hover {
            transform: none;
            box-shadow: none;
        }

        .wellness-checkout-step::before {
            display: none;
        }
    }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .wellness-step-body {
            padding: 18px;
        }

        .wellness-step-header {
            padding: 14px 18px;
        }

        .wellness-step-actions {
            padding: 16px 18px;
            flex-wrap: wrap;
        }

        .wellness-btn-next,
        .wellness-btn-back {
            width: 100%;
            justify-content: center;
        }

        .wellness-btn-next {
            margin-left: 0;
            order: -1;
        }

        .wellness-progress {
            gap: 12px;
        }

        .wellness-progress__dot {
            width: 28px;
            height: 28px;
        }
    }
</style>
<?php

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters('woocommerce_get_checkout_url', wc_get_checkout_url()); ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url($get_checkout_url); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

    <?php if ($checkout->get_checkout_fields()) : ?>

        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

        <div class="customer-details" id="customer_details">

            <!-- Step 1: Contact & Billing -->
            <div class="wellness-checkout-step" id="wellness-step-1" data-step="1" role="region" aria-label="<?php echo esc_attr__('Step 1: Contact & Billing', 'woodmart-child'); ?>">
                <div class="wellness-step-header">
                    <span class="wellness-step-number" aria-hidden="true"><?php echo wellness_step_icon(1); ?></span>
                    <span class="wellness-step-title"><?php esc_html_e('Contact & Billing', 'woodmart-child'); ?></span>
                    <span class="wellness-step-divider"></span>
                </div>
                <div class="wellness-step-body">
                    <?php do_action('woocommerce_checkout_billing'); ?>
                    <?php do_action('woocommerce_checkout_shipping'); ?>
                    <div class="wellness-step-actions">
                        <button type="button" class="wellness-btn-next" data-next="2">
                            <span><?php esc_html_e('Continue', 'woodmart-child'); ?></span>
                            <svg class="wellness-btn-next__icon" width="16" height="16" viewBox="0 0 16 16">
                                <path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>


            <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            <?php do_action('wellness_checkout_payment_step'); ?>
        </div>

    <?php endif; ?>


    <div class="checkout-order-review">
        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

        <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>

        <?php do_action('woocommerce_checkout_before_order_review'); ?>

        <div id="order_review" class="woocommerce-checkout-review-order">
            <?php do_action('woocommerce_checkout_order_review'); ?>
        </div>

        <?php do_action('woocommerce_checkout_after_order_review'); ?>

    </div>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>