<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use RRule\RSet;

/**
 * Class that parses and returns rules for appointable products.
 */
class WC_Product_Appointment_Rule_Manager {

	/**
	 * Get a range and put value inside each day
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  mixed  $value
	 * @return array|void
	 */
	private static function get_custom_range( $from, $to, $value ) {
		$availability = [];
		$from_date    = strtotime( $from );
		$to_date      = strtotime( $to );

		if ( empty( $to ) || empty( $from ) || $to_date < $from_date ) {
			return;
		}

		// We have at least 1 day, even if from_date == to_date
		$number_of_days = 1 + ( $to_date - $from_date ) / 60 / 60 / 24;

		for ( $i = 0; $i < $number_of_days; $i ++ ) {
			$year  = date( 'Y', strtotime( "+{$i} days", $from_date ) );
			$month = date( 'n', strtotime( "+{$i} days", $from_date ) );
			$day   = date( 'j', strtotime( "+{$i} days", $from_date ) );

			$availability[ $year ][ $month ][ $day ] = $value;
		}

		return $availability;
	}

	/**
	 * Get a range and put value inside each day
	 *
	 * Generates availability data where time range starts on first date on the beginning
	 * time and ends on the last date at the end time.
	 *
	 * @since 4.4.0
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  mixed  $value
	 * @return array|void
	 */
	private static function get_custom_datetime_range( $from_day, $to_day, $time_range ) {
		$availability = [];
		$from_date    = strtotime( $from_day );
		$to_date      = strtotime( $to_day );

		if ( empty( $to_day ) || empty( $from_day ) || $to_date < $from_date ) {
			return;
		}
		// We have at least 1 day, even if from_date == to_date
		$number_of_days = 1 + ( $to_date - $from_date ) / 60 / 60 / 24;

		for ( $i = 0; $i < $number_of_days; $i ++ ) {
			$year  = date( 'Y', strtotime( "+{$i} days", $from_date ) );
			$month = date( 'n', strtotime( "+{$i} days", $from_date ) );
			$day   = date( 'j', strtotime( "+{$i} days", $from_date ) );

			// First day starts at start time, other days start at midnight.
			if ( 0 === $i ) {
				$start = $time_range['from'];
			} else {
				$start = '00:00';
			}

			// Last day ends at end time, other days end at midnight.
			if ( $number_of_days - 1 === $i ) {
				$end = $time_range['to'];
			} else {
				$end = '24:00';
			}

			$time_range_for_day = array(
				'from' => $start,
				'to'   => $end,
				'rule' => $time_range['rule'],
			);

			$availability[ $year ][ $month ][ $day ] = $time_range_for_day;
		}

		return $availability;
	}

	/**
	 * Get a range and put value inside each day
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  mixed  $value
	 * @return array
	 */
	private static function get_months_range( $from, $to, $value ) {
		$months = [];
		$diff   = $to - $from;
		$diff   = 0 > $diff ? $diff + 12 : $diff;
		$month  = $from;

		for ( $i = 0; $i <= $diff; $i ++ ) {
			$months[ $month ] = $value;

			$month ++;

			if ( $month > 12 ) {
				$month = 1;
			}
		}

		return $months;
	}

	/**
	 * Get a range and put value inside each day
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  mixed  $value
	 * @return array
	 */
	private static function get_weeks_range( $from, $to, $value ) {
		$weeks = [];
		$diff  = $to - $from;
		$diff  = 0 > $diff ? $diff + 52 : $diff;
		$week  = $from;

		for ( $i = 0; $i <= $diff; $i ++ ) {
			$weeks[ $week ] = $value;

			$week ++;

			if ( $week > 52 ) {
				$week = 1;
			}
		}

		return $weeks;
	}

	/**
	 * Get a range and put value inside each day
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  mixed  $value
	 * @return array
	 */
	private static function get_days_range( $from, $to, $value ) {
		$from        = absint( $from );
		$to          = absint( $to );
		$day_of_week = $from;
		$diff        = $to - $from;
		$diff        = ( $diff < 0 ) ? 7 + $diff : $diff;
		$days        = [];

		for ( $i = 0; $i <= $diff; $i ++ ) {
			$days[ $day_of_week ] = $value;

			$day_of_week ++;

			if ( $day_of_week > 7 ) {
				$day_of_week = 1;
			}
		}

		return $days;
	}

	/**
	 * Get a range and put value inside each day
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  mixed  $value
	 * @return array
	 */
	private static function get_time_range( $from, $to, $value, $day = 0 ) {
		return array(
			'from' => $from,
			'to'   => $to,
			'rule' => $value,
			'day'  => $day,
		);
	}

	/**
	 * Get a time range for a set of custom dates.
	 *
	 * Generates availability data where time range is repeated for each day in range.
	 *
	 * @param  string $from_date
	 * @param  string $to_date
	 * @param  string $from_time
	 * @param  string $to_time
	 * @param  mixed  $value
	 * @return array
	 */
	private static function get_time_range_for_custom_date( $from_date, $to_date, $from_time, $to_time, $value ) {
		$time_range = array(
			'from' => $from_time,
			'to'   => $to_time,
			'rule' => $value,
		);
		return self::get_custom_range( $from_date, $to_date, $time_range );
	}

	/**
	 * Get a time range for a set of custom dates.
	 *
	 * Generates availability data where time range starts on first date on the beginning
	 * time and ends on the last date at the end time.
	 *
	 * @since 4.4.0
	 *
	 * @param  string $from_date
	 * @param  string $to_date
	 * @param  string $from_time
	 * @param  string $to_time
	 * @param  mixed  $value
	 * @return array
	 */
	private static function get_time_range_for_custom_datetime( $from_date, $to_date, $from_time, $to_time, $value ) {
		$time_range = array(
			'from' => $from_time,
			'to'   => $to_time,
			'rule' => $value,
		);
		return self::get_custom_datetime_range( $from_date, $to_date, $time_range );
	}

	/**
	 * Get duration range
	 *
	 * @param  [type] $from
	 * @param  [type] $to
	 * @param  [type] $value
	 * @return [type]
	 */
	private static function get_duration_range( $from, $to, $value ) {
		return array(
			'from' => $from,
			'to'   => $to,
			'rule' => $value,
		);
	}

	/**
	 * Get slots range
	 *
	 * @param  [type] $from
	 * @param  [type] $to
	 * @param  [type] $value
	 * @return [type]
	 */
	private static function get_slots_range( $from, $to, $value ) {
		return array(
			'from' => $from,
			'to'   => $to,
			'rule' => $value,
		);
	}

	/**
	 * Get recurring rule range
	 *
	 * @param  [type] $from
	 * @param  [type] $to
	 * @param  [type] $rrule
	 * @param  [type] $value
	 * @return [type]
	 */
	private static function get_rrule_range( $from, $to, $rrule, $value ) {
		return array(
			'from'  => $from,
			'to'    => $to,
			'rule'  => $value,
			'rrule' => $rrule,
		);
	}

	/**
	 * Get quant range
	 *
	 * @param  [type] $from
	 * @param  [type] $to
	 * @param  [type] $value
	 * @return [type]
	 */
	private static function get_quant_range( $from, $to, $value ) {
		return array(
			'from' => $from,
			'to'   => $to,
			'rule' => $value,
		);
	}

	/**
	 * Process and return formatted cost rules
	 *
	 * @param  $rules array
	 * @return array
	 */
	public static function process_pricing_rules( $rules ) {
		$costs = [];
		$index = 1;

		if ( ! is_array( $rules ) ) {
			return $costs;
		}

		// Go through rules.
		foreach ( $rules as $key => $fields ) {
			if ( empty( $fields['cost'] ) && empty( $fields['base_cost'] ) ) {
				continue;
			}

			// Skip expired rules by type (e.g., '%:expired').
			if ( isset( $fields['type'] ) && false !== strpos( $fields['type'], ':expired' ) ) {
				continue;
			}

			$cost          = apply_filters( 'woocommerce_appointments_process_pricing_rules_cost', $fields['cost'], $fields, $key );
			$modifier      = $fields['modifier'];
			$base_cost     = apply_filters( 'woocommerce_appointments_process_pricing_rules_base_cost', $fields['base_cost'], $fields, $key );
			$base_modifier = $fields['base_modifier'];

			$cost_array = array(
				'base' => array( $base_modifier, $base_cost ),
				'slot' => array( $modifier, $cost ),
			);

			$type_function = self::get_type_function( $fields['type'] );
			if ( ! $type_function ) {
				continue;
			}
			if ( 'get_time_range_for_custom_date' === $type_function ) {
				$type_costs = self::$type_function( $fields['from_date'], $fields['to_date'], $fields['from'], $fields['to'], $cost_array );
			} else {
				$type_costs = self::$type_function( $fields['from'], $fields['to'], $cost_array );
			}

			// Ensure day gets specified for time: rules.
			if ( strrpos( $fields['type'], 'time:' ) === 0 && 'time:range' !== $fields['type'] ) {
				list( , $day )     = explode( ':', $fields['type'] );
				$type_costs['day'] = absint( $day );
			}

			if ( $type_costs ) {
				$costs[ $index ] = array( $fields['type'], $type_costs );
				$index ++;
			}
		}

		return $costs;
	}

	/**
	 * Returns a function name (for this class) that returns our time or date range
	 *
	 * @param  string $type rule type
	 * @return string       function name
	 */
	public static function get_type_function( $type ) {
		if ( 'time:range' === $type ) {
			return 'get_time_range_for_custom_date';
		}
		if ( 'custom:daterange' === $type ) {
			return 'get_time_range_for_custom_datetime';
		}
		// Skip expired rules by type (e.g., '%:expired').
		if ( false !== strpos( $type, ':expired' ) ) {
			return false;
		}

		return strrpos( $type, 'time:' ) === 0 ? 'get_time_range' : 'get_' . $type . '_range';
	}

	/**
	 * Process and return formatted availability rules
	 *
	 * @version 3.3.0
	 * @param   array  $rules Rules to process.
	 * @param   string $level Staff, Product or Globally.
	 * @param   bool  $hide_past Hide past rules not relevant in future.
	 * @param   WC_Product_Appointment $product
	 * @return  array
	 */
	public static function process_availability_rules( $rules, $level, $hide_past = true, $product = NULL ) {
		$formatted_rules = [];

		if ( empty( $rules ) ) {
			return $formatted_rules;
		}

		// Go through rules.
		foreach ( $rules as $order_on_product => $fields ) {
			if ( empty( $fields['appointable'] ) ) {
				continue;
			}

			// Do not include dates that are in the past.
			if ( $hide_past && ( in_array( $fields['type'], array( 'custom', 'time:range', 'custom:daterange' ), true ) ) ) {
				$to_date = ! empty( $fields['to_date'] ) ? $fields['to_date'] : $fields['to'];
				if ( strtotime( $to_date ) < strtotime( 'midnight -1 day' ) ) {
					continue;
				}
			}

			#error_log( var_export( $fields, true ) );

			// Skip expired rules by type (e.g., '%:expired').
			if ( isset( $fields['type'] ) && false !== strpos( $fields['type'], ':expired' ) ) {
				continue;
			}

			#error_log( var_export( $fields, true ) );

			// Apply padding to a Synced rule?
			$is_synced = ! empty( $fields['event_id'] );
			if ( $is_synced && $product && apply_filters( 'wc_appointments_sync_with_padding', false, $product ) ) {
				$padding_duration_in_minutes = $product->get_padding_duration_in_minutes();
				#error_log( var_export( $padding_duration, true ) );
				if ( $padding_duration_in_minutes && in_array( $product->get_duration_unit(), [ 'hour', 'minute', 'day' ] ) ) {
					// Check if all day event.
					$is_all_day = false === strpos( $fields['from'], ':' );
					#error_log( var_export( $is_all_day, true ) );

					// Set all time as a WC_DateTime object.
					if ( $is_all_day ) {
						// Get Start and end date information
						$dtstart = new WC_DateTime( $fields['from_date'] );
						$dtend   = new WC_DateTime( $fields['to_date'] );
					} else {
						// Get Start and end datetime information
						$dtstart = new WC_DateTime( $fields['from_date'] . ' ' . $fields['from'] );
						$dtend   = new WC_DateTime( $fields['to_date'] . ' ' . $fields['to'] );
					}

					// Adjust rules with padding.
					$dtstart->modify( "-{$padding_duration_in_minutes} minutes" );
					$dtend->modify( "+{$padding_duration_in_minutes} minutes" );

					#error_log( var_export( $dtstart, true ) );

					// Now make sure all dates and times are adjusted correctly based on type of rule.
					if ( 'rrule' === $fields['type'] ) {
						if ( $is_all_day ) {
							$fields['from'] = $dtstart->format( 'Y-m-d' );
							$fields['to']   = $dtend->format( 'Y-m-d' );
						} else {
							$fields['from'] = $dtstart->format( 'Y-m-d\TH:i:sP' );
							$fields['to']   = $dtend->format( 'Y-m-d\TH:i:sP' );
						}

					} elseif ( $is_all_day ) {
						$fields['from'] = $dtstart->format( 'Y-m-d' );
						$fields['to']   = $dtend->format( 'Y-m-d' );
					} else {
						$fields['from_date'] = $dtstart->format( 'Y-m-d' );
						$fields['to_date']   = $dtend->format( 'Y-m-d' );
						$fields['from'] = $dtstart->format( 'H:i' );
						$fields['to']   = $dtend->format( 'H:i' );
					}

					#error_log( var_export( $fields, true ) );
				}
			}

			$type_function = self::get_type_function( $fields['type'] );
			if ( ! $type_function ) {
				continue;
			}
			$appointable   = 'yes' === $fields['appointable'] ? true : false;
			if ( 'get_rrule_range' === $type_function ) {
				$type_availability = self::$type_function( $fields['from'], $fields['to'], $fields['rrule'], $appointable );
			} elseif ( in_array( $type_function, array( 'get_time_range_for_custom_date', 'get_time_range_for_custom_datetime' ) ) ) {
				$type_availability = self::$type_function( $fields['from_date'], $fields['to_date'], $fields['from'], $fields['to'], $appointable );
			} else {
				$type_availability = self::$type_function( $fields['from'], $fields['to'], $appointable );
			}

			$priority = intval( ( isset( $fields['priority'] ) ? $fields['priority'] : 10 ) );
			$priority = 0 === $priority || '' === $priority ? 10 : $priority; #disallow zero and empty as priority number.
			$qty      = intval( ( isset( $fields['qty'] ) ? absint( $fields['qty'] ) : 0 ) );

			// Ensure day gets specified for time: rules.
			if ( strrpos( $fields['type'], 'time:' ) === 0 && 'time:range' !== $fields['type'] ) {
				list( , $day )            = explode( ':', $fields['type'] );
				$type_availability['day'] = absint( $day );
			}

			if ( ! empty( $type_availability ) ) {
				$formatted_rule = array(
					'type'     => $fields['type'],
					'range'    => $type_availability,
					'priority' => $priority,
					'qty'      => $qty,
					'level'    => $level,
					'order'    => $order_on_product,
				);

				if ( isset( $fields['kind_id'] ) ) {
					$formatted_rule['kind_id'] = $fields['kind_id'];
				}

				$formatted_rules[] = $formatted_rule;
			}
		}

		return $formatted_rules;
	}

	/**
	 * Get the minutes that should be available based on the rules and the date to check.
	 *
	 * The minutes are returned in a range from the start incrementing minutes right up to the last available minute.
	 *
	 * This function expects the rules to be ordered in the sequence that is should be processed. Later rule minutes
	 * will override prior rule minutes in the order given.
	 *
	 * @since 3.1.1 moved from WC_Product_Appointment.
	 *
	 * @param array $rules
	 * @param int   $check_date
	 * @param array $appointable_minutes
	 *
	 * @return array $appointable_minutes
	 */
	public static function get_minutes_from_rules( $rules, $check_date, $appointable_minutes = [] ) {
		$staff_minutes = [];

		#print '<pre>'; print_r( $check_date ); print '</pre>';

		foreach ( $rules as $rule ) {
			// Something terribly wrong if a rule has no level.
			if ( ! isset( $rule['level'] ) ) {
				continue;
			}

			$data_for_rule = self::get_rule_minute_range( $rule, $check_date );

			#print '<pre>'; print_r( $rule ); print '</pre>';
			#print '<pre>'; print_r( $data_for_rule ); print '</pre>';

			// split up the rules on a staff level to be dealt with independently
			// after the rules loop. This ensure staff do not affect one another.
			if ( 'staff' === $rule['level'] ) {
				$staff_id         = $rule['kind_id'];
				$availability_key = $data_for_rule['is_appointable'] ? 'appointable' : 'not_appointable';
				// adding minutes in the order of the rules received, higher index higher override power.
				$staff_minutes[ $staff_id ][] = array( $availability_key => $data_for_rule['minutes'] );
				continue;
			}

			// At this point we assume all staff rules have been processed as they have a lower
			// override order in the $rules given.
			// Remove available staff minutes if being overridden at the product or global level.
			if ( ! self::check_timestamp_against_rule( $check_date, $rule, true ) ) {
				$staff_minutes = [];
			}

			// If this time range is appointable, add to appointable minutes.
			if ( $data_for_rule['is_appointable'] ) {
				$appointable_minutes = array_merge( $appointable_minutes, $data_for_rule['minutes'] );
				continue;
			}

			// Handle NON-staff removal of unavailable minutes.
			$appointable_minutes = array_diff( $appointable_minutes, $data_for_rule['minutes'] );

			// Handle staff specific removal of unavailable minutes.
			foreach ( $staff_minutes as $id => $minute_ranges ) {
				foreach ( $minute_ranges as $index => $minute_range ) {
					if ( ! isset( $minute_range['appointable'] ) || empty( $data_for_rule['minutes'] ) ) {
						continue;
					}
					// remove the last minute from the array for hours not to be thrown off
					// what happens is that this last minute could fall right at the beginning of the
					// next slot like 7:00 to 8:00 range the last minute will be on 8:00 which means
					// 8:00 will be removed, leaving the resulting range to start at 8:01.
					// Exempt for global rules that remove availability.
					if ( 'global' !== $rule['level'] && $data_for_rule['is_appointable'] ) {
						array_pop( $data_for_rule['minutes'] );
					}
					$staff_minutes[ $id ][ $index ]['appointable'] = array_diff( $minute_range['appointable'], $data_for_rule['minutes'] );
				}
			}
		}

		#print '<pre>'; print_r( $staff_minutes ); print '</pre>';

		// One staff should not override the other, when automatically assigned: as long as one is available.
		foreach ( $staff_minutes as $staff_id => $minutes_for_rule_order ) {
			$staff_minutes = [];

			foreach ( $minutes_for_rule_order as $rule_minutes_with_availability ) {
				$is_appointable = isset( $rule_minutes_with_availability['appointable'] );
				if ( $is_appointable ) {
					$staff_minutes = array_merge( $staff_minutes, $rule_minutes_with_availability['appointable'] );
				} else {
					$staff_minutes = array_diff( $staff_minutes, $rule_minutes_with_availability['not_appointable'] );
				}
			}

			$appointable_minutes = array_merge( $staff_minutes, $appointable_minutes );
		}

		$appointable_minutes = array_unique( array_values( $appointable_minutes ) );

		sort( $appointable_minutes );

		#print '<pre>'; print_r( $check_date ); print '</pre>';
		#print '<pre>'; print_r( $appointable_minutes ); print '</pre>';

		return $appointable_minutes;
	}

	/**
	 * This function is a mediator that simplifies the creation of
	 * a data object representing the range of rules minutes and the property of appointable or not.
	 *
	 * @since 3.5.6
	 *
	 * @param array $rule
	 * @param int   $check_date
	 *
	 * @return array $minute_range
	 */
	public static function get_rule_minute_range( $rule, $check_date ) {
		$minute_range = array(
			'is_appointable' => false,
			'minutes'        => [],
		);

		if ( ( strpos( $rule['type'], 'time' ) > -1 ) || ( 'custom:daterange' === $rule['type'] ) ) {
			$minute_range = self::get_rule_minutes_for_time( $rule, $check_date );
		} elseif ( 'days' === $rule['type'] ) {
			$minute_range = self::get_rule_minutes_for_days( $rule, $check_date );
		} elseif ( 'weeks' === $rule['type'] ) {
			$minute_range = self::get_rule_minutes_for_weeks( $rule, $check_date );
		} elseif ( 'months' === $rule['type'] ) {
			$minute_range = self::get_rule_minutes_for_months( $rule, $check_date );
		} elseif ( 'custom' === $rule['type'] ) {
			$minute_range = self::get_rule_minutes_for_custom( $rule, $check_date );
		} elseif ( 'rrule' === $rule['type'] ) {
			$minute_range = self::get_rule_minutes_for_rrule( $rule, $check_date );
		}

		return $minute_range;
	}

	/**
	 * Calculate minutes range.
	 *
	 * @since 4.4.0
	 * @param $from
	 * @param $to
	 *
	 * @return array
	 */
	protected static function calculate_minute_range( $from, $to ) {
		$from_hour = absint( date( 'H', strtotime( $from ) ) );
		$from_min  = absint( date( 'i', strtotime( $from ) ) );
		$to_hour   = absint( date( 'H', strtotime( $to ) ) );
		$to_min    = absint( date( 'i', strtotime( $to ) ) );

		// If "to" is set to midnight, it is safe to assume they mean the end of the day
		// php wraps 24 hours to "12AM the next day"
		if ( 0 === $to_hour && 0 === $to_min ) {
			$to_hour = 24;
		}

		$minute_range = array( ( $from_hour * 60 ) + $from_min, ( $to_hour * 60 ) + $to_min );
		$merge_ranges = array();
		$minutes      = array();

		// if first time in range is larger than second, we
		// assume they want to go over midnight
		if ( $minute_range[0] > $minute_range[1] ) {
			$merge_ranges[] = array( $minute_range[0], 1440 );
			// fix.
			$merge_ranges[] = array( $minute_range[0], ( 1440 + $minute_range[1] ) );
		} else {
			$merge_ranges[] = array( $minute_range[0], $minute_range[1] );
		}

		foreach ( $merge_ranges as $range ) {
			// Add ranges to minutes this rule affects.
			$minutes = array_merge( $minutes, range( $range[0], $range[1] ) );
		}

		return $minutes;
	}

	/**
	 * Get minutes from rules for a time rule type.
	 *
	 * @since 3.1.1
	 * @param         $rule
	 * @param integer $check_date
	 *
	 * @return array
	 */
	public static function get_rule_minutes_for_time( $rule, $check_date ) {
		$minutes = array(
			'is_appointable' => false,
			'minutes'        => [],
		);

		$type  = $rule['type'];
		$range = $rule['range'];

		$year        = absint( date( 'Y', $check_date ) );
		$month       = absint( date( 'n', $check_date ) );
		$day         = absint( date( 'j', $check_date ) );
		$day_of_week = absint( date( 'N', $check_date ) );

		#var_dump( $range );

		if ( in_array( $type, array( 'time:range', 'custom:daterange' ) ) ) { // type: date range with time
			if ( ! isset( $range[ $year ][ $month ][ $day ] ) ) {
				return $minutes;
			} else {
				$range = $range[ $year ][ $month ][ $day ];
			}

			$from                      = $range['from'];
			$to                        = $range['to'];
			$minutes['is_appointable'] = $range['rule'];
		} elseif ( strpos( $rule['type'], 'time:' ) > -1 ) { // type: single week day with time
			if ( $day_of_week != $range['day'] ) {
				return $minutes;
			}

			$from                      = $range['from'];
			$to                        = $range['to'];
			$minutes['is_appointable'] = $range['rule'];
		} else {  // type: time all week per day
			$from                      = $range['from'];
			$to                        = $range['to'];
			$minutes['is_appointable'] = $range['rule'];
		}

		$calculated_minutes_range = self::calculate_minute_range( $from, $to );

		/**
		 * Consider an example of a clinic which is open from 11:00am to 4:00pm which offers 30 minute slots
		 * and also 1:00pm to 2:00pm is already scheduled.
		 *
		 * 11:00am = 11*60 = 660 (opening time)
		 * 04:00pm = 16*60 = 960 (closing time)
		 * 01:00pm = 13*60 = 780 (start of an existing appointment)
		 * 02:00pm = 14*60 = 840 (end of an existing appointment)
		 *
		 * The output of array_diff removes 780 and 840 from the range [660-960] which causes this issue:
		 * We need the 780 and 840 to be a part of appointable time array.
		 * What the below fix does is set the unappointable range as 1:01pm - 1:59pm (781 - 839).
		 * This is only for unappointable range, when the is_appointable is set to `false`.
		 */
		$minutes['overlapping_start_time'] = false;
		$minutes['overlapping_end_time']   = false;

		/**
		 * The front end pages usually only display the appointable range, but the
		 * admin pages like Appointments > Calendar display the unappointable range as well.
		 * And, since we are removing the first and the last minute from the unappointable range,
		 * it will display the time incorrectly. This is why we will also return these 2 removed
		 * values `overlapping_start_time` and `overlapping_end_time` with this method so that
		 * the unappointable range can be displayed correctly.
		 *
		 * @todo Since there are multiple PRs opened to fix the related issues: #2798, #2649 and #3160,
		 * concurrently, this fix is done here. Once tested and merged, we might want to create a
		 * separate utility function which takes care of this.
		 */

		if ( ! $minutes['is_appointable'] ) {
			$minutes['overlapping_start_time'] = array_shift( $calculated_minutes_range );
			$minutes['overlapping_end_time']   = array_pop( $calculated_minutes_range );
		}

		$minutes['minutes'] = $calculated_minutes_range;

		return $minutes;
	}

	/**
	 * Get minutes from rules for a 'rrule' rule type.
	 *
	 * @since 1.13.0
	 * @param $rule
	 * @param integer $check_date
	 *
	 * @return array
	 */
	public static function get_rule_minutes_for_rrule( $rule, $check_date ) {
		$start       = new WC_DateTime( $rule['range']['from'] );
		$end         = new WC_DateTime( $rule['range']['to'] );
		$is_all_day  = false === strpos( $rule['range']['from'], ':' );
		$date_format = $is_all_day ? 'Y-m-d' : 'Y-m-d g:i A';

		$minutes = array(
			'is_appointable' => false,
			'minutes'        => array(),
		);

		#error_log( var_export( $rule, true ) );
		#error_log( var_export( date( 'Y-m-d H:i:s', microtime( true ) ), true ) );

		try {
			$rset = new RSet( $rule['range']['rrule'], $is_all_day ? $start->format( $date_format ) : $start );
		} catch ( Exception $e ) {
			return $minutes;
		}

		#error_log( var_export( $rset->isInfinite(), true ) );
		#error_log( var_export( $check_date, true ) );

		$duration = $start->diff( $end, true );

		$current_date  = ( new DateTime( '@' . $check_date ) )->modify( 'midnight' );
		$tomorrow_date = ( new DateTime( '@' . $check_date ) )->modify( 'tomorrow' );
		// Offset from the server's timezone, back to UTC.
		$current_date->modify( '-' . get_option( 'gmt_offset' ) . ' hours' );
		$tomorrow_date->modify( '-' . get_option( 'gmt_offset' ) . ' hours' );

		// Use a limit since this reoccurrence can potentially be infinite.
		$occurrences = $rset->getOccurrencesBetween( $current_date, $tomorrow_date );

		foreach ( $occurrences as $occurrence ) {
			if ( date( 'Y-m-d', $check_date ) !== $occurrence->format( 'Y-m-d' ) ) {
				continue;
			}

			// Format to remove timezone since it's already been offseted to the server's timezone.
			$from = $occurrence->format( 'H:i' );
			$to   = $occurrence->add( $duration )->format( 'H:i' );

			$minute_range = self::calculate_minute_range( $from, $to );

			/*
			 * Remove 1 min from start and 1 min from end of the unappointable range to keep those minutes appointable.
			 * Detailed information on this can be found in `get_rule_minutes_for_time` method.
			 */
			$minutes['overlapping_start_time'] = false;
			$minutes['overlapping_end_time']   = false;
			if ( ! $minutes['is_appointable'] ) {
				$minutes['overlapping_start_time'] = array_shift( $minute_range );
				$minutes['overlapping_end_time']   = array_pop( $minute_range );
			}

			$minutes['minutes'] = array_merge( $minutes['minutes'], $minute_range );
		}

		#error_log( var_export( date( 'Y-m-d H:i:s', microtime( true ) ), true ) );
		#error_log( var_export( $minutes, true ) );

		return $minutes;
	}

	/**
	 * Get minutes from rules for days rule type.
	 *
	 * @since 3.1.1
	 * @param $rule
	 * @param integer $check_date
	 *
	 * @return array
	 */
	public static function get_rule_minutes_for_days( $rule, $check_date ) {
		$_rules         = $rule['range'];
		$minutes        = [];
		$is_appointable = false;
		$day_of_week    = intval( date( 'N', $check_date ) );

		if ( isset( $_rules[ $day_of_week ] ) ) {
			$minutes        = range( 0, 1440 );
			$is_appointable = $_rules[ $day_of_week ];
		}

		return array(
			'is_appointable' => $is_appointable,
			'minutes'        => $minutes,
		);
	}

	/**
	 * Get minutes from rules for a weeks rule type.
	 *
	 * @since 3.1.1
	 * @param $rule
	 * @param integer $check_date
	 *
	 * @return array
	 */
	public static function get_rule_minutes_for_weeks( $rule, $check_date ) {

		$range          = $rule['range'];
		$week_number    = intval( date( 'W', $check_date ) );
		$minutes        = [];
		$is_appointable = false;

		if ( isset( $range[ $week_number ] ) ) {
			$minutes        = range( 0, 1440 );
			$is_appointable = $range[ $week_number ];
		}

		return array(
			'is_appointable' => $is_appointable,
			'minutes'        => $minutes,
		);
	}

	/**
	 * Get minutes from rules for a months rule type.
	 *
	 * @since 3.1.1
	 * @param $rule
	 * @param integer $check_date
	 *
	 * @return array
	 */
	public static function get_rule_minutes_for_months( $rule, $check_date ) {

		$range          = $rule['range'];
		$month          = date( 'n', $check_date );
		$minutes        = [];
		$is_appointable = false;
		if ( isset( $range[ $month ] ) ) {
			$minutes        = range( 0, 1440 );
			$is_appointable = $range[ $month ];
		}

		return array(
			'is_appointable' => $is_appointable,
			'minutes'        => $minutes,
		);
	}

	/**
	 * Get minutes from rules for custom rule type.
	 *
	 * @since 3.1.1
	 * @param $rule
	 * @param integer $check_date
	 *
	 * @return array
	 */
	public static function get_rule_minutes_for_custom( $rule, $check_date ) {

		$range = $rule['range'];
		$year  = date( 'Y', $check_date );
		$month = date( 'n', $check_date );
		$day   = date( 'j', $check_date );

		$minutes        = [];
		$is_appointable = false;

		if ( isset( $range[ $year ][ $month ][ $day ] ) ) {
			$minutes        = range( 0, 1440 );
			$is_appointable = $range[ $year ][ $month ][ $day ];
		}

		return array(
			'is_appointable' => $is_appointable,
			'minutes'        => $minutes,
		);
	}

	/**
	 * Sort rules in order of precedence.
	 *
	 * @version 3.1.1 sort order reversed
	 * The order produced will be from the lowest to the highest.
	 * The elements with higher indexes overrides those with lower indexes e.g. `4` overrides `3`
	 * Index corresponds to override power. The higher the element index the higher the override power
	 *
	 * Level    : `global` > `product` > `product` (greater in terms off override power)
	 * Priority : within a level
	 * Order    : Within a priority The lower the order index higher the override power.
	 *
	 * @param array $rule1
	 * @param array $rule2
	 *
	 * @return integer
	 */
	public static function sort_rules_callback( $rule1, $rule2 ) {
		$level_weight = apply_filters(
			'wc_availability_rules_priority',
			array(
				'staff'   => 5,
				'product' => 3,
				'global'  => 1,
			)
		);

		// The override power goes from the outside inward.
		// Priority is outside which means it has the most weight when sorting.
		// Then level(global, product, staff)
		// Lastly order is applied within the level.
		if ( $rule1['priority'] === $rule2['priority'] ) {
			if ( $level_weight[ $rule1['level'] ] === $level_weight[ $rule2['level'] ] ) {
				// if `order index of 1` < `order index of 2` $rule1 one has a higher override power. So we
				// increase the index for $rule1 which corresponds to override power.
				return ( $rule1['order'] < $rule2['order'] ) ? 1 : -1;
			}

			// if `level of 1` < `level of 2` $rule1 must have lower override power. So we
			// decrease the index for 1 which corresponds to override power.
			return $level_weight[ $rule1['level'] ] < $level_weight[ $rule2['level'] ] ? -1 : 1;
		}

		// if `priority of 1` < `priority of 2` $rule1 must have lower override power. So we
		// decrease the index for 1 which corresponds to override power.
		return $rule1['priority'] < $rule2['priority'] ? 1 : -1;
	}

	/**
	 * Filter out all but time rules.
	 *
	 * @param  array $rule
	 * @return boolean
	 */
	private static function filter_time_rules( $rule ) {
		return ! empty( $rule['type'] ) && ! in_array( $rule['type'], [ 'days', 'custom', 'months', 'weeks' ] );
	}

	/**
	 * Check a appointable product's availability rules against a time range and return if appointable or not.
	 *
	 * @param  WC_Product_Appointment $appointable_product
	 * @param  int                    $start timestamp
	 * @param  int                    $end timestamp
	 * @param  int                    $staff_id
	 * @return boolean
	 */
	public static function check_range_availability_rules( $appointable_product, $start, $end, $staff_id = null ) {
		// This is a time range.
		if ( in_array( $appointable_product->get_duration_unit(), [ 'minute', 'hour' ] ) ) {
			return self::check_availability_rules_against_time( $appointable_product, $start, $end, $staff_id );
		} else {  // Else this is a date range (days).
			$timestamp = $start;

			while ( $timestamp < $end ) {
				if ( ! self::check_availability_rules_against_date( $appointable_product, $timestamp, $staff_id ) ) {
					return false;
				}
				if ( 'start' === $appointable_product->get_availability_span() ) {
					break; // Only need to check first day.
				}
				$timestamp = strtotime( '+1 day', $timestamp );
			}
		}

		return true;
	}

	/**
	 * Check a time against the time specific availability rules
	 *
	 * @param integer                $slot_start_time
	 * @param integer                $slot_end_time
	 * @param integer                $staff_id
	 * @param WC_Product_Appointment $appointable_product
	 * @param bool|null If not null, it will default to the boolean value. If null, it will use product default availability.
	 *
	 * @return bool available or not
	 */
	public static function check_availability_rules_against_time( $appointable_product, $slot_start_time, $slot_end_time, $staff_id, $get_capacity = false, $appointable = null ) {
	    // Rules.
	    $rules = $appointable_product->get_availability_rules( $staff_id );

	    if ( is_null( $appointable ) ) {
	        $appointable = $appointable_product->get_default_availability();
	    }

	    if ( empty( $rules ) ) {
	        return $appointable;
	    }

	    $slot_start_time = is_numeric( $slot_start_time ) ? $slot_start_time : strtotime( $slot_start_time );
	    $slot_end_time   = is_numeric( $slot_end_time ) ? $slot_end_time : strtotime( $slot_end_time );

	    #print '<pre>'; print_r( date( 'Y-m-d H:i', $slot_start_time ) . '__' . date( 'Y-m-d H:i', $slot_end_time ) ); print '</pre>';

	    #print '<pre>'; print_r( $rules ); print '</pre>';

	    // Capacity.
	    $individual = ! $staff_id || is_array( $staff_id ) ? false : true;
	    $capacity   = $appointable_product->get_available_qty( $staff_id, false, $individual ); #ID, fallback, individual

	    #print '<pre>'; print_r( $staff_id . '__' . $capacity ); print '</pre>';

	    // Get the date values for the slots being checked.
	    $slot_year      = intval( date( 'Y', $slot_start_time ) );
	    $slot_month     = intval( date( 'n', $slot_start_time ) );
	    $slot_date      = intval( date( 'j', $slot_start_time ) );
	    $slot_date_next = intval( date( 'j', strtotime( '+ 1 day', $slot_start_time ) ) );
	    $slot_day_no    = intval( date( 'N', $slot_start_time ) );
	    $slot_week      = intval( date( 'W', $slot_start_time ) );

	    // Slot next day.
	    $slot_next_day = false;

	    // Slot next day.
	    $rule_next_day = false;

	    // default from and to for the whole day.
	    $from = strtotime( 'midnight', $slot_start_time );
	    $to   = strtotime( 'midnight + 1 day', $slot_start_time );

	    #print '<pre>'; print_r( $rules ); print '</pre>';

	    foreach ( $rules as $rule ) {
	        $type  = $rule['type'];
	        $range = $rule['range'];
	        $qty   = $rule['qty'] && $rule['qty'] >= 1 ? absint( $rule['qty'] ) : $capacity;

	        #print '<pre>'; print_r( $qty ); print '</pre>';
	        #print '<pre>'; print_r( $range ); print '</pre>';
	        #print '<pre>'; print_r( $capacity ); print '</pre>';

			// Skip expired rules.
			if ( substr( $type, -8 ) === ':expired' ) {
				continue;
			}

	        if ( 'rrule' === $type ) {
	            if ( self::rrule_has_occurrences_between_slots( $range, $slot_start_time + 1, $slot_end_time - 1 ) ) {
					#print '<pre>'; print_r( $range ); print '</pre>';
					$appointable = $range['rule']; // For google calendar events bookable value will be always "no".
					continue;
					#return $range['rule'];
				} elseif ( self::rrule_matches_timestamp( $range, $slot_start_time + 1 ) || self::rrule_matches_timestamp( $range, $slot_end_time - 1 ) ) {
	                #print '<pre>'; print_r( $range ); print '</pre>';
	                $appointable = $range['rule'];
	                continue;
	                #return $range['rule'];
	            } else {
	                continue;
	            }
	        }

	        // Handling NON-time specific rules first.
	        if ( in_array( $type, array( 'days', 'custom', 'months', 'weeks' ) ) ) {
	            if ( 'days' === $type ) {
	                if ( ! isset( $range[ $slot_day_no ] ) ) {
	                    continue;
	                }
	            } elseif ( 'custom' === $type ) {
	                if ( ! isset( $range[ $slot_year ][ $slot_month ][ $slot_date ] ) ) {
	                    continue;
	                }
	            } elseif ( 'months' === $type ) {
	                if ( ! isset( $range[ $slot_month ] ) ) {
	                    continue;
	                }
	            } elseif ( 'weeks' === $type ) {
	                if ( ! isset( $range[ $slot_week ] ) ) {
	                    continue;
	                }
	            }

	            $rule_val = self::check_timestamp_against_rule( $slot_start_time, $rule, $appointable_product->get_default_availability(), $capacity );
	            $from     = '00:00'; // start of day
	            $to       = '00:00'; // end of day
	            $capacity = self::check_timestamp_against_rule( $slot_start_time, $rule, $appointable_product->get_default_availability(), $capacity, ( $get_capacity ? true : false ) );
	        }

	        // Handling all time specific rules
	        $apply_rule_times = false;
	        if ( in_array( $type, array( 'time:range' ) ) ) {
	            if ( ! isset( $range[ $slot_year ][ $slot_month ][ $slot_date ] ) ) {
	                continue;
	            }
	            $time_range_rule  = $range[ $slot_year ][ $slot_month ][ $slot_date ];
	            $rule_val         = $time_range_rule['rule'];
	            $from             = $time_range_rule['from'];
	            $to               = $time_range_rule['to'];
	            $apply_rule_times = true;
	        } elseif ( in_array( $type, array( 'custom:daterange' ) ) && ! isset( $range[ $slot_year ][ $slot_month ][ $slot_date_next ] ) ) {
	            if ( ! isset( $range[ $slot_year ][ $slot_month ][ $slot_date ] ) ) {
	                continue;
	            }
	            $time_range_rule  = $range[ $slot_year ][ $slot_month ][ $slot_date ];
	            $rule_val         = $time_range_rule['rule'];
	            $from             = $time_range_rule['from'];
	            $to               = $time_range_rule['to'];
	            $apply_rule_times = true;
	        } elseif ( in_array( $type, array( 'custom:daterange' ) ) && isset( $range[ $slot_year ][ $slot_month ][ $slot_date_next ] ) ) {
	            if ( ! isset( $range[ $slot_year ][ $slot_month ][ $slot_date ] ) ) {
	                continue;
	            }
	            $time_range_rule_prev = $range[ $slot_year ][ $slot_month ][ $slot_date ];
	            $time_range_rule_next = $range[ $slot_year ][ $slot_month ][ $slot_date_next ];
	            $rule_val             = $time_range_rule_prev['rule'];
	            $from                 = $time_range_rule_prev['from'];
	            $to                   = $time_range_rule_next['to'];
	            $apply_rule_times     = true;
	            $rule_next_day        = true;
	        } elseif ( false !== strpos( $type, 'time' ) ) {

	            #print '<pre>'; print_r( $slot_day_no ); print '</pre>';
	            #print '<pre>'; print_r( $range['day'] ); print '</pre>';

	            // Add next day to rule if "to" rule is before "from".
	            if ( ! empty( $range['day'] ) ) {
	                $prev_day = ( $slot_day_no - 1 ) === 0 ? 7 : ( $slot_day_no - 1 );
	                if ( ( $range['to'] < $range['from'] ) && ( $range['day'] === $prev_day ) ) {
	                    $slot_next_day = $range['day'];
	                } else {
	                    $slot_next_day = false;
	                }
	            }

	            #print '<pre>'; print_r( 'ruleFROM: ' . $range['from'] . ' ruleTO: ' . $range['to'] . 'ruleday: ' . $range['day'] . '____' . 'calday: ' . $slot_day_no . ' nextday: ' . $slot_next_day ); print '</pre>';

	            // if the day doesn't match and the day is not zero skip the rule
	            // zero means all days. So rule only applies for zero or a matching day.
	            if ( ! empty( $range['day'] ) && $slot_day_no != $range['day'] && $slot_next_day != $range['day'] ) {
	                #print '<pre>'; print_r( 'ruleday2: ' . $range['day'] . ' calday2: ' . $slot_day_no . ' slot_next_day: ' . $slot_next_day ); print '</pre>';
	                continue;
	            }

	            // check that the rule should be applied to the current slot
	            // if not time it must be time:day_number
	            if ( 'time' !== $type ) {
	                if ( ! strpos( $type, (string) $slot_day_no ) && ! strpos( $type, (string) $slot_next_day ) ) {
	                    #print '<pre>'; print_r( 'rultype3: ' . $type . ' calday3: ' . $slot_day_no . ' slot_next_day: ' . $slot_next_day ); print '</pre>';
	                    continue;
	                }
	            }

	            $rule_val         = $range['rule'];
	            $from             = $range['from'];
	            $to               = $range['to'];
	            $apply_rule_times = true;
	        }

	        #print '<pre>'; print_r( $from .'__'.$to ); print '</pre>';
	        #print '<pre>'; print_r( $rule_next_day ); print '</pre>';

	        $rule_start_time = $apply_rule_times ? strtotime( $from, $slot_start_time ) : $slot_start_time;
	        $rule_end_time   = $apply_rule_times ? strtotime( $to, $slot_start_time ) : $slot_start_time;
	        if ( $rule_next_day ) {
	            $rule_end_time = strtotime( '+ 1 day', $rule_end_time );
	        }

	        // 24/7 availability.
	        if ( $rule_start_time === $rule_end_time ) {
	            #print '<pre>'; print_r( 'x0' ); print '</pre>';
	            $appointable = $rule_val;
	            $capacity    = $qty;
	            continue;
	        }

	        /*
	        // When end date is before start.
	        if ( $to <= $from ) {
	            $slot_start_time = strtotime( '-1 day', $slot_start_time );
	        }
	        */

	        #print '<pre>'; print_r( date( 'ymd H:i', $rule_start_time ) .'__'.date( 'ymd H:i', $rule_end_time ) ); print '</pre>';

	        // Make sure next day time rule converts correctly.
	        if ( ! in_array( $type, array( 'days', 'custom', 'months', 'weeks' ) ) && ( $rule_end_time <= $rule_start_time ) ) {
	            if ( ! $slot_next_day && ( $rule_start_time <= $slot_start_time ) ) {
	                #print '<pre>'; print_r( 'rule1 end +1 day' ); print '</pre>';
	                // When slot start larger than end on the next day, set rule end
	                // on the next day.
	                $rule_end_time = strtotime( '+1 day', $rule_end_time );
	            } elseif ( $slot_next_day && ( $rule_start_time > $slot_start_time ) ) {
	                #print '<pre>'; print_r( 'rule2 start -1 day' ); print '</pre>';
	                // When slot start is larger than end on the same day, set rule start
	                // on the day before.
	                $rule_start_time = strtotime( '-1 day', $rule_start_time );
	            } elseif ( ! $slot_next_day && ( $rule_start_time > $slot_start_time ) ) {
	                #print '<pre>'; print_r( 'rule3 start +1 day' ); print '</pre>';
	                // When slot start is larger than end on the same day, set rule start
	                // on the day before.
	                $rule_start_time = strtotime( '-1 day', $rule_end_time );
	            }

	            if ( $slot_next_day && ( date( 'ymd', $slot_start_time ) === date( 'ymd', $slot_end_time ) ) ) {
	                #print '<pre>'; print_r( 'rule4 start -1 day' ); print '</pre>';
	                // When slot start is larger than end on the same day, set rule start
	                // on the day before.
	                $rule_start_time = strtotime( '-1 day', $rule_start_time );
	            }
	        }

	        #print '<pre>'; print_r( $range['day'] ); print '</pre>';
	        #print '<pre>'; print_r( $prev_day ); print '</pre>';
	        #print '<pre>'; print_r( $slot_next_day ); print '</pre>';
	        #print '<pre>'; print_r( date( 'ymd H:i', $rule_start_time ) .'__'.date( 'ymd H:i', $rule_end_time ) .'_||_'. date( 'ymd H:i', $slot_start_time ) .'__'.date( 'ymd H:i', $slot_end_time ) .'=='. $rule['qty'] .'_??_'. $type ); print '</pre>';

	        // Reverse date/day rule.
	        if ( in_array( $type, array( 'days', 'custom', 'months', 'weeks' ) ) && ( $rule_end_time <= $rule_start_time ) ) {
	            if ( $slot_end_time > $rule_start_time ) {
	                #print '<pre>'; print_r( 'x1' . '__' . date( 'ymd H:i', $slot_start_time ) ); print '</pre>';
	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;
	            }
	            if ( $slot_start_time >= $rule_start_time && $slot_end_time >= $rule_end_time ) {
	                #print '<pre>'; print_r( 'x2' . '__' . date( 'ymd H:i', $slot_start_time ) ); print '</pre>';
	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;
	            }
	            // does this rule apply?
	            // does slot start before rule start and end after rules start time {goes over start time}
	            if ( $slot_start_time < $rule_start_time && $slot_end_time > $rule_start_time ) {
	                #print '<pre>'; print_r( 'x3' . '__' . date( 'ymd H:i', $slot_start_time ) ); print '</pre>';
	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;
	            }
	        } else {
	            // Normal rule.
	            if ( $slot_start_time >= $rule_start_time && $slot_end_time <= $rule_end_time ) {
	            #if ( $slot_start_time < $rule_end_time && $slot_end_time > $rule_start_time ) {
	                #print '<pre>'; print_r( 'x4' . '__' . date( 'ymd H:i', $rule_start_time ) .'__'.date( 'ymd H:i', $rule_end_time ) .'_||_'. date( 'ymd H:i', $slot_start_time ) .'__'.date( 'ymd H:i', $slot_end_time ) .'=='. $rule_val ); print '</pre>';
	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;
	            }

	            // Specific to hour duration types. If start time is in between
	            // rule start and end times the rule should be applied.
	            if ( 'hour' === $appointable_product->get_duration_unit()
	                && $slot_start_time > $rule_start_time
	                && $slot_start_time < $rule_end_time
	                && $slot_end_time > $rule_start_time
	                && $slot_end_time < $rule_end_time ) {

	                    #print '<pre>'; print_r( 'x5' . '__' . date( 'ymd H:i', $rule_start_time ) .'__'.date( 'ymd H:i', $rule_end_time ) .'_||_'. date( 'ymd H:i', $slot_start_time ) .'__'.date( 'ymd H:i', $slot_end_time ) .'=='. $rule_val ); print '</pre>';

	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;

	            }

	            // If slot drops into any of the unavailable rules
	            // make sure to include this rule as well.
	            if ( ! $rule_val
	                && $slot_start_time >= $rule_start_time
	                && $slot_start_time < $rule_end_time ) {

	                    #print '<pre>'; print_r( 'x7' . '__' . date( 'ymd H:i', $rule_start_time ) .'__'.date( 'ymd H:i', $rule_end_time ) .'_||_'. date( 'ymd H:i', $slot_start_time ) .'__'.date( 'ymd H:i', $slot_end_time ) .'=='. $rule_val ); print '</pre>';

	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;

	            }
	            if ( ! $rule_val
	                && $slot_end_time > $rule_start_time
	                && $slot_end_time <= $rule_end_time ) {

	                    #print '<pre>'; print_r( 'x8' . '__' . date( 'Y-m-d H:i', $slot_start_time ) . '__' . date( 'Y-m-d H:i', $slot_end_time ) . ' ruleval=' .$rule_val ); print '</pre>';

	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;

	            }
	            if ( ! $rule_val
	                && $slot_start_time <= $rule_start_time
	                && $slot_end_time >= $rule_end_time ) {

	                    #print '<pre>'; print_r( 'x6' . '__' . date( 'Y-m-d H:i', $slot_start_time ) . '__' . date( 'Y-m-d H:i', $slot_end_time ) . ' ruleval=' .$rule_val ); print '</pre>';

	                $appointable = $rule_val;
	                $capacity    = $qty;
	                continue;

	            }
	        }
	    }

	    #print '<pre>'; print_r( $staff_id . ' ... ' . date( 'Y-m-d H:i', $slot_start_time ) . '__' . date( 'Y-m-d H:i', $slot_end_time ) . ' == ' . absint( $capacity ) ); print '</pre>';
	    #print '<pre>'; print_r( date( 'Y-m-d H:i', $slot_start_time ) . '__' . date( 'Y-m-d H:i', $slot_end_time ) . '__' . $appointable ); print '</pre>';
	    #print '<pre>'; print_r( $staff_id . ' ... ' . date( 'Y-m-d H:i', $slot_start_time ) . '__GC:' . $get_capacity . '__C:' . $capacity . '_==_' . $appointable ); print '</pre>';

	    // Return rule type capacity.
	    if ( $get_capacity ) {
	        return $appointable ? absint( $capacity ) : 0;
	    }

	    return $appointable;
	}

	/**
	 * Check a date against the availability rules
	 *
	 * @version 4.0.0  Added woocommerce_appointments_is_date_appointable filter hook
	 * @version 2.7    Moved to this class from WC_Product_Appointment
	 *                 only apply rules if within their scope
	 *                 keep appointment value alive within the loop to ensure the next rule with higher power can override.
	 * @version 2.6    Removed all calls to break 2 to ensure we get to the highest
	 *                 priority rules, otherwise higher order/priority rules will not
	 *                 override lower ones and the function exit with the wrong value.
	 *
	 * @param  WC_Product_Appointment $appointable_product
	 * @param  int                    $staff_id
	 * @param  int                    $check_date timestamp
	 * @return bool available or not
	 */
	public static function check_availability_rules_against_date( $appointable_product, $check_date, $staff_id = 0, $get_capacity = false, $appointable = null ) {
		if ( is_null( $appointable ) ) {
			$appointable = $appointable_product->get_default_availability();
		}

		// Rules.
		$rules = $appointable_product->get_availability_rules( $staff_id );

		// Capacity.
		$capacity = $appointable_product->get_available_qty( $staff_id );

		#var_dump($capacity);

		foreach ( $rules as $rule ) {
			if ( self::does_rule_apply( $rule, $check_date ) ) {
				// passing $appointable into the next check as it overrides the previous value
				$appointable = self::check_timestamp_against_rule( $check_date, $rule, $appointable, $capacity, $get_capacity );
			}
		}

		/**
		 * Is date appointable hook.
		 *
		 * Filter allows for overriding whether or not date is appointable. Filters should return true
		 * if appointable or false if not.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $appointable available or not
		 * @param WC_Product_Appointment $appointable_product
		 * @param int $staff_id
		 * @param int $check_date timestamp
		 */
		return apply_filters( 'woocommerce_appointments_is_date_appointable', $appointable, $appointable_product, $check_date, $staff_id, $get_capacity );
	}

	/**
	 * Does the time stamp fall within the scope of the rule?
	 *
	 * @param $rule
	 * @param $timestamp
	 * @return bool
	 */
	public static function does_rule_apply( $rule, $timestamp ) {
		$year        = intval( date( 'Y', $timestamp ) );
		$month       = intval( date( 'n', $timestamp ) );
		$day         = intval( date( 'j', $timestamp ) );
		$day_of_week = intval( date( 'N', $timestamp ) );
		$week        = intval( date( 'W', $timestamp ) );

		$range = $rule['range'];

		switch ( $rule['type'] ) {
			case 'months':
				if ( isset( $range[ $month ] ) ) {
					return true;
				}
				break;
			case 'weeks':
				if ( isset( $range[ $week ] ) ) {
					return true;
				}
				break;
			case 'days':
				if ( isset( $range[ $day_of_week ] ) ) {
					return true;
				}
				break;
			case 'custom':
				if ( isset( $range[ $year ][ $month ][ $day ] ) ) {
					return true;
				}
				break;
			case 'rrule':
				if ( self::rrule_matches_timestamp( $range, $timestamp ) ) {
					return true;
				}
				break;
			case 'time':
			case 'time:1':
			case 'time:2':
			case 'time:3':
			case 'time:4':
			case 'time:5':
			case 'time:6':
			case 'time:7':
				if ( $day_of_week === $range['day'] || 0 === $range['day'] ) {
					return true;
				}
				break;
			case 'custom:daterange':
			case 'time:range':
				if ( isset( $range[ $year ][ $month ][ $day ] ) ) {
					return true;
				}
				break;
		}

		return false;
	}

	/**
	 * Given a timestamp and rule check to see if the time stamp is appointable based on the rule.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $timestamp
	 * @param array   $rule
	 * @param boolean $default
	 * @return boolean
	 */
	public static function check_timestamp_against_rule( $timestamp, $rule, $default, $capacity = 1, $get_capacity = false ) {
		$year        = intval( date( 'Y', $timestamp ) );
		$month       = intval( date( 'n', $timestamp ) );
		$day         = intval( date( 'j', $timestamp ) );
		$day_of_week = intval( date( 'N', $timestamp ) );
		$week        = intval( date( 'W', $timestamp ) );

		$type  = $rule['type'];
		$range = $rule['range'];
		$qty   = $rule['qty'] && $rule['qty'] >= 1 ? $rule['qty'] : $capacity;

		$appointable = $default;

		switch ( $type ) {
			case 'months':
				if ( isset( $range[ $month ] ) ) {
					$appointable = $range[ $month ];
					$capacity    = $qty;
				}
				break;
			case 'weeks':
				if ( isset( $range[ $week ] ) ) {
					$appointable = $range[ $week ];
					$capacity    = $qty;
				}
				break;
			case 'days':
				if ( isset( $range[ $day_of_week ] ) ) {
					$appointable = $range[ $day_of_week ];
					$capacity    = $qty;
				}
				break;
			case 'custom':
				if ( isset( $range[ $year ][ $month ][ $day ] ) ) {
					$appointable = $range[ $year ][ $month ][ $day ];
					// Maybe skip since time:range applies it for this rule.
					$capacity = $qty;
				}
				break;
			case 'rrule':
				if ( self::rrule_matches_timestamp( $range, $timestamp ) ) {
					#print '<pre>'; print_r( $range ); print '</pre>';
					$appointable = $range['rule'];
					$capacity    = $qty;
					#continue;
					#return $range['rule'];
				}
				break;
			case 'time':
			case 'time:1':
			case 'time:2':
			case 'time:3':
			case 'time:4':
			case 'time:5':
			case 'time:6':
			case 'time:7':
				if ( false === $default && ( $day_of_week === $range['day'] || 0 === $range['day'] ) ) {
					$appointable = $range['rule'];
					$capacity    = $qty;
				}
				break;
			case 'time:range':
			case 'custom:daterange':
				if ( false === $default && ( isset( $range[ $year ][ $month ][ $day ] ) ) ) {
					$appointable = $range[ $year ][ $month ][ $day ]['rule'];
					$capacity    = $qty;
				}
				break;
		}

		#var_dump( date( 'Y-m-d H:i', $timestamp ) . '__' . $appointable );
		#var_dump( date( 'Y-m-d H:i', $timestamp ) . '__' . $get_capacity . '__' . $capacity );

 		// Return rule type capacity.
 		if ( $get_capacity ) {
 			return absint( $capacity );
 		}

		return $appointable;
	}

	/**
	 * Checks if the given rrule and event happens at the given timestamp.
	 *
	 * @param array $range Range and rrule to check.
	 * @param int   $timestamp Timestamp to check against.
	 *
	 * @return bool
	 */
	 private static function rrule_matches_timestamp( $range, $timestamp ) {
 		// This function is normally called twice with the same parameters so let's cache the result.
 		static $cache = array();

 		// This function will be called with the same rrules but different timestamps, so cache the rrule object and duration here.
 		static $rrule_cache = array();

 		// Cache expensive shared objects per request.
 		static $tz_gmt = null;
 		static $gmt_offset_opt = null;
 		static $gmt_offset_interval = null;

 		$rrule_cache_key = $range['from'] . ':' . $range['to'] . ':' . $range['rrule'];
 		$cache_key       = $rrule_cache_key . ':' . $timestamp;

 		if ( isset( $cache[ $cache_key ] ) ) {
 			return $cache[ $cache_key ];
 		}

 		try {
 			// Init static shared pieces.
 			if ( null === $tz_gmt ) {
 				$tz_gmt = new DateTimeZone( 'GMT' );
 			}
 			if ( null === $gmt_offset_opt ) {
 				$gmt_offset_opt = (float) get_option( 'gmt_offset' );
 				// Build DateInterval from float offset.
 				$abs_off   = abs( $gmt_offset_opt );
 				$hours     = (int) floor( $abs_off );
 				$minutes   = (int) round( ( $abs_off - $hours ) * 60 );
 				$gmt_offset_interval = new DateInterval( sprintf( 'PT%dH%dM', $hours, $minutes ) );
 				if ( $gmt_offset_opt < 0 ) {
 					$gmt_offset_interval->invert = 1;
 				}
 			}

 			// Prepare current timestamp DateTimeImmutable (UTC).
 			$datetime = new DateTimeImmutable( '@' . (int) $timestamp );

 			if ( ! isset( $rrule_cache[ $rrule_cache_key ] ) ) {
 				$is_all_day = ( false === strpos( $range['from'], ':' ) );

 				// Normalize start/end to GMT and apply site offset using immutable objects.
 				$start = ( new DateTimeImmutable( $range['from'] ) )
 					->setTimezone( $tz_gmt )
 					->add( $gmt_offset_interval );

 				$end = ( new DateTimeImmutable( $range['to'] ) )
 					->setTimezone( $tz_gmt )
 					->add( $gmt_offset_interval );

 				// Cache duration as integer seconds for faster math/comparisons.
 				$duration_secs = $end->getTimestamp() - $start->getTimestamp();
 				if ( $duration_secs < 0 ) {
 					$duration_secs = -$duration_secs;
 				}

 				$rrule = new RSet( $range['rrule'], $is_all_day ? $start->format( 'Y-m-d' ) : $start );

 				$rrule_cache[ $rrule_cache_key ] = array(
 					'rrule_object'   => $rrule,
 					'duration_secs'  => $duration_secs,
 				);
 			}

 			$rrule         = $rrule_cache[ $rrule_cache_key ]['rrule_object'];
 			$duration_secs = $rrule_cache[ $rrule_cache_key ]['duration_secs'];

 			// If duration is zero, only exact matches count.
 			if ( 0 === $duration_secs ) {
 				// Prefer fast path if library supports occursAt.
 				if ( method_exists( $rrule, 'occursAt' ) ) {
 					$result = (bool) $rrule->occursAt( $datetime );
 					$cache[ $cache_key ] = $result;
 					return $result;
 				}
 				// Fallback: bounded search in a 1-minute window.
 				$window_start = $datetime->sub( new DateInterval( 'PT1M' ) );
 				$window_end   = $datetime->add( new DateInterval( 'PT1M' ) );
 				if ( method_exists( $rrule, 'getOccurrencesBetween' ) ) {
 					$occurrences = $rrule->getOccurrencesBetween( $window_start, $window_end, true );
 					foreach ( $occurrences as $occurrence ) {
 						if ( (int) $occurrence->getTimestamp() === (int) $timestamp ) {
 							$cache[ $cache_key ] = true;
 							return true;
 						}
 					}
 					$cache[ $cache_key ] = false;
 					return false;
 				}
 				// Last resort: iterate but break early.
 				foreach ( $rrule as $occurrence ) {
 					$occ_ts = (int) $occurrence->getTimestamp();
 					if ( $occ_ts === (int) $timestamp ) {
 						$cache[ $cache_key ] = true;
 						return true;
 					}
 					if ( $occ_ts > (int) $timestamp ) {
 						break;
 					}
 				}
 				$cache[ $cache_key ] = false;
 				return false;
 			}

 			// For non-zero duration, look for occurrences that start within [timestamp - duration, timestamp].
 			$window_start_ts = (int) $timestamp - (int) $duration_secs;
 			$window_start    = new DateTimeImmutable( '@' . $window_start_ts );
 			$window_end      = $datetime;

 			if ( method_exists( $rrule, 'getOccurrencesBetween' ) ) {
 				$occurrences = $rrule->getOccurrencesBetween( $window_start, $window_end, true );
 				foreach ( $occurrences as $occurrence ) {
 					$occ_ts = (int) $occurrence->getTimestamp();
 					if ( $occ_ts <= (int) $timestamp && ( $occ_ts + (int) $duration_secs ) >= (int) $timestamp ) {
 						$cache[ $cache_key ] = true;
 						return true;
 					}
 				}
 				$cache[ $cache_key ] = false;
 				return false;
 			}

 			// Fallback: iterate occurrences but stop as soon as we pass the target.
 			foreach ( $rrule as $occurrence ) {
 				$occ_ts = (int) $occurrence->getTimestamp();

 				if ( $occ_ts <= (int) $timestamp && ( $occ_ts + (int) $duration_secs ) >= (int) $timestamp ) {
 					$cache[ $cache_key ] = true;
 					return true;
 				}
 				if ( $occ_ts > (int) $timestamp ) {
 					break;
 				}
 				// Additional skip: if occurrence is before window_start, try to fast-forward when possible.
 				if ( $occ_ts < $window_start_ts ) {
 					// No reliable way to jump ahead without library support; continue.
 					continue;
 				}
 			}
 		} catch ( Exception $e ) {
 			wc_get_logger()->error( $e->getMessage() );
 		}
 		$cache[ $cache_key ] = false;
 		return false;
 	}

	/**
	 * Checks if the given rrule and event happens between the given slot timestamps.
	 *
	 * @param array $range           Range and rrule to check.
	 * @param int   $slot_start_time Start Timestamp of the slot.
	 * @param int   $slot_end_time   End Timestamp of the slot.
	 *
	 * @return bool
	 */
	 private static function rrule_has_occurrences_between_slots( $range, $slot_start_time, $slot_end_time ) {
 		static $result_cache  = array(); // per-query boolean cache
 		static $rset_cache    = array(); // cache of prebuilt RSet + normalized dates
 		static $tz_cache      = null;    // DateTimeZone cache
 		static $gmt_str_cache = array(); // cache of gmt_offset string modifiers

 		$cache_key = $range['from'] . ':' . $range['to'] . ':' . $range['rrule'] . ':' . ( is_object( $slot_start_time ) ? $slot_start_time->getTimestamp() : (string) $slot_start_time ) . ':' . ( is_object( $slot_end_time ) ? $slot_end_time->getTimestamp() : (string) $slot_end_time );
 		if ( isset( $result_cache[ $cache_key ] ) ) {
 			return $result_cache[ $cache_key ];
 		}

 		// Prepare timezone and GMT offset string once.
 		if ( null === $tz_cache ) {
 			$tz_cache = new DateTimeZone( wc_timezone_string() );
 		}
 		$gmt_offset = get_option( 'gmt_offset' );
 		if ( ! isset( $gmt_str_cache[ $gmt_offset ] ) ) {
 			// Convert float offset to "H hours M minutes" with proper sign and zero-padding.
 			$sign           = ( $gmt_offset < 0 ) ? -1 : 1;
 			$abs_offset     = abs( (float) $gmt_offset );
 			$hours          = (int) $abs_offset;
 			$minutes        = (int) round( ( $abs_offset - $hours ) * 60 );
 			$hours_signed   = $hours * $sign;
 			$gmt_str_cache[ $gmt_offset ] = sprintf( '%+d hours %d minutes', $hours_signed, $minutes );
 		}
 		$gmt_offset_string = $gmt_str_cache[ $gmt_offset ];

 		$is_all_day = ( false === strpos( $range['from'], ':' ) );
 		$rrule_str  = wc_appointments_esc_rrule( $range['rrule'], $is_all_day );

 		// Build a secondary cache key for the expensive bits that don't depend on the specific slot.
 		$prep_key = implode( '|', array( $range['from'], $range['to'], $rrule_str, (string) (int) $is_all_day, (string) $gmt_offset, $tz_cache->getName() ) );

 		if ( ! isset( $rset_cache[ $prep_key ] ) ) {
 			// Normalize "from" and "to" once and cache.
 			$from_dt = new DateTimeImmutable( $range['from'] );
 			$from_dt = $from_dt->setTimezone( $tz_cache )->modify( $gmt_offset_string );

 			$to_dt = null;
 			if ( ! empty( $range['to'] ) ) {
 				$to_dt = new DateTimeImmutable( $range['to'] );
 				$to_dt = $to_dt->setTimezone( $tz_cache )->modify( $gmt_offset_string );
 			}

 			// Build and cache RSet, using local or all-day DTSTART appropriately.
 			$dtstart = $is_all_day ? $from_dt->format( 'Y-m-d' ) : $from_dt;
 			$rset    = new RSet( $rrule_str, $dtstart );

 			$rset_cache[ $prep_key ] = array(
 				'rset'   => $rset,
 				'from'   => $from_dt,
 				'to'     => $to_dt,
 				'allDay' => $is_all_day,
 			);
 		}

 		$prepared = $rset_cache[ $prep_key ];
 		$rset     = $prepared['rset'];
 		$from_dt  = $prepared['from'];
 		$to_dt    = $prepared['to'];

 		// Early window rejection: if slot window is entirely outside [from, to], no need to query RSet.
 		// Note: If 'to' is empty, do not upper-bound reject.
 		if ( $slot_end_time instanceof DateTimeInterface && $slot_start_time instanceof DateTimeInterface ) {
 			// Convert slot datetimes to the same timezone as normalized range for fair comparison (no mutation).
 			$slot_start_cmp = ( $slot_start_time instanceof DateTimeImmutable ? $slot_start_time : DateTimeImmutable::createFromMutable( $slot_start_time ) )->setTimezone( $tz_cache );
 			$slot_end_cmp   = ( $slot_end_time instanceof DateTimeImmutable ? $slot_end_time : DateTimeImmutable::createFromMutable( $slot_end_time ) )->setTimezone( $tz_cache );

 			// Apply same GMT offset modifier as range normalization, to keep comparisons consistent.
 			$slot_start_cmp = $slot_start_cmp->modify( $gmt_offset_string );
 			$slot_end_cmp   = $slot_end_cmp->modify( $gmt_offset_string );

 			// If the entire slot window ends before the rule window starts, or starts after the rule window ends, it's a miss.
 			if ( $slot_end_cmp < $from_dt ) {
 				$result_cache[ $cache_key ] = false;
 				return false;
 			}
 			if ( $to_dt && $slot_start_cmp > $to_dt ) {
 				$result_cache[ $cache_key ] = false;
 				return false;
 			}
 		}

 		// Ask for at most a single occurrence to keep it O(1) after the setup.
 		$occurrences  = $rset->getOccurrencesBetween( $slot_start_time, $slot_end_time, 1 );
 		$has_occ      = ! empty( $occurrences );
 		$result_cache[ $cache_key ] = $has_occ;

 		return $has_occ;
 	}
}
