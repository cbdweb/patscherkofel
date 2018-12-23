<?php
/**
 * Booking form class
 */

include_once('tac_functions.php');

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


	private $functions;

	private static $booking_debug = array();

	private static $DEBUG_ON = FALSE;

	private static $cost_cache;

	private static $cost_member_cache;

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
		$this->functions = new tac_functions;
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

		//print_r($posted);

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


	//There is something wrong with this function. It is not returning all of the people who are booked into beds.
	//Only some.

	// WHEN A MEMBER IS VIEWING AVAILABLE ROOMS SHOW IF ANY OTHER MEMBER HAS ALREADY BOOKED A BED
	public function whos_booked( $selected )
	{
		global $wpdb, $bed_types;

		$selected['start_date'] = $selected['start_date'] . '000000';
		$selected['end_date'] = $selected['end_date'] . '000000';

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
							'type' 	  => 'NUMERIC'
					),
					array(
							'key'     => '_booking_start',
							'value'   => $selected['end_date'],
							'compare' => '<',
							'type' 	  => 'NUMERIC'
					),

			)
		) );

		$bookings = array();
		$days_booked = array();
		$beds_unavailable = array();
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

			foreach ($bookings as $bkey => $b) {

				$curr_day = date('Ymd', strtotime(substr($selected['start_date'],0,8).' '.$i.' day'));

				if ($curr_day >= substr($b['start_date'],0,8) && $curr_day < substr($b['end_date'],0,8)) {
					// get number of people booked for that day
					//This is now returning all the results required
					$sql = "select * from {$wpdb->prefix}woocommerce_order_itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items ON {$wpdb->prefix}woocommerce_order_itemmeta.`order_item_id` = {$wpdb->prefix}woocommerce_order_items.`order_item_id` where ($bed_sql) and {$wpdb->prefix}woocommerce_order_items.order_item_id='".$b['order_item_id']."'";
					//print_r($sql);
					$re = '';

					$re = $wpdb->get_results($sql);

					$serialize_catch = array();

					foreach ($re as $rk => $rv) {

						$unserialized = (is_array(unserialize($re[$rk]->meta_value))) ? unserialize($re[$rk]->meta_value) : unserialize(unserialize($re[$rk]->meta_value));

						if (isset($unserialized['account_number']) && !empty($unserialized['account_number'])) {
							$_POST['member_id'] = $unserialized['account_number'];

							$member = $this->get_member_details('NO_AJAX')['member'];

							$unserialized = array(
									'name' => $unserialized['name'],
									'account_number' => $unserialized['account_number'],
									'member_type' => $unserialized['member_type'],
									'age' => $unserialized['age'],
									'gender' => $member['gender'][0],
									'bed' => $unserialized['bed']
							);

						}

						$serialize_catch[$rv->order_item_name] = $unserialized;

						$s = $booking_result[$bkey]->booking_items;
						$s[] = $serialize_catch;
						$booking_result[$bkey]->booking_items = $s;

					}

				}

			}

		}

		return $booking_result;
	}

	/**
	 * Checks booking data is correctly set, and that the chosen blocks are indeed available.
	 *
	 * @param array $selected array of user date selection
	 * @param $return_html boolean return html or raw array
	 * @return WP_Error on failure, true on success
	 */
	public function is_bookable( $selected = null, $return_html = false, $returnBookings = false)
	{
		if( is_null($selected) && ( isset($_SESSION['from']) && !empty($_SESSION['from']) ) )
		{
			$start_date 	= implode('', array_reverse(explode('/', $_SESSION['from']))) . '000000';
			$end_date 		= implode('', array_reverse(explode('/', $_SESSION['to']))) . '000000';

			$selected = array( 
					'start_date' 	=> $start_date,
					'end_date' 		=> $end_date,
					'duration' 		=> $_SESSION['duration'],
					'start_day' 	=> date('w', strtotime($start_date)),
					'end_day' 		=> date('w', strtotime($end_date))
				);
		}

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
                    'type' 	  => 'NUMERIC'
				),
				array(
					'key'     => '_booking_start',
					'value'   => $selected['end_date'],
					'compare' => '<',
                    'type' 	  => 'NUMERIC'
				),

			)
		) );

		$bookings    = array();
		$days_booked = array();
		$beds_unavailable = array();
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

				$curr_day = date('Ymd', strtotime(substr($selected['start_date'],0,8).' '.$i.' day'));

				if ($curr_day >= substr($b['start_date'],0,8) && $curr_day < substr($b['end_date'],0,8)) {
					// get number of people booked for that day
					$sql = "select count(*) as counter, {$wpdb->prefix}woocommerce_order_items.order_item_name, meta_key from {$wpdb->prefix}woocommerce_order_itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items ON {$wpdb->prefix}woocommerce_order_itemmeta.`order_item_id` = {$wpdb->prefix}woocommerce_order_items.`order_item_id` where ($bed_sql) and {$wpdb->prefix}woocommerce_order_items.order_item_id='".$b['order_item_id']."' group by meta_key";
                    //print_r($sql);
					$res = '';

					$res = $wpdb->get_results($sql);

					foreach ($res as $r) {
						// if array is not empty, init it.
						if ( !isset($days_booked[date('d/m/Y',strtotime($curr_day))][$r->meta_key]) ) {
							$days_booked[date('d/m/Y',strtotime($curr_day))][$r->meta_key] = 0;

							//Need to get the room number in here somewhere

//							$beds_unavailable[$r->order_item_name][] = $r->meta_key;
							$beds_unavailable[] = $r->meta_key;
						}
						$days_booked[date('d/m/Y',strtotime($curr_day))][$r->meta_key] += $r->counter;

					}

				}
				
			}
		}

		if($returnBookings === true) {

			if (count($days_booked)) {
				if (!$return_html) {
					return new WP_Error( 'Error', $this->format_days_booked($days_booked), 'woocommerce-bookings' );
				}
				else {
					return $days_booked;
				}
			}

		}
		else {

			// IF THERE ARE PARTIAL BOOKINGS RETURN WHAT BEDS ARE ACUALY AVAILABLE
			if (!empty($beds_unavailable)) {

				$roomBedData = $this->get_all_beds();

				foreach($roomBedData as $roomBedType => $roomBeds) {

					foreach ($roomBeds as $roomBedKey => $roomBed) {

						//$roomBed value is 'single_1', 'single_2', 'bunk_1' etc.
						//$roomBedType value is 'single_beds', 'double_beds' or 'bunk_beds'

						//If the particular room has a bed that is in this room's booked beds list, remove it
						//We want to have an array of only unoccupied beds left

						if(in_array($roomBed, $beds_unavailable)) {

							unset($roomBedData[$roomBedType][$roomBedKey]);

						}

					}
				}

				foreach($roomBedData as $roomBeds) {

					if(!empty($roomBeds)) {

						return true;

					}

				}

				return false;

			}
			else {
				return true;
			}

		}

//		return true;
	}



	/**
	 * @return array of all beds that belong to a room
	 *
	 */
	private function get_all_beds()
	{
		global $bed_types;

		$all_beds 		= array();

		foreach ($bed_types as $bed_key => $bed_type)
		{
			$beds = get_post_meta( $this->product->id, '_wc_booking_'.$bed_key.'_bed', true );

			if( $beds > 0 )
			{
				for ($i = 0; $i < $beds; $i++)
				{ 
				 	$all_beds[$bed_key.'_beds'][] = $bed_key . '_' .($i+1);
				}
			}
		}

		return $all_beds;
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
		//die( json_encode(array('success' => 1), true) );

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

		$bookable = $booking_form->is_bookable( $selected, false, true );

		// if a day is fully booked, we display the error, else we display success but with booked days	
		if ( is_wp_error( $bookable ) ) {
			$result = ( preg_match('/Fully Booked/',$bookable->get_error_message()) ) ? 'ERROR' : 'SUCCESS';
				
			die( json_encode( array(
			'result' => $result,
			'html'   => '<span class="booking-error"></span><br /><span class="booking-error">' . $bookable->get_error_message() . '</span>'
			) ) );
		}

		//$cost = $booking_form->calculate_booking_cost( $posted, $selected );
		$cost = $booking_form->get_booking_rule( $posted, $selected );


		$event_out = '';

		if( is_array($cost) )
		{
			$event_out = '';

			$cost_cache = '';


			if( isset($cost['has_events']) )
			{
				foreach ($cost as $c => $cst)
				{
					if( !is_null($cst['event_name']) )
					{
						$event_out .= ' <p><strong>'.ucwords($cst['member']['name']).' has a price amendment due to '.$cst['event_name'].' Event</strong></p>';
					}

					$cost_cache += $cst['cost'];
				}
			}else{

				foreach ($cost as $c => $cost_index)
				{
					foreach ($cost_index as $mci => $mem_cost)
					{
						$cost_cache += $mem_cost['cost'];
					}
				}
			}

			$cost = $cost_cache;
		}

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


		self::$DEBUG_ON = TRUE;
		self::get_debug_booking();

		die( json_encode( array(
			'result' => 'SUCCESS',
			'html'   => __( 'Booking cost', 'woocommerce-bookings' ) . ': <strong>' . wc_price( $display_price ) . $price_suffix . '</strong>'.$event_out
		) ) );
	}



	static function checkComingEvents()
	{
		//print_r($_SESSION);
		// global $wpdb;
		// 	$wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d;", $post->ID ), ARRAY_A );
		$posts = get_posts(array('post_type' => 'booking-rules', 'orderby' => 'menu_order', 'order' => 'ASC'));

		//print_r($posts);

		$dates = array();

		// PEAK
		foreach ($posts as $k => $v) 
		{
			$from = explode('/',get_field('from_start_date', $v->ID));
			$dates[$k]['from'] = $from[2].'-'.$from[1].'-'.$from[0];

			$to = explode('/',get_field('to_end_date', $v->ID));
			$dates[$k]['to'] = $to[2].'-'.$to[1].'-'.$to[0];

			// if user is valid debenture, get priority debenture, else get secondary debenture
			if( isset($_SESSION['member'][0]) )
			{
				if ($_SESSION['member'][0]['member_type'] == 'debenture') {
	                $dates[$k]['incubation'] = get_field('first_priority_incubation_period', $v->ID);
	            }
	            else {
	                $dates[$k]['incubation'] = get_field('second_priority_incubation_period', $v->ID);
			    }
			}

			if( !isset($_SESSION['member'][0]) && isset($_SESSION['member'][1]) )
			{
				if ($_SESSION['member'][1]['member_type'] == 'debenture') {
	                $dates[$k]['incubation'] = get_field('first_priority_incubation_period', $v->ID);
	            }
	            else {
	                $dates[$k]['incubation'] = get_field('second_priority_incubation_period', $v->ID);
			    }
			}
        }


        // OFF PEAK
        foreach ($posts as $k => $v) {

			$from = explode('/',get_field('from_start_date_off_peak', $v->ID));
			$dates[$k]['from_off_peak'] = $from[2].'-'.$from[1].'-'.$from[0];

			$to = explode('/',get_field('to_end_date_off_peak', $v->ID));
			$dates[$k]['to_off_peak'] = $to[2].'-'.$to[1].'-'.$to[0];

			// if user is valid debenture, get priority debenture, else get secondary debenture
			if( isset($_SESSION['member'][0]) )
				{
				if ($_SESSION['member'][0]['member_type'] == 'debenture') {
	                $dates[$k]['incubation_off_peak'] = get_field('first_priority_incubation_period_off_peak', $v->ID);
	            }
	            else {
	                $dates[$k]['incubation_off_peak'] = get_field('second_priority_incubation_period_off_peak', $v->ID);
			    }
			}

			if( !isset($_SESSION['member'][0]) && isset($_SESSION['member'][1]) )
				{
				if ($_SESSION['member'][1]['member_type'] == 'debenture') {
	                $dates[$k]['incubation_off_peak'] = get_field('first_priority_incubation_period_off_peak', $v->ID);
	            }
	            else {
	                $dates[$k]['incubation_off_peak'] = get_field('second_priority_incubation_period_off_peak', $v->ID);
			    }
			}
        }

        unset($dates[1], $dates[2], $dates[3]);

        //print_r($dates);

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

	/*public function cal_member_pricing_children_week($member, $selected, $rule) {
		
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
	}*/

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
		/*$rule = $this->get_rule(203);
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
		}*/

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


	private static function season_collision_dates( $rule )
	{
		$rule['collision_start_date'] = min($rule['start_date'], $rule['start_date_off_peak']);
		$rule['collision_end_date'] = max($rule['end_date'], $rule['end_date_off_peak']);
		
		return $rule;
	}


	public static function get_booking_rule( $posted, $selected )
	{
		$booking_form = new PSL_Booking_Form();

		$member = $booking_form->get_submitted_user_arr($posted);

		$cost = array();

		$dowMap = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

		$from_unix = $to_unix = '';

		if( '' !== $_SESSION['from'] )
		{
			$fu 		= new DateTime( str_replace('/', '-', $_SESSION['from']) );
			$from_unix 	= $fu->format("U");
		}

		if( '' !== $_SESSION['to'] )
		{
			$tu 		= new DateTime( str_replace('/', '-', $_SESSION['to']) );
			$to_unix 	= $tu->format("U");
		}


		$selected['duration'] 	= self::daysDifference($from_unix, $to_unix);

		$weeks = self::weeksDifference( $_SESSION['from'], $_SESSION['to'] );

		$RULE_MATCH = false;

		self::add_debug_booking( '::WEEKS::', $weeks );

		$temp_cost = 0;

		for ($i = 0; $i < $weeks['loop']; $i++ )
		{
			//if( $weeks['loop'] > 1 ) continue;

			// RESET
			if( $i > 0 )
			{
				$selected['start_day'] = $selected['start_day'] + $weeks['days'];

				if( $selected['start_day'] > 6 )
				{
					$selected['start_day'] = 0;
				}
			}



			// CHECK WEEKLY BOOKING sun to sun, or friday to friday. Booking rule ID is 116
			// If the remainder is grater than 0 it can't be this rule
			if( $weeks['weeks'] <= 1 && ( ($i+1) < $weeks['loop'] || $weeks['loop'] == 1)  )
			{
				$rule = self::get_rule_static(116);
				$rule = self::season_collision_dates($rule);

				if (
					$selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& (
						($selected['start_day'] == 0 && $selected['end_day'] == 0)
						|| ($selected['start_day'] == 5 && $selected['end_day'] == 5) // FRIDAY
					)

					||

					$selected['start_date'] >= $rule['start_date_off_peak'] && $selected['end_date'] <= $rule['end_date_off_peak']
					&& (
						($selected['start_day'] == 0 && $selected['end_day'] == 0)
						|| ($selected['start_day'] == 5 && $selected['end_day'] == 5) // FRIDAY
					)

					||

					$selected['start_date'] >= $rule['collision_start_date'] && $selected['end_date'] <= $rule['collision_end_date']
					&& (
						($selected['start_day'] == 0 && $selected['end_day'] == 0)
						|| ($selected['start_day'] == 5 && $selected['end_day'] == 5) // FRIDAY
					)

				){
					
					// APPLY THE COST
					$cost[] = self::cal_member_pricing_static($member, $selected, $rule);
					$RULE_MATCH = true;

					$debug = array( 'WEEKS LOOP' => $weeks['weeks'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );

					self::add_debug_booking( '::SUN TO SUN, OR FRIDAY TO FRIDAY::', $debug );

					//$temp_cost = 6;

					//continue;
				}

				/*if ($selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date'] )
				{
					//$cost += self::rule_fallback( $selected, $weeks, $member );
					//print_r(':: WEEK BOOKING matched ::');
				}*/
				//continue;
			}

			//exit;


			// CHECK IF PEAK MIDWEEK 4 NIGHTS BOOKING, Mon to Friday. Booking rule ID is 118
			$rule = self::get_rule_static(118);
			$rule = self::season_collision_dates($rule);

			if( ($weeks['days']) <= 5 && $weeks['loop'] <= 1 )
			{
				if (
					$selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& ($selected['start_day'] == 1 && $selected['end_day'] == 5)

					||

					$selected['start_date'] >= $rule['start_date_off_peak'] && $selected['end_date'] <= $rule['end_date_off_peak']
					&& ($selected['start_day'] == 1 && $selected['end_day'] == 5)

					||

					$selected['start_date'] >= $rule['collision_start_date'] && $selected['end_date'] <= $rule['collision_end_date']
					&& ($selected['start_day'] == 1 && $selected['end_day'] == 5)

					) {
					//print_r(':: PEAK MIDWEEK 5 NIGHTS BOOKING ::');
					$cost[] = self::cal_member_pricing_static($member, $selected, $rule);
					$RULE_MATCH = true;

					$debug = array( 'WEEKS LOOP' =>$weeks['weeks'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );
					self::add_debug_booking( '::PEAK MIDWEEK 5 NIGHTS BOOKING::', $debug );
					//$temp_cost+= $weeks['days'];
				}
			}


			// CHECK IF PEAK MIDWEEK NiGHTS BOOKING, Mon to Thursday. Booking rule ID is 120
			$rule = self::get_rule_static(120);
			$rule = self::season_collision_dates($rule);

			if( ($weeks['days']) > 0 && ($weeks['days']) < 4 && $weeks['loop'] <= 1 )
			{
				/*print_r($selected);
				echo ' | ';
				print_r($rule['end_date']);*/
				if (
					$selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& ($selected['start_day'] >= 0 && $selected['end_day'] <= 5)
					&& ($selected['end_day'] != 6 && $selected['end_day'] != 0)

					||

					$selected['start_date'] >= $rule['start_date_off_peak'] && $selected['end_date'] <= $rule['end_date_off_peak']
					&& ($selected['start_day'] >= 0 && $selected['end_day'] <= 5)
					&& ($selected['end_day'] != 6 && $selected['end_day'] != 0)

					||

					$selected['start_date'] >= $rule['collision_start_date'] && $selected['end_date'] <= $rule['collision_end_date']
					&& ($selected['start_day'] >= 0 && $selected['end_day'] <= 5)
					&& ($selected['end_day'] != 6 && $selected['end_day'] != 0)

					) {

					//print_r(':: PEAK MIDWEEK NiGHTS BOOKING, Mon to Thursday ::');
					$cost[] = self::rule_fallback( $selected, $weeks, $member );
					$RULE_MATCH = true;


					$debug = array( 'WEEKS LOOP' =>$weeks['weeks'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );
					self::add_debug_booking( '::PEAK MIDWEEK NiGHTS BOOKING, MON TO THURSDAY::', $debug );

					$temp_cost+= $weeks['days'];
				}
			}


			// CHECK IF PEAK WEEKEND BOOKING, fri, sat and sun. Booking rule ID is 117
			$rule = self::get_rule_static(117);
			$rule = self::season_collision_dates($rule);

			if( ($weeks['days']) <= 3 && $weeks['loop'] <= 1 )
			{
				if (
					$selected['start_date'] >= $rule['start_date'] && $selected['end_date'] <= $rule['end_date']
					&& ($selected['start_day'] == 5 || $selected['start_day'] == 6)
					&& ($selected['end_day'] == 6 || $selected['end_day'] == 0)
					
					||

					$selected['start_date'] >= $rule['start_date_off_peak'] && $selected['end_date'] <= $rule['end_date_off_peak']
					&& ($selected['start_day'] == 5 || $selected['start_day'] == 6)
					&& ($selected['end_day'] == 6 || $selected['end_day'] == 0)

					||

					$selected['start_date'] >= $rule['collision_start_date'] && $selected['end_date'] <= $rule['collision_end_date']
					&& ($selected['start_day'] == 5 || $selected['start_day'] == 6)
					&& ($selected['end_day'] == 6 || $selected['end_day'] == 0)

					) {
					
					$cost[] = self::cal_member_pricing_static($member, $selected, $rule);
					$RULE_MATCH = true;


					$debug = array( 'WEEKS LOOP' =>$weeks['weeks'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );
					self::add_debug_booking( '::WEEKEND BOOKING, FRI, SAT AND SUN::', $debug );

					//$temp_cost+= $weeks['days'];
				}
			}

			//$debug = array( 'WEEKS LOOP' =>$weeks['loop'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );
			//self::add_debug_booking( '::NO RULE MATCHED::', $debug );

		// IF NO RULES ARE MATCHED THEN CHARGE FALLBACK
		}


		// MULTIPLE WEEKS
		/*if( $weeks['weeks'] >= 1 && $weeks['loop'] > 0 )
		{
			$mw_cost = $md_cost = 0;

			$rule = self::get_rule_static(116);
			$cost[] = self::cal_member_pricing_static($member, $selected, $rule);

			$day_rule = self::get_rule_static(120);
			$cost_day[] = self::cal_member_pricing_static($member, $selected, $day_rule);

			$debug = array( 'cost weeks' => $cost, 'cost days' => $cost_day );
			self::add_debug_booking( '::MULTIPLE WEEKS UNITS::', $debug );

			foreach ($cost[0] as $c => $each_cost)
			{
				$mw_cost = $cost[0][$c]['cost'] * $weeks['weeks'];
				$md_cost = $cost_day[0][$c]['cost'] * $weeks['days'];
				$cost[0][$c]['cost'] = $mw_cost + $md_cost;
			}


			$RULE_MATCH = true;
			
			$debug = array( 'WEEKS LOOP' => $weeks['weeks'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );
			self::add_debug_booking( '::MULTIPLE WEEKS TOTAL::', $debug );
		}*/
		


	

		if( !$RULE_MATCH )
		{			
			$cost[] = self::rule_fallback( $selected, $weeks, $member );

			$debug = array( 'WEEKS LOOP' => $weeks['weeks'], 'DAYS LOOP' => $weeks['days'], 'cost' => $cost );
			self::add_debug_booking( '::FALLBACK::', $debug );
		}

		$events = self::get_events( $member, $selected );



		if( $temp_cost > 0 )
		{
			if( isset($cost[0][0]) )
			{
				foreach ($cost as $rk => $rv)
				{
					foreach ($rv as $rc => $rCost)
					{
						$cost[ $rk ][ $rc ]['cost'] = ($rCost['cost']*$temp_cost);
					}
				};
			}
		}


		$rule_costs = $cost;

		/*print_r(self::$cost_cache);
		print_r($rule_costs);*/


		// IF THERES AN EVENT GET PRICE OVERIDES FOR ALL GUESTS AND MEMBERS
		if( $events )
		{
				
				$event_cost = array('has_events' => 1);
				//$cost['has_events'] = 1;

				$members_have_no_events = array();

				foreach ($events as $e => $event)
				{

					/*$event_cost[] = array(
						'cost' => $event['price_overide'],
						'event_name' => $event['event_name'],
						'member' => $event['member']
						);*/

				
					foreach ($member as $m => $mbr)
					{
						if( $mbr['name'] == $event['member']['name'] )
						{
							$event_cost[$m] = array(
								'cost' => $event['price_overide'],
								'event_name' => $event['event_name'],
								'member' => $member[$m]
							);

							$member[$m]['hasevent'] = 1;

						}else{

							/*$event_cost[$m] = array(
								'cost' => self::$cost_cache[$m]['cost'],
								'event_name' => $event['event_name'],
								'member' => $member[$m]
							);*/
							//$member[$m]['hasevent'] = 0;
						}
					}



					/*$event_cost[] = array(
						'cost' => self::$cost_cache[$ri]['cost'],
						'event_name' => null,
						'member' => $mbr
						);
					print_r($mbr);*/
				}


				foreach ($member as $m => $mbr)
				{
					if( ! $mbr['hasevent'] )
					{
						$event_cost[$m] = array(
								'cost' => self::$cost_cache[$m]['cost'],
								'event_name' => $event['event_name'],
								'member' => $member[$m]
							);

						//print_r(self::$cost_cache);
					}
				}

				/*foreach ($member as $m => $mbr)
				{
					foreach ($rule_costs[0] as $ri => $mem_cost)
					{
						if( $mem_cost['member'] ==  $mbr['name'] )
						{
							$event_cost[$m] = array(
								'cost' => $mem_cost['cost'],
								'event_name' => null,
								'member' => $mbr
								);
						}
					}
				}*/

				self::add_debug_booking( '::EVENT DETECTED::', $event_cost );
				self::add_debug_booking( '::EVENT::', $event );


			$cost = $event_cost;
		}

		//print_r($event_cost);

		return $cost;
	}


	public static function cal_member_pricing_static($member, $selected, $rule) 
	{
		self::$cost_cache = array();

		$season 	= '';

		$season 	= self::get_season_peak( $rule, $selected );

		self::add_debug_booking( '::SELECTED::', $selected );
		self::add_debug_booking( '::SEASON::', ($season == '') ? 'PEAK' : $season );

		$event = self::get_events( $member, $selected );

		
		foreach ($member as $index => $m)
		{
			$cost = 0;

			if( !empty($event) )
			{
				foreach ($event as $e => $evnt)
				{
					$evnt['member'] = $m['name'];

					$cost += $evnt['price_overide'];

					self::$cost_cache[$index] = array( 'cost' => $cost, 'member' => $m['name'], 'hasevent' => 1 );

					continue;
				}
			}


			$cost 		= $rule['surcharge'];

			// if debenture number valid, use debenture price
			if ($m['member_type'] == 'debenture') {
				$cost += $rule['member_price'.$season];
				
			}
			// if aac, use aac prices
			elseif ($m['member_type'] == 'aac') {
				$cost += $rule['reciprocal_price'.$season];
			}
			// else use guest pricing
			else {
				$cost += $rule['guest_price'.$season];
			}

			self::$cost_cache[$index] = array( 'cost' => $cost, 'member' => $m['name'], 'hasevent' => 0 );
		}
		

		return self::$cost_cache;
	}


	private static function get_season_peak( $rule, $selected )
	{
		$off_peak_date_start 	= strtotime($rule['start_date_off_peak']);
		$off_peak_date_end  	= strtotime($rule['end_date_off_peak']);

		$peak_date_start 		= strtotime($rule['start_date']);
		$peak_date_end  		= strtotime($rule['end_date']);

		$selected_date_start 	= strtotime($selected['start_date']);
		$selected_date_end  	= strtotime($selected['end_date']);

		if (
		  $selected_date_start >= $off_peak_date_start && 
		  $selected_date_end <= $off_peak_date_end
		  ){
		  	return '_off_peak';
		}

		if (
		  $selected_date_start >= $peak_date_start && 
		  $selected_date_end <= $peak_date_end
		  ){
		  	return '';
		}


		return '';
	}

	public static function set_booking_rule(){}


	private static function rule_fallback( $selected, $weeks, $member )
	{
		$costs 			= array();

		$cost 			= 0;

		$weekday_cost 	= 0;

		$weekend_cost 	= 0;

		$weekend_total 	= 0;

		$weekday_total 	= 0;

		$period 		= self::date_range( str_replace('/','-',$_SESSION['from']), str_replace('/','-',$_SESSION['to']) );

		
		
		// IF MORE THAN A WEEK
		if( $weeks['weeks'] >= 0 )
		{
			$start_point = $mid_point = $end_point = array();
			$period_cache = $period;
			
			// start point
			foreach ($period as $i => $pos)
			{
				if( $pos['pos'] != 5 )
				{
					$start_point[] = $pos;
					unset( $period_cache[$i] );
				}

				if( $pos['pos'] == 5 )
				{
					break;
				}
			}

			$period_cache = array_values($period_cache);

			// mid point
			$mid_point = (int) ( (sizeof($period_cache)-1) / 7 );
			
			// end point
			$remainder = ( (sizeof($period_cache)-1) % 7 );
			$end_point = array_slice($period_cache, -$remainder, $remainder, false);
			if( !empty($end_point) && sizeof($end_point) > 1 ) array_pop($end_point);

			if( $weeks['weeks'] == 0 && sizeof($start_point) > 1 && empty($end_point) ) array_pop($start_point);

			// total cost
			$mid_cost_arr 	= self::cal_member_pricing_static($member, $selected, self::get_rule_static(116) );
			$mid_cost = 0;
			foreach ($mid_cost_arr as $key => $mem)
			{
				$mid_cost += $mem['cost'] * $mid_point;
			}
			
			$start_cost = self::fall_business_rules( $start_point, $member );
			$end_cost 	= self::fall_business_rules( $end_point, $member );
			

			$total = $start_cost + $mid_cost + $end_cost;
			$costs[] = array( 'cost' => $total, 'member' => $member );

			self::add_debug_booking( 'START POINT' , $start_cost );
			self::add_debug_booking( 'MID POINT' , $mid_cost);
			self::add_debug_booking( 'END POINT' , $end_cost );

			return $costs;
		}



		// ONLY IF LESS THAN A WEEK
		if( $weeks['weeks'] <= 1 && $weeks['days'] <= 1 )
		{
			foreach ($period as $p => $day)
			{	
				if( $day['pos'] == 5 || $day['pos'] == 6 )
				{
					$weekend_total++;

				}else{

					$weekday_total++;
				}
			};

			$weekend_total_cache = $weekend_total;

			$weekend_total = ( ($weekend_total/2) > 1 ) ? ($weekend_total/2) : 1;
			$weekend_total = ( $weekend_total_cache <= 0 ) ? 0 : $weekend_total;


			$weekday_cost = self::cal_member_pricing_static($member, $selected, self::get_rule_static(120));

			$weekend_cost = self::cal_member_pricing_static($member, $selected, self::get_rule_static(117));


			foreach( $weekday_cost as $w => $day_cost )
			{
				$wday_cost = $weekday_total * $day_cost['cost'];

				$weekday_cost[$w]['cost'] = $wday_cost;
			}


			$wend_cost = '';

			foreach( $weekend_cost as $w => $day_cost )
			{
				$wend_cost = $weekend_total * $day_cost['cost'];

				$weekend_cost[$w]['cost'] = $wend_cost;

				$costs[] = array( 'cost' => $weekday_cost[$w]['cost'] + $weekend_cost[$w]['cost'], 'member' => $day_cost['member'] );
			}

		}


		// debug output
		self::add_debug_booking( 'Weekday rule', self::get_rule_static(120) );
		self::add_debug_booking( 'Weekday duration', $weekday_total );
		self::add_debug_booking( 'Weekday', $weekday_cost );
		
		self::add_debug_booking( 'Weekend rule', self::get_rule_static(117) );
		self::add_debug_booking( 'Weekend duration', $weekend_total );
		self::add_debug_booking( 'Weekend', $weekend_cost );

		self::add_debug_booking( 'Total', $costs );
		//

		return $costs;
	}

	private static function fall_business_rules( $pos, $member )
	{
		if( empty($pos) ) return 0;

		$booking_cache = $pos;

		$cost = 0;

		$selected['start_date'] = $pos[0]['date'];
		$selected['end_date'] = end($pos)['date'];
		$selected['start_day'] = $pos[0]['pos'];
		$selected['end_day'] = end($pos)['pos'];




		// CHECK START DAY IS fri. Booking rule ID is 117
		// FRIDAY
		$WEEKEND_RULE = self::get_rule_static(117);
		$WEEKEND_RULE = self::season_collision_dates($WEEKEND_RULE);
		$WEEK_END_COST = 0;
		if( $selected['start_day'] == 5 )
		{
			$WEEK_END_C = self::cal_member_pricing_static($member, $selected, $WEEKEND_RULE);
			$WEEK_END_C = ( !empty($WEEK_END_C) ) ? $WEEK_END_C : array();
			//$WEEK_END_COST += $WEEK_END_C[0]['cost'];
			foreach ($WEEK_END_C as $key => $DAY)
			{
				$WEEK_END_COST += $WEEK_END_C[$key]['cost'];
			}
			unset($booking_cache[0], $booking_cache[1]);
		}

		// SATURDAY
		if( $selected['start_day'] == 6 )
		{
			$WEEK_END_C = self::cal_member_pricing_static($member, $selected, $WEEKEND_RULE);
			$WEEK_END_C = ( !empty($WEEK_END_C) ) ? $WEEK_END_C : array();
			//$WEEK_END_COST += $WEEK_END_C[0]['cost'];
			foreach ($WEEK_END_C as $key => $DAY)
			{
				$WEEK_END_COST += $WEEK_END_C[$key]['cost'];
			}
			unset($booking_cache[0]);
		}




		// CHECK IF PEAK MIDWEEK NiGHTS BOOKING, Mon to Thursday. Booking rule ID is 120
		// WEEKDAYS
		$WEEKDAY_RULE = self::get_rule_static(120);
		$WEEKDAY_RULE = self::season_collision_dates($WEEKDAY_RULE);
		$WEEKDAY_COST = 0;
		if( !empty($booking_cache) )
		{
			foreach ($booking_cache as $b => $date)
			{
				$WEEKDAY_C = self::cal_member_pricing_static($member, $selected, $WEEKDAY_RULE);
				$WEEKDAY_C = ( !empty($WEEKDAY_C) ) ? $WEEKDAY_C : array();
				
				
				foreach ($WEEKDAY_C as $key => $DAY)
				{
					$WEEKDAY_COST += $WEEKDAY_C[$key]['cost'];
				}
					//$WEEKDAY_COST += $WEEKDAY_C[0]['cost'];
					//$costs[] = array( 'week_days' => $weekday_cost[$w]['cost'] + $weekend_cost[$w]['cost'], 'member' => $day_cost['member'] );
			}

		}




		$cost = $WEEKDAY_COST + $WEEK_END_COST;
		
		return $cost;
	}




	private static function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y' ) {

	    $dates = array();
	    $current = strtotime($first);
	    $last = strtotime($last);

	    while( $current <= $last ) {

	        $dates[] = array('pos'=> (date('w', $current)), 'date' => date('d-m-Y', $current) );
	        $current = strtotime($step, $current);
	    }

	    //unset( $dates[ (sizeof($dates)-1) ] );

	    return $dates;
	}


	public static function get_events( $members, $selected )
	{
		if( empty($members) ){
			return;
		}

		//print_r($member);

		//$member 		= $member[0];

		$rule 			= FALSE;


		$args = array(
			'posts_per_page'   	=> 500,
			'offset'           	=> 0,
			'orderby'          	=> 'date',
			'order'           	=> 'DESC',
			'post_type'        	=> 'booking_events',
			'post_status'      	=> 'publish',
			'suppress_filters' 	=> true
		);


		foreach (get_posts( $args ) as $e => $event)
		{
			$event_start_date 	= get_post( $event->ID )->from_start_date;

			$event_end_date 	= get_post( $event->ID )->to_end_date;

			/*print_r($selected['start_date']);
			echo ' | ';
			print_r($event_start_date);
			echo ' | ';*/

			// IF THERES AN EVENT WITHIN THE MEMBERS SELECTED DATES GET THE OVERIDE PRICE FOR THE STAY
			if ($selected['start_date'] >= $event_start_date && $selected['end_date'] <= $event_end_date )
			{
				foreach ($members as $m => $member)
				{
					if( $member['member_type'] == 'debenture' )
					{
						$prefix = 'member_price_';
					}

					if( $member['member_type'] == 'aac' )
					{
						$prefix = 'reciprocal_price_';
					}

					if( $member['member_type'] == '' )
					{
						$prefix = 'guest_price_';
					}

					$member_age = strtolower(str_replace(' ', '_', $member['age']));

					$price_overide = get_post_meta( $event->ID, $prefix.$member_age );

					$surcharge = get_post_meta( $event->ID, 'surcharge' );


					//$price_overide = ( isset($price_overide[0]) && (!empty($price_overide[0]) || $price_overide[0] == 0) ) ? $price_overide[0] : '';

					if( isset($price_overide[0]) && (!empty($price_overide[0]) || $price_overide[0] == 0) )
					{
						if( $price_overide[0] != '' )
						{
							if( isset($surcharge[0]) )
							{
								$price_overide[0] += $surcharge[0];
							}

							$rule[] = array( 
								'price_overide' => $price_overide[0],
								'event_name' => get_post($event->ID)->post_title,
								'member' => $member
								);
						}else{

							//print_r($member);
						}
					}

				}



				/*$rule = array(
						
						'event_is_on' => $EVENT_IS_ON,
						'start_date' => get_post( $event->ID )->from_start_date,
						'end_date' => get_post( $event->ID )->to_end_date,

						$prefix.$member_age => $price_overide
					);*/
			}
		}

		//print_r($rule);
		
		return $rule;
	}


	public static function get_rule_static($id)
	{
		$rule = array();
		$rule['start_date'] = get_post($id)->from_start_date;
		$rule['end_date'] = get_post($id)->to_end_date;
		$rule['start_date_off_peak'] = get_post($id)->from_start_date_off_peak;
		$rule['end_date_off_peak'] = get_post($id)->to_end_date_off_peak;
		$rule['surcharge'] = get_field('surcharge', $id);
		
		$rule['member_price'] = get_field('member_price', $id);
		$rule['reciprocal_price'] = get_field('reciprocal_price', $id);
		$rule['guest_price'] = get_field('guest_price', $id);

		$rule['member_price_off_peak'] = get_field('member_price_off_peak', $id);
		$rule['reciprocal_price_off_peak'] = get_field('reciprocal_price_off_peak', $id);
		$rule['guest_price_off_peak'] = get_field('guest_price_off_peak', $id);
		
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
	public static function get_member_details( $NO_AJAX = null )
	{
		if( isset($_POST['member_id']) && empty($_POST['member_id']) )
		{
			echo json_encode( array('success' => '0', 'error' => 'Please enter a membership #' ), true);

			exit;
		}

		$memberCache = array();
		$memberAdded = false;

		if( isset($_POST['members_added']) && !empty($_POST['members_added']) )
		{
			$memberCache = explode(',', $_POST['members_added']);

			$memberCache = array_filter($memberCache);
		}


		global $wpdb;

		$returnObj 		= array();

		$members 		= get_users( 'role=customer&member_type=debenture' );

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
									if( in_array($meta[0], $memberCache) )
									{
										$returnObj = array( 'success' => '0', 'error' => 'Member already added' );

										echo json_encode( $returnObj, true);

										exit;
									}

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

			$returnObj = array( 'success' => '0', 'error' => 'No member found' );
		}


		if( !is_null($NO_AJAX) && $NO_AJAX == 'NO_AJAX' ){
			return $returnObj;
		}

		echo json_encode( $returnObj, true);

		exit;
	}


	function get_product_availability()
	{
	    global $product, $post, $wpdb;

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


		// Add all resource quantities of this product together
		$qtyResources = 0;

		/*foreach ( $resources as $resource )
		{
		  $qtyResources += get_post_meta($resource->ID, 'qty', true);
		}*/

		// get the number of bookings for this product on the start date
		/*$bookedPosts = get_posts(array(
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
		);*/
		$booking_statuses = array(
			'unpaid',
			'pending-confirmation',
			'confirmed',
			'paid',
			'complete'
		);

		$bookedPosts = get_posts( array(
			'post_type'     => 'wc_booking',
			'post_status'   => $booking_statuses,
			'no_found_rows' => true,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_booking_product_id',
					'value'   => $product->id,
					'compare' => '=',
				),
				array(
					'key'     => '_booking_end',
					'value'   => implode('', array_reverse(explode('/', $start_date))),
					'compare' => '>',
				),
				array(
					'key'     => '_booking_start',
					'value'   => implode('', array_reverse(explode('/', $end_date))),
					'compare' => '<',
				),

			)
		) );

		foreach ($bookedPosts as $res => $booking)
		{
			$sql = "SELECT *

							FROM wp_woocommerce_order_items as item_meta

								INNER JOIN wp_woocommerce_order_itemmeta as items

									ON item_meta.`order_item_id` = items.`order_item_id`

								WHERE item_meta.order_id = '".$booking->post_parent."'

									AND(
										meta_value LIKE '%single_%'
										OR
										meta_value LIKE '%double_%'
										OR
										meta_value LIKE '%bunk_%'
									)

							;";

			$bookedPosts = $wpdb->get_results( $sql );

			//print_r( $bookedPosts );
		}


		$productBookings = count($bookedPosts);

		// If total resource quantity is equal to confirmed bookings then it's fully booked
		/*if ($qtyResources <= $productBookings) {
			$fullyBooked = true;
		} else {
			$fullyBooked = false;
		}*/

		//return $fullyBooked;
	}


	public static function psl_get_product_beds()
	{
		$beds = array();

		$beds['single_bed'] = get_post_meta( $_POST['product_id'], '_wc_booking_single_bed', true );
		$beds['double_bed'] = get_post_meta( $_POST['product_id'], '_wc_booking_double_bed', true );
		$beds['bunk_bed'] 	= get_post_meta( $_POST['product_id'], '_wc_booking_bunk_bed', true );

		foreach ($beds as $key => $value)
		{
			if(!$value)
			{
				unset( $beds[$key] );
			}
		}

		wp_send_json( array('success' => 1, 'beds' => $beds) );
    	wp_die();
	}


	private static function add_debug_booking( $key, $str )
	{
		self::$booking_debug[][$key] = $str;

		//print_r(self::$booking_debug);
	}

	private static function get_debug_booking()
	{
		if( self::$DEBUG_ON && ( isset($_POST['action']) && $_POST['action'] == 'psl_bookings_calculate_costs' ) )
		{
			print_r(self::$booking_debug);
		}
	}
}
