<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Cron job handler.
 */
class WC_Appointment_Cron_Manager {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_schedule_daily_cleanup' ) );
		add_action( 'woocommerce_admin_new_appointment_notification', [ $this, 'send_appointment_notification_to_admin' ] );
		add_action( 'wc-appointment-confirmed', [ $this, 'send_appointment_confirmation' ] );
		add_action( 'wc-appointment-reminder', [ $this, 'send_appointment_reminder' ] );
		add_action( 'wc-appointment-complete', [ $this, 'maybe_mark_appointment_complete' ] );
		add_action( 'wc-appointment-follow-up', [ $this, 'send_appointment_follow_up' ] );
		add_action( 'wc-appointment-remove-inactive-cart', [ $this, 'remove_inactive_appointment_from_cart' ] );
		add_action( 'woocommerce_appointments_daily_cleanup', [ $this, 'cleanup_cart_data' ] );
		add_action( 'woocommerce_appointments_daily_cleanup', [ $this, 'cleanup_expired_availability' ] );
	}

	/**
	 * Schedule a recurring Action Scheduler every day to cleanup
	 * the the cart data and expired availability for now.
	 */
	public function maybe_schedule_daily_cleanup() {
		if ( false === as_next_scheduled_action( 'woocommerce_appointments_daily_cleanup' ) ) {
			as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'woocommerce_appointments_daily_cleanup' );
		}
	}

	/**
	 * Send appointment to admin and staff
	 */
	public function send_appointment_notification_to_admin( $appointment_id ) {
		$appointment = get_wc_appointment( $appointment_id );

		// Don't procede if id is not of a valid appointment.
		if ( ! is_a( $appointment, 'WC_Appointment' ) ) {
			return;
		}

		$mailer       = WC()->mailer();
		$notification = $mailer->emails['WC_Email_Admin_New_Appointment'];
		$notification->trigger( $appointment_id );
	}

	/**
	 * Send appointment to admin and staff
	 */
	public function send_appointment_confirmation( $appointment_id ) {
		$appointment = get_wc_appointment( $appointment_id );

		// Don't procede if id is not of a valid appointment.
		if ( ! is_a( $appointment, 'WC_Appointment' ) ) {
			return;
		}

		$mailer       = WC()->mailer();
		$notification = $mailer->emails['WC_Email_Appointment_Confirmed'];
		$notification->trigger( $appointment_id );
	}

	/**
	 * Send appointment reminder email
	 */
	public function send_appointment_reminder( $appointment_id ) {
		$appointment = get_wc_appointment( $appointment_id );

		// Don't procede if id is not of a valid appointment.
		if ( ! is_a( $appointment, 'WC_Appointment' ) || ! $appointment->is_active() ) {
			return;
		}

		$mailer       = WC()->mailer();
		$notification = $mailer->emails['WC_Email_Appointment_Reminder'];
		$notification->trigger( $appointment_id );
	}

	/**
	 * Change the appointment status if it wasn't previously cancelled
	 */
	public function maybe_mark_appointment_complete( $appointment_id ) {
		$appointment = get_wc_appointment( $appointment_id );

		// Don't procede if id is not of a valid appointment.
		if ( ! is_a( $appointment, 'WC_Appointment' ) ) {
			return;
		}

		if ( 'cancelled' === get_post_status( $appointment_id ) ) {
			$appointment->schedule_events();
		} else {
			$this->mark_appointment_complete( $appointment );
		}
	}

	/**
	 * Send appointment follow-up email
	 */
	public function send_appointment_follow_up( $appointment_id ) {
		$appointment = get_wc_appointment( $appointment_id );

		// Don't procede if id is not of a valid appointment.
		if ( ! is_a( $appointment, 'WC_Appointment' ) || ! $appointment->is_active() ) {
			return;
		}

		$mailer       = WC()->mailer();
		$notification = $mailer->emails['WC_Email_Appointment_Follow_Up'];
		$notification->trigger( $appointment_id );
	}

	/**
	 * Change the appointment status to complete
	 */
	public function mark_appointment_complete( $appointment ) {
		$appointment->update_status( 'complete' );
		$appointment->update_customer_status( 'arrived' );
	}

	/**
	 * Remove inactive appointment
	 */
	public function remove_inactive_appointment_from_cart( $appointment_id ) {
		$appointment = $appointment_id ? get_wc_appointment( $appointment_id ) : false;
		if ( $appointment_id && $appointment && $appointment->has_status( [ 'in-cart', 'was-in-cart' ] ) ) {
			wp_delete_post( $appointment_id );
			// Scheduled hook deletes itself on execution, but do it anyways if fired manually.
			as_unschedule_action( 'wc-appointment-remove-inactive-cart', [ $appointment_id ], 'wca' );
			// Delete transient of this appointable product to free up the slots.
			if ( $appointment ) {
				WC_Appointments_Cache::delete_appointment_slots_transient( $appointment->get_product_id() );
			}
		}
	}

	/**
	 * Cleans up old in-cart data
	 *
	 * Cron callback twice per day.
	 *
	 * @since 3.7.0
	 */
	public function cleanup_cart_data() {
		// Make sure active in-cart appointment are not removed.
		$hold_stock_minutes = (int) get_option( 'woocommerce_hold_stock_minutes', 60 );
		$minutes            = apply_filters( 'woocommerce_appointments_remove_inactive_cart_time', $hold_stock_minutes );
		$timestamp          = current_time( 'timestamp' ) - MINUTE_IN_SECONDS * (int) $minutes;

		$appointment_ids = WC_Appointment_Data_Store::get_appointment_ids_by(
			[
				'status'           => [ 'in-cart', 'was-in-cart' ],
				'post_date_before' => $timestamp,
				'limit'            => 100,
			]
		);

		if ( $appointment_ids ) {
			foreach ( $appointment_ids as $appointment_id ) {
				wp_trash_post( $appointment_id );
			}
		}
	}

	/**
	 * Delete expired availability rules in batches.
	 *
	 * Expiration logic:
	 * - Non-recurring rules (rrule IS NULL or empty) with a finite to_date in the past are removed.
	 * - Optionally, recurring rules with UNTIL in the past can be removed if the rrule contains an UNTIL
	 *   date (basic parse). This can be toggled via the 'cleanup_rrule_until' filter.
	 *
	 * Notes:
	 * - The availability table stores dates as varchar; we parse them in PHP for reliability.
	 * - Batch size is small to avoid long-running cron tasks.
	 */
	function cleanup_expired_availability() {
		global $wpdb;

		$table = $wpdb->prefix . 'wc_appointments_availability';
		$now   = current_time( 'timestamp', true ); // GMT timestamp.

		$batch_size          = (int) apply_filters( 'wc_appointments_cleanup_batch_size', 300 );
		$process_rrule_until = (bool) apply_filters( 'wc_appointments_cleanup_rrule_until', true );
		$process_rrule_count = (bool) apply_filters( 'wc_appointments_cleanup_rrule_count', true );

		// Fetch candidate rows and exclude ones already marked as expired.
		$candidates = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, rrule, from_date, to_date, from_range, to_range, range_type
				 FROM {$table}
				 WHERE (
				 	    ( (rrule IS NULL OR rrule = '') AND to_date IS NOT NULL AND to_date <> '' )
				     OR ( %d = 1 AND rrule IS NOT NULL AND rrule <> '' AND rrule LIKE '%%UNTIL%%' )
				     OR ( %d = 1 AND rrule IS NOT NULL AND rrule <> '' AND rrule LIKE '%%COUNT%%' )
				 )
				   AND (range_type NOT LIKE '%%:expired')
				 ORDER BY ID ASC
				 LIMIT %d",
				$process_rrule_until ? 1 : 0,
				$process_rrule_count ? 1 : 0,
				$batch_size
			)
		);

		if ( empty( $candidates ) ) {
			return;
		}

		$ids_to_mark = array();

		foreach ( $candidates as $row ) {
			// Double-check not already marked.
			if ( is_string( $row->range_type ) && substr( $row->range_type, -8 ) === ':expired' ) {
				continue;
			}

			$rrule = is_string( $row->rrule ) ? trim( $row->rrule ) : '';
			$to    = is_string( $row->to_date ) ? trim( $row->to_date ) : '';
			$fromR = is_string( $row->from_range ) ? trim( $row->from_range ) : '';
			$toR   = is_string( $row->to_range ) ? trim( $row->to_range ) : '';

			// Non-recurring: expire by to_date < now.
			if ( $rrule === '' ) {
				$to_ts = $to !== '' ? strtotime( $to . ' UTC' ) : false;
				if ( $to_ts && $to_ts < $now ) {
					$ids_to_mark[] = (int) $row->ID;
				}
				continue;
			}

			// Recurring with UNTIL: expire if UNTIL < now.
			if ( $process_rrule_until && stripos( $rrule, 'UNTIL' ) !== false ) {
				if ( preg_match( '/(?:^|;)\s*UNTIL=([0-9]{8}(T[0-9]{6}Z?)?)/i', $rrule, $m ) ) {
					$until_raw = $m[1];
					if ( strlen( $until_raw ) === 8 ) {
						$until_str = substr( $until_raw, 0, 4 ) . '-' . substr( $until_raw, 4, 2 ) . '-' . substr( $until_raw, 6, 2 ) . ' 23:59:59 UTC';
					} else {
						$ymd = substr( $until_raw, 0, 8 );
						$hms = preg_replace( '/[^0-9]/', '', substr( $until_raw, 9 ) );
						$until_str = substr( $ymd, 0, 4 ) . '-' . substr( $ymd, 4, 2 ) . '-' . substr( $ymd, 6, 2 ) .
							' ' . substr( $hms, 0, 2 ) . ':' . substr( $hms, 2, 2 ) . ':' . substr( $hms, 4, 2 ) . ' UTC';
					}
					$until_ts = strtotime( $until_str );
					if ( $until_ts && $until_ts < $now ) {
						$ids_to_mark[] = (int) $row->ID;
						continue;
					}
				}
			}

			// Recurring with COUNT: expire if last computed occurrence < now.
			if ( $process_rrule_count && stripos( $rrule, 'COUNT' ) !== false ) {
				// Use PHP RRULE library when available for accurate expansion.
				if ( class_exists( '\RRule\RRule' ) ) {
					try {
						// DTSTART must come from from_range (fallback to to_range). These may include time.
						$dtstart_str = $fromR !== '' ? $fromR : $toR;

						if ( ! $dtstart_str ) {
							continue;
						}

						$dtstart = new DateTimeImmutable( $dtstart_str, new DateTimeZone( 'UTC' ) );

						$rule        = new \RRule\RRule( $rrule, $dtstart );
						$occurrences = $rule->getOccurrences();

						$last = null;
						foreach ( $occurrences as $occurrence ) {
							$last = $occurrence; // Iterates up to COUNT.
						}

						if ( $last instanceof DateTimeInterface ) {
							$last_ts = (int) $last->getTimestamp();

							if ( $last_ts < $now ) {
								$ids_to_mark[] = (int) $row->ID;
							}
						}
					} catch ( \Throwable $e ) {
						// Skip on parse/compute errors.
						continue;
					}
				}
			}
		}

		$ids_to_mark = array_values( array_unique( array_filter( $ids_to_mark ) ) );

		#error_log( var_export( $ids_to_mark, true ) );

		if ( empty( $ids_to_mark ) ) {
			return;
		}

		// Mark as expired by appending ':expired' (idempotent).
		$in = implode( ',', array_map( 'intval', $ids_to_mark ) );
		$wpdb->query(
			"UPDATE {$table}
			    SET range_type = CASE
					WHEN RIGHT(range_type, 8) = ':expired' THEN range_type
					ELSE CONCAT(range_type, ':expired')
				END
			  WHERE ID IN ({$in})
			    AND (range_type NOT LIKE '%:expired')"
		);

		if ( class_exists( 'WC_Appointments_Cache' ) ) {
			WC_Appointments_Cache::clear_cache();
		}

		/**
		 * Fired after a cleanup pass marks expired availability rules.
		 *
		 * @param int[] $ids Marked rule IDs.
		 */
		do_action( 'wc_appointments_after_cleanup_expired_availability', $ids_to_mark );
	}



}

$GLOBALS['wc_appointment_cron_manager'] = new WC_Appointment_Cron_Manager();
