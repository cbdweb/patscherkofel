<?php
/**
 * Booking form class
 */
class PSL_Booking_Form  {

	/**
	 * Booking product data.
	 * @var WC_Product_Booking
	 */
	public $product;

	/**
	 * Booking fields.
	 * @var array
	 */
	private $fields;

	/**
	 * Constructor
	 * @param $product WC_Product_Booking
	 */
	public function __construct( $product = null )
	{
		if( !is_null($product) )
		{
			$this->product = $product;
		}
	}

	/**
	 * Booking form scripts
	 */
	public function scripts() {
		global $wp_locale;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$wc_bookings_booking_form_args = array(
			'closeText'                  => __( 'Close', 'woocommerce-bookings' ),
			'currentText'                => __( 'Today', 'woocommerce-bookings' ),
			'monthNames'                 => array_values( $wp_locale->month ),
			'monthNamesShort'            => array_values( $wp_locale->month_abbrev ),
			'dayNames'                   => array_values( $wp_locale->weekday ),
			'dayNamesShort'              => array_values( $wp_locale->weekday_abbrev ),
			'dayNamesMin'                => array_values( $wp_locale->weekday_initial ),
			'firstDay'                   => get_option( 'start_of_week' ),
			'current_time'               => date( 'Ymd', current_time( 'timestamp' ) ),
			'check_availability_against' => $this->product->wc_booking_check_availability_against,
			'duration_unit'              => $this->product->wc_booking_duration_unit
		);

		if ( in_array( $this->product->wc_booking_duration_unit, array( 'minute', 'hour' ) ) ) {
			$wc_bookings_booking_form_args['booking_duration'] = 1;
		} else {
			$wc_bookings_booking_form_args['booking_duration']        = $this->product->wc_booking_duration;
			$wc_bookings_booking_form_args['booking_duration_type']   = $this->product->get_duration_type();

			if ( 'customer' == $wc_bookings_booking_form_args['booking_duration_type'] ) {
				$wc_bookings_booking_form_args['booking_min_duration'] = $this->product->get_min_duration();
				$wc_bookings_booking_form_args['booking_max_duration'] = $this->product->get_max_duration();
			} else {
				$wc_bookings_booking_form_args['booking_min_duration'] = $wc_bookings_booking_form_args['booking_duration'];
				$wc_bookings_booking_form_args['booking_max_duration'] = $wc_bookings_booking_form_args['booking_duration'];
			}
		}
		// use our own js script
		wp_enqueue_script( 'wc-bookings-booking-form', get_stylesheet_directory_uri() . '/assets/js/booking-form.js', array( 'jquery', 'jquery-blockui' ), WC_BOOKINGS_VERSION, true );
		wp_localize_script( 'wc-bookings-booking-form', 'wc_bookings_booking_form', $wc_bookings_booking_form_args );
		wp_register_script( 'wc-bookings-date-picker', WC_BOOKINGS_PLUGIN_URL . '/assets/js/date-picker' . $suffix . '.js', array( 'wc-bookings-booking-form', 'jquery-ui-datepicker' ), WC_BOOKINGS_VERSION, true );
		wp_register_script( 'wc-bookings-month-picker', WC_BOOKINGS_PLUGIN_URL . '/assets/js/month-picker' . $suffix . '.js', array( 'wc-bookings-booking-form' ), WC_BOOKINGS_VERSION, true );
		wp_register_script( 'wc-bookings-time-picker', WC_BOOKINGS_PLUGIN_URL . '/assets/js/time-picker' . $suffix . '.js', array( 'wc-bookings-booking-form' ), WC_BOOKINGS_VERSION, true );

		// Variables for JS scripts
		$booking_form_params = array(
			'ajax_url'              => WC()->ajax_url(),
			'i18n_date_unavailable' => __( 'This date is unavailable', 'woocommerce-bookings' ),
			'i18n_start_date'       => __( 'Choose a Start Date', 'woocommerce-bookings' ),
			'i18n_end_date'         => __( 'Choose an End Date', 'woocommerce-bookings' ),
			'i18n_dates'            => __( 'Dates', 'woocommerce-bookings' ),
			'i18n_choose_options'   => __( 'Please select the options for your booking above first', 'woocommerce-bookings' ),
		);

		wp_localize_script( 'wc-bookings-booking-form', 'booking_form_params', apply_filters( 'booking_form_params', $booking_form_params ) );
	}

	/**
	 * Prepare fields for the booking form
	 */
	public function prepare_fields() {
		// Destroy existing fields
		$this->reset_fields();

		// Add fields in order
		$this->duration_field();
		$this->date_field();

		$this->fields = apply_filters( 'booking_form_fields', $this->fields );
	}

	/**
	 * Reset fields array
	 */
	public function reset_fields() {
		$this->fields = array();
	}

	/**
	 * Add duration field to the form
	 */
	private function duration_field() {
		// Customer defined bookings
		if ( 'customer' == $this->product->wc_booking_duration_type ) {
			$after = '';
			$type  = '';
			switch ( $this->product->wc_booking_duration_unit ) {
				case 'month' :
					if ( $this->product->wc_booking_duration > 1 ) {
						$after = sprintf( __( '&times; %s Months', 'woocommerce-bookings' ), $this->product->wc_booking_duration );
					} else {
						$after = __( 'Month(s)', 'woocommerce-bookings' );
					}
					break;
				case 'week' :
					if ( $this->product->wc_booking_duration > 1 ) {
						$after = sprintf( __( '&times; %s weeks', 'woocommerce-bookings' ), $this->product->wc_booking_duration );
					} else {
						$after = __( 'Week(s)', 'woocommerce-bookings' );
					}
					break;
				case 'day' :
					if ( $this->product->wc_booking_duration % 7 ) {
						if ( $this->product->wc_booking_duration > 1 ) {
							$after = sprintf( __( '&times; %s days', 'woocommerce-bookings' ), $this->product->wc_booking_duration );
						} else {
							$after = __( 'Day(s)', 'woocommerce-bookings' );
						}
					} else {
						if ( $this->product->wc_booking_duration / 7 == 1 ) {
							$after = __( 'Week(s)', 'woocommerce-bookings' );
						} else {
							$after = sprintf( __( '&times; %s weeks', 'woocommerce-bookings' ), $this->product->wc_booking_duration / 7 );
						}
					}
					break;
				case 'hour' :
					if ( $this->product->wc_booking_duration > 1 ) {
						$after = sprintf( __( '&times; %s hours', 'woocommerce-bookings' ), $this->product->wc_booking_duration );
					} else {
						$after = __( 'Hour(s)', 'woocommerce-bookings' );
					}
					break;
				case 'minute' :
					if ( $this->product->wc_booking_duration > 1 ) {
						$after = sprintf( __( '&times; %s minutes', 'woocommerce-bookings' ), $this->product->wc_booking_duration );
					} else {
						$after = __( 'Minute(s)', 'woocommerce-bookings' );
					}
					break;
			}

			$this->add_field( array(
				'type'  => 'number',
				'name'  => 'duration',
				'label' => __( 'Duration', 'woocommerce-bookings' ),
				'after' => $after,
				'min'   => $this->product->wc_booking_min_duration,
				'max'   => $this->product->wc_booking_max_duration,
				'step'  => 1
			) );
		}
	}

	/**
	 * Add the date field to the booking form
	 */
	private function date_field() {
		$picker = null;

		// Get date picker specific to the duration unit for this product
		switch ( $this->product->get_duration_unit() ) {
			case 'month' :
				include_once( dirname(WC_BOOKINGS_MAIN_FILE).'/includes/booking-form/class-wc-booking-form-month-picker.php' );
				$picker = new WC_Booking_Form_Month_Picker( $this );
				break;
			case 'day' :
				include_once( dirname(WC_BOOKINGS_MAIN_FILE).'/includes/booking-form/class-wc-booking-form-date-picker.php' );
				$picker = new WC_Booking_Form_Date_Picker( $this );
				break;
			case 'minute' :
			case 'hour' :
				include_once( dirname(WC_BOOKINGS_MAIN_FILE).'/includes/booking-form/class-wc-booking-form-datetime-picker.php' );
				$picker = new WC_Booking_Form_Datetime_Picker( $this );
				break;
			default :
				break;
		}

		if ( ! is_null( $picker ) ) {
			$this->add_field( $picker->get_args() );
		}
	}

	/**
	 * Add Field
	 * @param  array $field
	 * @return void
	 */
	public function add_field( $field ) {
		$default = array(
			'name'  => '',
			'class' => array(),
			'label' => '',
			'type'  => 'text'
		);

		$field = wp_parse_args( $field, $default );

		if ( ! $field['name'] || ! $field['type'] ) {
			return;
		}

		$nicename = 'wc_bookings_field_' . sanitize_title( $field['name'] );

		$field['name']    = $nicename;
		$field['class'][] = $nicename;

		$this->fields[ sanitize_title( $field['name'] ) ] = $field;
	}

	/**
	 * Output the form - called from the add to cart templates
	 */
	public function output() {
		$this->scripts();
		/*$this->prepare_fields();

		foreach ( $this->fields as $key => $field ) {
			wc_get_template( 'booking-form/' . $field['type'] . '.php', array( 'field' => $field ), 'woocommerce-bookings', WC_BOOKINGS_TEMPLATE_PATH );
		}*/
	}

	/**
	 *
	 * VERY IMPORTANT FUNCTION!!
	 * Get posted form data into a neat array
	 * 
	 * @param  array $posted
	 * @return array
	 */
	public function get_posted_data( $posted = array() ) {
		global $bed_types;

		if ( empty( $posted ) ) {
			$posted = $_POST;
		}

		$data = array(
			'_year'    => '',
			'_month'   => '',
			'_day'     => ''
		);

		$account_number_cache = array();

		// Get date fields (y, m, d)
		if ( ! empty( $posted['wc_bookings_field_start_date_year'] ) && ! empty( $posted['wc_bookings_field_start_date_month'] ) && ! empty( $posted['wc_bookings_field_start_date_day'] ) ) {
			$data['_year']  = absint( $posted['wc_bookings_field_start_date_year'] );
			$data['_year']  = $data['_year'] ? $data['_year'] : date('Y');
			$data['_month'] = absint( $posted['wc_bookings_field_start_date_month'] );
			$data['_day']   = absint( $posted['wc_bookings_field_start_date_day'] );
			$data['_date']  = $data['_year'] . '-' . $data['_month'] . '-' . $data['_day'];
			$data['date']   = date_i18n( wc_date_format(), strtotime( $data['_date'] ) );
			$data['_account_number'] = '';
			
			// we will form the bed arrays now
			foreach ($bed_types as $k => $v){
				if (isset($posted[$k])) {
					foreach ($posted[$k][$this->product->id] as $bed_no => $bed_val) {
						foreach ($bed_val as $person_no => $person_val) {
							if ($person_val['name'] != '') {
								$data[$k][$this->product->id][$bed_no][$person_no]['name'] = stripslashes($posted[$k][$this->product->id][$bed_no][$person_no]['name']);

								if( $person_val['account_number'] != '' )
								{
									$account_number_cache[] = stripslashes($posted[$k][$this->product->id][$bed_no][$person_no]['account_number']);
								}
							}
						}
					}
				}
			}

			$data['_account_number'] = json_encode($account_number_cache);
		}

		// Get time field
		if ( ! empty( $posted['wc_bookings_field_start_date_time'] ) ) {
			$data['_time'] = wc_clean( $posted['wc_bookings_field_start_date_time'] );

			$data['time']  = date_i18n( get_option( 'time_format' ), strtotime( "{$data['_year']}-{$data['_month']}-{$data['_day']} {$data['_time']}" ) );
		} else {
			$data['_time'] = '';
		}

		// Quantity being booked
		$data['_qty'] = 1;

		
		// Duration
		if ( 'customer' == $this->product->wc_booking_duration_type ) {
			$booking_duration       = isset( $posted['wc_bookings_field_duration'] ) ? max( 0, absint( $posted['wc_bookings_field_duration'] ) ) : 0;
			$booking_duration_unit  = $this->product->get_duration_unit();

			$data['_duration_unit'] = $booking_duration_unit;
			$data['_duration']      = $booking_duration;

			// Get the duration * block duration
			$total_duration = $booking_duration * $this->product->wc_booking_duration;

			// Nice formatted version
			switch ( $booking_duration_unit ) {
				case 'month' :
					$data['duration'] = $total_duration . ' ' . _n( 'month', 'months', $total_duration, 'woocommerce-bookings' );
					break;
				case 'day' :
					if ( $total_duration % 7 ) {
						$data['duration'] = $total_duration . ' ' . _n( 'day', 'days', $total_duration, 'woocommerce-bookings' );
					} else {
						$data['duration'] = ( $total_duration / 7 ) . ' ' . _n( 'week', 'weeks', $total_duration, 'woocommerce-bookings' );
					}
					break;
				case 'hour' :
					$data['duration'] = $total_duration . ' ' . _n( 'hour', 'hours', $total_duration, 'woocommerce-bookings' );
					break;
				case 'minute' :
					$data['duration'] = $total_duration . ' ' . _n( 'minute', 'minutes', $total_duration, 'woocommerce-bookings' );
					break;
				default :
					$data['duration'] = $total_duration;
					break;
			}
		} else {
			// Fixed duration
			$booking_duration      = $this->product->get_duration();
			$booking_duration_unit = $this->product->get_duration_unit();
			$total_duration        = $booking_duration;
		}

		// Work out start and end dates/times
		if ( ! empty( $data['_time'] ) ) {
			$data['_start_date'] = strtotime( "{$data['_year']}-{$data['_month']}-{$data['_day']} {$data['_time']}" );
			$data['_end_date']   = strtotime( "+{$total_duration} {$booking_duration_unit}", $data['_start_date'] );
			$data['_all_day']    = 0;
		} else {
			$data['_start_date'] = strtotime( "{$data['_year']}-{$data['_month']}-{$data['_day']}" );
			$data['_end_date']   = strtotime( "+{$total_duration} {$booking_duration_unit}", $data['_start_date'] );
			$data['_all_day']    = 1;
		}

		return $data;
	}
	
	/**
	 * formats the days booked array
	 * @param array $data
	 * @return string
	 */
	public function format_days_booked($booking) {

		// get the bed total for the room
		$product = get_post($this->product->id);
		$single = $product->_wc_booking_single_bed;
		$double = $product->_wc_booking_double_bed;
		$bunk = $product->_wc_booking_bunk_bed;

		$html = '';

		foreach ($booking as $date_key => $date_val) {
			$bed_total = $single + $double + $bunk;

			$remainder = $bed_total;

			foreach ($date_val as $k => $v) {
				$remainder -= $v;	
			}

			// error_log('remainder is '.$remainder);


			if ($remainder == $bed_total) {
				$html .= $date_key.' (Free) <br/>';
			}
			elseif ($remainder < 1) {
				$html .= $date_key.' (Fully Booked) <br/>';
			}
			else {
				$html .= $date_key.' ('.$remainder.' Available) <br/>';
			}
			
		}
		
		return $html;
	}


	public function is_account_number_booked( $members )
	{
		global $wpdb;

		$selected 				= array();
		$selected['start_date'] = date('Ymd', strtotime(str_replace('/','-',$_SESSION['from'].'+1 day')));
		$selected['end_date'] 	= date('Ymd', strtotime(str_replace('/','-',$_SESSION['to'])));
		$selected['duration'] 	= $_SESSION['duration'];
		$selected['start_day'] 	= date('w', strtotime(str_replace('/','-',$_SESSION['from'])));
		$selected['end_day'] 	= date('w', strtotime(str_replace('/','-',$_SESSION['to'])));

		$booking_statuses = array(
			'unpaid',
			'pending-confirmation',
			'confirmed',
			'paid',
			'complete'
		);

		$booking_result = get_posts( array(
			'post_type'     => 'wc_booking',
			'post_status'   => $booking_statuses,
			'no_found_rows' => true,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_booking_end',
					'value'   => $selected['start_date'],
					'compare' => '>',
				),
				array(
					'key'     => '_booking_start',
					'value'   => $selected['end_date'],
					'compare' => '<',
				),

			)
		) );

		if( !empty($booking_result) )
		{
			$bookings    = array();

			foreach ( $booking_result as $k => $v )
			{
				$bookings[$k]['parent_id'] = $v->post_parent;

				$order_id = $v->post_parent;

				$sql = "SELECT
							
							meta_value

							FROM wp_woocommerce_order_items as item_meta

								INNER JOIN wp_woocommerce_order_itemmeta as items

									ON item_meta.`order_item_id` = items.`order_item_id`

								WHERE item_meta.order_id = '".$order_id."'

									AND meta_key = 'Account Number'

							;";

				$order = $wpdb->get_results( $sql );

				if( !empty($order) )
				{
					foreach ( $order as $key => $debenture_id )
					{
						$debentures_booked = json_decode($debenture_id->meta_value);

						foreach ($members as $key => $member)
						{
							if( isset($member['account_number']) && !empty($member['account_number']) )
							{
								if( in_array($member['account_number'], $debentures_booked ))
								{
									unset( $members[$key] );
								}
							}
						}
					};
				}
			}
		}

		return $members;
	}

	/**
	 * Checks booking data is correctly set, and that the chosen blocks are indeed available.
	 *
	 * @param array $selected array of user date selection
	 * @param $return_html boolean return html or raw array
	 * @return WP_Error on failure, true on success
	 */
	public function is_bookable( $selected , $return_html = false)
	{
		global $wpdb, $bed_types;

		$booking_ids = array();

		$booking_statuses = array(
			'unpaid',
			'pending-confirmation',
			'confirmed',
			'paid',
			'complete'
		);

		$booking_result = get_posts( array(
			'post_type'     => 'wc_booking',
			'post_status'   => $booking_statuses,
			'no_found_rows' => true,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_booking_product_id',
					'value'   => $this->product->id,
					'compare' => '=',
				),
				array(
					'key'     => '_booking_end',
					'value'   => $selected['start_date'],
					'compare' => '>',
				),
				array(
					'key'     => '_booking_start',
					'value'   => $selected['end_date'],
					'compare' => '<',
				),

			)
		) );
		$bookings    = array();
		$days_booked = array();
		$bed_sql = '';
		$bed_count = 0;
		foreach($bed_types as $k => $v) {
			$bed_sql .= " meta_key like '%{$k}_%'";
			$bed_count++;
			if ($bed_count == count($bed_types)) {
				break;
			}
			else {
				$bed_sql .= ' or ';
			}
		}
		
		foreach ( $booking_result as $k => $v ) {
			$bookings[$k]['id'] = $v->ID;
			$bookings[$k]['start_date'] = $v->_booking_start;
			$bookings[$k]['end_date'] = $v->_booking_end;
			$bookings[$k]['order_item_id'] = $v->_booking_order_item_id;
		}

		for ($i=0; $i < $selected['duration']; $i++) {
			
			$bed_count = 0;
			
			foreach ($bookings as $b) {

				$curr_day = date('Ymd', strtotime($selected['start_date'].' '.$i.' day'));

				if ($curr_day >= substr($b['start_date'],0,8) && $curr_day < substr($b['end_date'],0,8)) {
					// get number of people booked for that day
					$sql = "select count(*) as counter, meta_key from {$wpdb->prefix}woocommerce_order_itemmeta where ($bed_sql) and order_item_id='".$b['order_item_id']."' group by meta_key";
                    //print_r($sql);
					$res = '';
					$res = $wpdb->get_results($sql);
					foreach ($res as $r) {
						// if array is not empty, init it.
						if ( !isset($days_booked[date('d/m/Y',strtotime($curr_day))][$r->meta_key]) ) {
							$days_booked[date('d/m/Y',strtotime($curr_day))][$r->meta_key] = 0;
						}
						$days_booked[date('d/m/Y',strtotime($curr_day))][$r->meta_key] += $r->counter;
					}

				}
				
			}
		}
		if (count($days_booked)) {
			if (!$return_html) {
				return new WP_Error( 'Error', $this->format_days_booked($days_booked), 'woocommerce-bookings' );
			}
			else {
				return $days_booked;
			}
		}

		return true;
	}

	/**
	 * Get an array of formatted time values
	 * @param  string $timestamp
	 * @return array
	 */
	public function get_formatted_times( $timestamp ) {
		return array(
			'timestamp'   => $timestamp,
			'year'        => date( 'Y', $timestamp ),
			'month'       => date( 'n', $timestamp ),
			'day'         => date( 'j', $timestamp ),
			'week'        => date( 'W', $timestamp ),
			'day_of_week' => date( 'N', $timestamp ),
			'time'        => date( 'YmdHi', $timestamp ),
		);
	}

	/**
	 * get bed number from bookable array of this format
	 *
	 * @param array $bookable bookable array
	 * @param string single, double, or bunk
	 * @return array array of bed number found
	 * 
	 *	Array ( 
	 *	[12/10/2015] => Array ( 
	 *		[double_1] => 1 
	 *		[single_1] => 1 
	 *	) 
	 *	[13/10/2015] => Array ( 
	 *		[double_1] => 1 
	 *		[single_1] => 1
	 *	)
	 * ) 
	 */
	public function get_bed_number_from_bookable_array($bookable, $bed_type) {
		$bed_num_found = array();
		if (is_array($bookable)) {
			foreach ($bookable as $date_key => $date_val) {
				foreach ($date_val as $k => $v) {
					$key = explode('_', $k);
					if ($bed_type = $key[0]) {
						$bed_num_found[] = $key[1];
					}
				}
			}
		}
		return $bed_num_found;
	}
	/**
	 * get user selected settings
	 * 
	 * @param  [type] $posted [description]
	 * @return [type]         [description]
	 */
	public function get_user_selected_settings()
	{
		$selected['start_date'] = date('Ymd',strtotime(str_replace('/','-',$_SESSION['from'])));
		$selected['end_date'] 	= date('Ymd',strtotime(str_replace('/','-',$_SESSION['to'])));
		$selected['duration'] 	= $_SESSION['duration'];
		$selected['start_day'] 	= date('w',strtotime(str_replace('/','-',$_SESSION['from'])));
		$selected['end_day'] 	= date('w', strtotime(str_replace('/','-',$_SESSION['to'])));

		return $selected;
	}

	/**
	 * wrapper around calculate_booking_cost function and returns json results
	 * 
	 * @return [type] [description]
	 */
	static function psl_calculate_costs_json()
	{
		$posted = array();

		parse_str( $_POST['form'], $posted );

		// product_if is the booking id before payment is made
		$booking_id = $posted['add-to-cart'];

		$product    = get_product( $booking_id );

		if ( ! $product ) {
			die( json_encode( array(
				'result' => 'ERROR',
				'html'   => '<span class="booking-error">' . __( 'This booking is unavailable.', 'woocommerce-bookings' ) . '</span>'
			) ) );
		}

		$booking_form = new PSL_Booking_Form( $product );

		$data = $booking_form->get_posted_data( $posted );

		$selected = $booking_form->get_user_selected_settings();

		$bookable = $booking_form->is_bookable( $selected );

		// if a day is fully booked, we display the error, else we display success but with booked days	
		if ( is_wp_error( $bookable ) ) {
			$result = ( preg_match('/Fully Booked/',$bookable->get_error_message()) ) ? 'ERROR' : 'SUCCESS';
				
			die( json_encode( array(
			'result' => $result,
			'html'   => '<span class="booking-error">' . $bookable->get_error_message() . '</span>'
			) ) );
			
		}

		$cost = $booking_form->calculate_booking_cost( $posted, $selected );

		if ( is_wp_error( $cost ) ) {
			die( json_encode( array(
				'result' => 'ERROR',
				'html'   => '<span class="booking-error">' . $cost->get_error_message() . '</span>'
			) ) );
		}

		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		$display_price    = $tax_display_mode == 'incl' ? $product->get_price_including_tax( 1, $cost ) : $product->get_price_excluding_tax( 1, $cost );

		if ( version_compare( WC_VERSION, '2.4.0', '>=' ) ) {
			$price_suffix = $product->get_price_suffix( $cost, 1 );
		} else {
			$price_suffix = $product->get_price_suffix();
		}

		die( json_encode( array(
			'result' => 'SUCCESS',
			'html'   => __( 'Booking cost', 'woocommerce-bookings' ) . ': <strong>' . wc_price( $display_price ) . $price_suffix . '</strong>'
		) ) );
	}



	static function checkComingEvents(){
		// global $wpdb;
		// 	$wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d;", $post->ID ), ARRAY_A );
		$posts = get_posts(array('post_type' => 'booking-rules', 'orderby' => 'menu_order', 'order' => 'ASC'));

		$dates = array();
		foreach ($posts as $k => $v) {

			$from = explode('/',get_field('from_start_date', $v->ID));
			$dates[$k]['from'] = $from[2].'-'.$from[1].'-'.$from[0];

			$to = explode('/',get_field('to_end_date', $v->ID));
			$dates[$k]['to'] = $to[2].'-'.$to[1].'-'.$to[0];
			// if user is valid debenture, get priority debenture, else get secondary debenture
			if ($_SESSION['member'][0]['member_type'] == 'debenture') {
                $dates[$k]['incubation'] = get_field('first_priority_incubation_period', $v->ID);
            }
            else {
                $dates[$k]['incubation'] = get_field('second_priority_incubation_period', $v->ID);
		    }
        }

		return $dates;
	}

	/**
	 * make the submitted user array simple
	 * @param  array $posted submitted array
	 * @return array        stream-lined array
	 */
	public function get_submitted_user_arr($data) {
		global $bed_types;
		$member = array();

		foreach ($bed_types as $k => $v) {
			if (isset($data[$k])) {
				foreach ($data[$k] as $product_id => $product_val) {
					foreach ($product_val as $bed_no => $bed_val) {
						foreach ($bed_val as $person_no => $person_val) {
							if ($person_val['name'] != '') {
								$member[] = 
								array('name' => stripslashes($data[$k][$product_id][$bed_no][$person_no]['name']),
									'age' => $data[$k][$product_id][$bed_no][$person_no]['age'],
									'member_type' => $data[$k][$product_id][$bed_no][$person_no]['member_type'],
									'account_number' => $data[$k][$product_id][$bed_no][$person_no]['account_number']
								);
							}
						}
					}
				}
			}
		}

		return $member;
	}

	public function cal_member_pricing($member, $selected, $rule) {
		
		$cost = $rule['surcharge'];
		
		foreach ($member as $m) {
			// if debenture number valid, use debenture price
			if ($m['member_type'] == 'debenture' && $m['account_number'] != '') {
				$cost += $rule['member_price'];
				
			}
			// if aac, use aac prices
			elseif ($m['member_type'] == 'aac' && $m['account_number'] != '') {
				$cost += $rule['reciprocal_price'];
			}
			// else use guest pricing
			else {
				$cost += $rule['guest_price'];
			}
		}
		return $cost;
	}

	public function cal_member_pricing_children_week($member, $selected, $rule) {
		
		$cost = $rule['surcharge'];
		
		foreach ($member as $m) {
			if ($m['age'] == 'Under 5') {
				continue;
			}
			elseif ($m['age'] == '5 to 16') {
				$cost += $rule['special_rate_for_children_under_16'];
			}
			// if debenture number valid, use debenture price
			elseif ($m['member_type'] == 'debenture' && $m['account_number'] != '') {
				$cost += $rule['member_price'];
				
			}
			// if aac, use aac prices
			elseif ($m['member_type'] == 'aac' && $m['account_number'] != '') {
				$cost += $rule['reciprocal_price'];
			}
			// else use guest pricing
			else {
				$cost += $rule['guest_price'];
			}
		}
		return $cost;
	}

	public function get_rule($id) {
		$rule = array();
		$rule['start_date'] = get_post($id)->from_start_date;
		$rule['end_date'] = get_post($id)->to_end_date;
		$rule['surcharge'] = get_field('surcharge', $id);
		$rule['member_price'] = get_field('member_price', $id);
		$rule['reciprocal_price'] = get_field('reciprocal_price', $id);
		$rule['guest_price'] = get_field('guest_price', $id);
		$rule['special_rate_for_children_under_16'] = get_field('special_rate_for_children_under_16', $id);
		return $rule;
	}

	/**
	 * VERY IMPORTANT FUNCTION
	 * 
	 * Calculate costs from ajax calls
	 * 
	 * @param  array $posted
	 * @return string cost
	 */
	public function calculate_booking_cost( $posted, $selected ) {
		global $wpdb;

		if ( ! empty( $this->booking_cost ) ) {
			return $this->booking_cost;
		}
		
		// Get posted data
		// $data = $this->get_posted_data( $posted );

		// get member array
		$member = $this->get_submitted_user_arr($posted);
		// init cost to 0
		$cost = 0;

		// CHECK IF CHILDREN WEEK 1, sun and sun. Booking rule ID is 203
		$rule = $this->get_rule(203);
		if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
			&& ($selected['start_day'] == 0 && $selected['end_day'] == 0)
			) {
			
			$cost = $this->cal_member_pricing_children_week($member, $selected, $rule);
			return $cost;
		}

		// CHECK IF CHILDREN WEEK 2, sun and sun. Booking rule ID is 110
		$rule = $this->get_rule(110);
		if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
			&& ($selected['start_day'] == 0 && $selected['end_day'] == 0)
			) {
			$cost = $this->cal_member_pricing_children_week($member, $selected, $rule);
			return $cost;
		}

		// CHECK WEEKLY BOOKING sun to sun, or friday to friday. Booking rule ID is 116
		$rule = $this->get_rule(116);
		if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
			&& (($selected['start_day'] == 0 && $selected['end_day'] == 0)
				|| ($selected['start_day'] == 5 &&  $selected['end_day'] == 5))
			) {
			
			
			$cost = $this->cal_member_pricing($member, $selected, $rule);
			
			// if its more than a week incl.
			if ($selected['duration'] > 7) {
				// process some logic here 
				return new WP_Error( 'Error', __( 'Sorry, you booked more than a week. Please contact the admin.', 'woocommerce-bookings' ) );
			}

			return $cost;
		}

		// CHECK IF PEAK WEEKEND BOOKING, sat and sun. Booking rule ID is 117
		$rule = $this->get_rule(117);
		if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
			&& ($selected['start_day'] == 5 && $selected['end_day'] == 7)
			) {
			
			$cost = $this->cal_member_pricing($member, $selected, $rule);
			return $cost;
		}
		
		// CHECK IF PEAK MIDWEEK 4 NIGHTS BOOKING, Mon to Friday. Booking rule ID is 118
		$rule = $this->get_rule(118);
		if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
			&& ($selected['start_day'] == 1 && $selected['end_day'] == 5)
			) {
			
			$cost = $this->cal_member_pricing($member, $selected, $rule);
			return $cost;
		}

		// CHECK IF PEAK MIDWEEK NiGHTS BOOKING, Mon to Thursday. Booking rule ID is 120
		$rule = $this->get_rule(120);
		if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
			&& ($selected['start_day'] > 0 && $selected['end_day'] <= 5)
			) {
			for ($i = 0; $i < $selected['duration']; $i++) {
				$cost += $this->cal_member_pricing($member, $selected, $rule);
			}
			
			return $cost;
		}


		// if all rules doens't match, we return an error
		return new WP_Error( 'Error', __( 'Sorry, there was an error calculating the booking cost. Please contact the admin.', 'woocommerce-bookings' ) );

		// return apply_filters( 'booking_form_calculated_booking_cost', $this->booking_cost, $this, $posted );
	}


	public static function set_booking_rule()
	{
		$dowMap = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

		$from_unix = $to_unix = '';

		if( '' !== $_POST['from'] )
		{
			$fu 		= new DateTime( str_replace('/', '-', $_POST['from']) );
			$from_unix 	= $fu->format("U");
		}

		if( '' !== $_POST['to'] )
		{
			$tu 		= new DateTime( str_replace('/', '-', $_POST['to']) );
			$to_unix 	= $tu->format("U");
		}

		$selected = array();

		$selected['start_date'] = date('Ymd',strtotime(str_replace('/','-',$_POST['from'])));
		$selected['end_date'] 	= date('Ymd',strtotime(str_replace('/','-',$_POST['to'])));
		$selected['start_day'] 	= date('w',strtotime(str_replace('/','-',$_POST['from'])));
		$selected['end_day'] 	= date('w', strtotime(str_replace('/','-',$_POST['to'])));
		$selected['duration'] 	= self::daysDifference($from_unix, $to_unix);

		$weeks = self::weeksDifference( $_POST['from'], $_POST['to'] );

		$RULE_MATCH = false;

		print_r($weeks);

		for ($i = 0; $i < $weeks['loop']; $i++ )
		{
			// RESET
			if( $i > 0 )
			{
				/*echo ' .. ';
				print_r( $selected['duration'] / $weeks['days'] - $weeks['weeks'] * 7 );
				echo ' .. ';*/


				$selected['start_day'] = $selected['start_day'] + $weeks['days'];

				//print_r($selected['start_day']);

				if( $selected['start_day'] > 6 )
				{
					$selected['start_day'] = 0;
				}

				//print_r($selected['start_day'].' | ');

				//print_r( $dowMap[ $selected['start_day'] ] );
			}

			// CHECK WEEKLY BOOKING sun to sun, or friday to friday. Booking rule ID is 116
			// If the remainder is grater than 0 it can't be this rule
			print_r($i);
			if( $weeks['weeks'] > 0 && ( ($i+1) < $weeks['loop'] || $weeks['loop'] == 1) || $weeks['days'] == 0 )
			{
				$rule = self::get_rule_static(116);

				if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& (
						($selected['start_day'] == 0 && $selected['end_day'] == 0)
						|| ($selected['start_day'] == 5 && $selected['end_day'] == 5) // FRIDAY
						|| ($selected['start_day'] == 7 && $selected['end_day'] == 7) // SUNDAY
					)){
					
					print_r(':: SUNDAY to SUNDAY, FRIDAY to FRIDAY matched ::');

					$RULE_MATCH = true;

					continue;
				}

				if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date'] )
				{
					self::rule_fallback( $selected, $weeks );
					print_r(':: WEEK BOOKING matched ::');
				}

				continue;
			}

			//exit;


			// CHECK IF PEAK MIDWEEK 4 NIGHTS BOOKING, Mon to Friday. Booking rule ID is 118
			$rule = self::get_rule_static(118);
			if( ($weeks['days']) <= 5 )
			{
				if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& ($selected['start_day'] == 1 && $selected['end_day'] == 5)
					) {
					
					print_r(':: MONDAY to FRIDAY matched ::');

					$RULE_MATCH = true;
				}
			}


			// CHECK IF PEAK MIDWEEK NiGHTS BOOKING, Mon to Thursday. Booking rule ID is 120
			$rule = self::get_rule_static(120);
			if( ($weeks['days']) > 0 && ($weeks['days']) < 4 && $weeks['loop'] <= 1 )
			{
				if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& ($selected['start_day'] >= 1 && $selected['end_day'] <= 5)
					&& ($selected['end_day'] != 6 && $selected['end_day'] != 0)
					) {

					print_r(':: MID WEEK matched ::');

					$RULE_MATCH = true;
				}
			}


			// CHECK IF PEAK WEEKEND BOOKING, fri, sat and sun. Booking rule ID is 117
			$rule = self::get_rule_static(117);
			if( ($weeks['days']) <= 3 )
			{
				if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& ($selected['start_day'] == 5 || $selected['start_day'] == 6)
					&& ($selected['end_day'] == 6 || $selected['end_day'] == 0)
					) {
					
					print_r(':: SATURDAY to SUNDAY matched ::');

					$RULE_MATCH = true;
				}
			}


			// IF NO RULES ARE MATCHED THEN CHARGE DAILY RATES
			if( !$RULE_MATCH )
			{
				
				self::rule_fallback( $selected, $weeks );

				//$weekdays_total = $selected['duration'] - $weekend_total;

				/*print_r('WEEK DAYS:: '. $weekdays_total);
				print_r('WEEKEND:: '. $weekend_total);*/
			}


			

		}


		

		/*$message = json_encode( $selected, true);

		echo $message;*/

		exit;
	}


	private static function rule_fallback( $selected, $weeks )
	{
		//print_r('NO RULES FOUND');

		//print_r($selected);
		$weekend_total 	= 0;

		$weekday_total 	= 0;

		$period 		= self::date_range( str_replace('/','-',$_POST['from']), str_replace('/','-',$_POST['to']) );

		foreach ($period as $p => $day)
		{	
			if( $day['pos'] == 5 || $day['pos'] == 6 || $day['pos'] == 0)
			{
				$weekend_total++;

			}else{

				$weekday_total++;
			}
		};

		/*$weekend_total = 0;

		$weekday_total = 0;

		if( 
			($selected['end_day'] == 5 || $selected['end_day'] == 6 || $selected['end_day'] == 0)

		   ){

			if( $selected['end_day'] == 5 )
			{
				$weekend_total += 1;
			}
			if( $selected['end_day'] == 6 )
			{
				$weekend_total += 2;
			}
			if( $selected['end_day'] == 0 )
			{
				$weekend_total += 3;
			}
		}

		$weekday_total = $selected['duration'] - $weekend_total;*/
		
		print_r(' Weekdays:: ' . $weekday_total  );
		//print_r('Weekdays:: '.$weekend_total);
		print_r(' Weekend days:: '.$weekend_total." | ");

		//print_r($selected);

		return $weekend_total;
	}

	private static function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y' ) {

	    $dates = array();
	    $current = strtotime($first);
	    $last = strtotime($last);

	    while( $current <= $last ) {

	        $dates[] = array('pos'=> (date('w', $current)) );//date($output_format, $current);
	        $current = strtotime($step, $current);
	    }

	    return $dates;
	}


	public static function get_rule_static($id)
	{
		$rule = array();
		$rule['start_date'] = get_post($id)->from_start_date;
		$rule['end_date'] = get_post($id)->to_end_date;
		$rule['surcharge'] = get_field('surcharge', $id);
		$rule['member_price'] = get_field('member_price', $id);
		$rule['reciprocal_price'] = get_field('reciprocal_price', $id);
		$rule['guest_price'] = get_field('guest_price', $id);
		$rule['special_rate_for_children_under_16'] = get_field('special_rate_for_children_under_16', $id);

		return $rule;
	}


	private static function daysDifference( $startDate, $endDate )
    {
		$seconds_diff 	= $endDate - $startDate;
		$days_diff 		= floor($seconds_diff/3600/24);
		
		return $days_diff;
    }

    private static function weeksDifference( $startDate, $endDate )
    {
		$day   		= 24 * 3600;
		$startDate  = strtotime(str_replace('/', '-', $startDate));
		$endDate    = strtotime(str_replace('/', '-', $endDate));
		$diff  		= abs($endDate - $startDate);
		$weeks 		= floor($diff / $day / 7);
		$days  		= $diff / $day - $weeks * 7;
		
		$out   		= array();

		$out['weeks'] 	= ($weeks) 	? $weeks : 0;
		$out['days'] 	= $days;
		$out['loop'] 	= ($days) ? $weeks+1 : $weeks;
		
		return $out;
	}

	
	// CHECK IF THERE IS A MEMBER WITH A "MEMBERSHIP #" SUPPLIED OR DEBENTURE ID SUPPLIED
	public static function get_member_details()
	{
		if( isset($_POST['member_id']) && empty($_POST['member_id']) )
		{
			echo json_encode( array('success' => '0', 'error' => 'Please enter a membership #' ), true);

			exit;
		}

		global $wpdb;

		$returnObj 		= array();

		$members 		= get_users( 'role=subscriber&member_type=debenture' );

		$account_ids 	= array();


		foreach ($members as $index => $member)
		{
			$member_meta = get_user_meta( $member->ID );

			foreach ($member_meta as $key => $meta)
			{
				if( preg_match_all('/account_number_(\d+)/i', $key) )

				{
					
					if( substr($key, 0, 1) != "_" )
					{
						foreach ($meta as $t => $v)
						{
							if( !empty($v) )
							{
								if( $meta[0] == trim($_POST['member_id']) )
								{
									$returnObj = $member_meta;

									break;
								}
							}
						}
					}
				}
			}
		}

		if( !empty($returnObj) )
		{
			$returnObj = array( 'success' => '1', 'member' => $returnObj );

		}else{

			$returnObj = array('success' => '0', 'error' => 'No member found');
		}

		echo json_encode( $returnObj, true);

		exit;
	}


	function get_product_availability()
	{
	    global $product, $post;

		// Get dates from custom field
		/*$start_date 	= $_SESSION['from'];
		$end_date 		= str_replace( '/', '-', $_SESSION['to'] );*/
		$start_date 	= (isset($_SESSION['from']) && $_SESSION['from'] != '') ? $_SESSION['from'] : '';
		$end_date 		= (isset($_SESSION['to']) && $_SESSION['to'] != '') ? $_SESSION['to'] : '';

		$from_unix = $to_unix = '';

		if( '' !== $start_date )
		{
			$fu 		= new DateTime( str_replace('/', '-', $start_date) );
			$from_unix 	= $fu->format("U");
		}

		if( '' !== $end_date )
		{
			$tu 		= new DateTime( str_replace('/', '-', $end_date) );
			$to_unix 	= $tu->format("U");
		}



		// Get into class
		$WC_Product_Booking = new WC_Product_Booking($product);

		//print_r( $WC_Product_Booking->get_available_bookings($from_unix, $to_unix) );

		// Get resources for this product
		$resources = $WC_Product_Booking->get_resource($product->id);

		//print_r($resources);



		// Add all resource quantities of this product together
		$qtyResources = 0;

		foreach ( $resources as $resource )
		{
		  $qtyResources += get_post_meta($resource->ID, 'qty', true);
		}

		// get the number of bookings for this product on the start date
		$bookedPosts = get_posts(array(
		    'post_type'   => 'wc_booking',
		    'post_status' => array('unpaid', 'pending-confirmation', 'confirmed', 'paid'),
		    'posts_per_page' => -1,
		    'fields' => 'ids',
		    'meta_query' => array(
					array(
						'key' => '_booking_product_id',
						'value' => $post->ID
					),
					array(
						'key' => '_booking_start',
						'value'   => $start_date."000000"
					),
				)
		  )
		);



		$productBookings = count($bookedPosts);

		// If total resource quantity is equal to confirmed bookings then it's fully booked
		if ($qtyResources <= $productBookings) {
			$fullyBooked = true;
		} else {
			$fullyBooked = false;
		}

		return $fullyBooked;
	}
}
