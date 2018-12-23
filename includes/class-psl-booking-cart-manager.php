<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * PSL_Booking_Cart_Manager class.
 */
class PSL_Booking_Cart_Manager {

	/**
	 * Constructor
	 */
	public function __construct() {
		// remove existing hook from current library
		remove_filters_for_anonymous_class('woocommerce_add_to_cart_validation', 'WC_Booking_Cart_Manager', 'validate_add_cart_item', 10);
		remove_filters_for_anonymous_class('woocommerce_booking_add_to_cart', 'WC_Booking_Cart_Manager', 'add_to_cart', 10);
		remove_filters_for_anonymous_class('woocommerce_add_cart_item', 'WC_Booking_Cart_Manager', 'add_cart_item', 10);
		remove_filters_for_anonymous_class('woocommerce_add_cart_item_data', 'WC_Booking_Cart_Manager', 'add_cart_item_data', 10);
		remove_filters_for_anonymous_class('woocommerce_get_item_data', 'WC_Booking_Cart_Manager', 'get_item_data', 10);
		remove_filters_for_anonymous_class('woocommerce_add_order_item_meta', 'WC_Booking_Cart_Manager', 'order_item_meta', 50);
		
		// use own logic
		add_action( 'woocommerce_booking_add_to_cart', array( $this, 'add_to_cart' ), 30 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 1 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'order_item_meta' ), 50, 2 );
	}

	/**
	 * Add to cart for bookings
	 */
	public function add_to_cart() {
		global $product;

		// Prepare form
		$booking_form = new PSL_Booking_Form( $product );

		// Get template
		woocommerce_get_template( 'single-product/add-to-cart/booking.php', array( 'booking_form' => $booking_form ), 'woocommerce-bookings', WC_BOOKINGS_TEMPLATE_PATH );
	}

	/**
	 * Put meta data into format which can be displayed
	 *
	 * @param mixed $other_data
	 * @param mixed $cart_item
	 * @return array meta
	 */
	public function get_item_data( $other_data, $cart_item )
	{
		//print_r($cart_item);

		global $bed_types;

		if ( ! empty( $cart_item['booking'] ) ) {

			// get start date
			$other_data[] = array(
				'name'    => 'Check-in Date',
				'value'   => date('d/m/Y',$cart_item['booking']['_start_date']),
				'display' => ''
			);

			// get end date
			$other_data[] = array(
				'name'    => 'Check-out Date',
				'value'   => date('d/m/Y',$cart_item['booking']['_end_date']),
				'display' => ''
			);

			// get duration
			$other_data[] = array(
				'name'    => 'Duration',
				'value'   => $cart_item['booking']['duration'],
				'display' => ''
			);

			// get duration
			/*$other_data[] = array(
				'name'    => 'Account Number',
				'value'   => $cart_item['booking']['_account_number'],
				'display' => ''
			);*/

			// we need to sort out the beds logic here
			// if a bed is not empty, we nede to display the details
			foreach ($bed_types as $k => $v) {
				if (!empty($cart_item['booking'][$k])) {
					foreach ($cart_item['booking'][$k] as $product_no => $product_val) {
						foreach ($product_val as $bed_key => $bed_val) {
							foreach($bed_val as $person_key => $person_val) {
								
								$other_data[] = array(
								'name'    => $v.' '.$bed_key,
								'value'   => Serialize( array( 'name' => $person_val['name'], 'age' => $this->get_age($person_val['name']), 'gender' => $this->get_gender($person_val['name']) ) ),
								'display' => $person_val['name']
								);
							}
						}
					}
				}
			}
		}

		return $other_data;
	}

	/**
	 * return age based on username
	 * 
	 * @param  [type] $member [description]
	 * @return [type]         [description]
	 */
	public function get_age($name) {
		foreach ($_SESSION['member'] as $v) {
			if ($v['name'] == $name) {
				return $v['age'];
			}
		}
	}


	/**
	 * return gender based on username
	 * 
	 * @param  [type] $member [description]
	 * @return [type]         [description]
	 */
	public function get_gender($name) {
		foreach ($_SESSION['member'] as $v) {
			if ($v['name'] == $name) {
				if( isset($v['gender']) ){
					return $v['gender'];
				}else{
					return '';
				}
			}
		}
	}


	/**
	 * return gender based on username
	 * 
	 * @param  [type] $member [description]
	 * @return [type]         [description]
	 */
	public function get_session_value($name, $value) {
		foreach ($_SESSION['member'] as $v) {
			if ($v['name'] == $name) {
				if( isset($v[ $value ]) ){
					return $v[ $value ];
				}else{
					return '';
				}
			}
		}
	}


	/**
	 * find if member is in cart
	 * 
	 * @param string $member
	 * @return boolean
	 */
	public static function is_member_in_cart($name)
	{
		global $bed_types;
		$cart = WC()->cart->get_cart();
		foreach ($cart as $val) {
			foreach ($bed_types as $k => $v) {
				if (isset($val['booking'][$k])) {
					foreach ($val['booking'][$k] as $product_no => $product_val) {
						foreach ($product_val as $bed_no => $bed_val) {
							foreach ($bed_val as $person_no => $person_val) {
								if ($person_val['name'] == $name) {
									return true;
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * add order item meta upon "add to cart"
	 *
	 * @param mixed $item_id
	 * @param mixed $values
	 */
	public function order_item_meta( $item_id, $values ) {
		global $wpdb, $bed_types;

		if ( ! empty( $values['booking'] ) ) {
			$product        = $values['data'];
			$booking_id     = $values['booking']['_booking_id'];
			$booking        = get_wc_booking( $booking_id );
			$booking_status = 'unpaid';
			$order_id       = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $item_id ) );

			// Set as pending when the booking requires confirmation
			if ( wc_booking_requires_confirmation( $values['product_id'] ) ) {
				$booking_status = 'pending-confirmation';
			}

			if ( ! $booking ) {
				$booking = $this->create_booking_from_cart_data( $cart_item_meta, $product->id );
			}

			$booking->set_order_id( $order_id, $item_id );

			// Add summary of details to line item
			
			// add start date
			wc_add_order_item_meta( $item_id, 'Check-in Date', date('d/m/Y',$values['booking']['_start_date']) );

			// add end date
			wc_add_order_item_meta( $item_id, 'Check-out Date', date('d/m/Y',$values['booking']['_end_date']) );

			// add duration
			wc_add_order_item_meta( $item_id, 'Duration', $values['booking']['duration'] );

			// add duration
			//wc_add_order_item_meta( $item_id, 'Account Number', $values['booking']['_account_number'] );
			
			// we need to sort out the beds logic here
			// if a bed is not empty, we need to display the details
			foreach ($bed_types as $k => $v) {
				if (!empty($values['booking'][$k])) {
					foreach ($values['booking'][$k] as $product_no => $product_val) {
						foreach ($product_val as $bed_key => $bed_val) {
							foreach($bed_val as $person_key => $person_val) {
								//wc_add_order_item_meta( $item_id, $k.'_'.$bed_key, $person_val['name'].' (test)' );
								$sData = array( 
												'name' 				=> $person_val['name'], 
												//'account_number' 	=> $this->get_session_value( $person_val['name'], 'account_number' ),
												'member_type' 		=> $this->get_session_value( $person_val['name'], 'member_type' ), 
												'age' 				=> $this->get_age($person_val['name']), 
												'gender' 			=> $this->get_gender($person_val['name']), 
												'bed' 				=> $k.'_'.$bed_key
											);
								//$sData = base64_encode(serialize($sData));
								wc_add_order_item_meta( $item_id, $k.'_'.$bed_key, serialize($sData) );
								//wc_add_order_item_meta( $item_id, 'member_details', $sData );
								//
							}
						}
					}
				}
			}

			wc_add_order_item_meta( $item_id, __( 'Booking ID', 'woocommerce-bookings' ), $values['booking']['_booking_id'] );

			// Update status
			$booking->update_status( $booking_status );
		}
	}

	/**
	 * Adjust the price of the booking product based on booking properties
	 *
	 * @param mixed $cart_item
	 * @return array cart item
	 */
	public function add_cart_item( $cart_item )
	{
		if( isset($_SESSION['is_admin_checkout']) ){
			$cart_item['booking']['_cost'] = 0;
		}

		if ( ! empty( $cart_item['booking'] ) && ! empty( $cart_item['booking']['_cost'] ) ) {
			$cart_item['data']->set_price( $cart_item['booking']['_cost'] );
		}
		return $cart_item;
	}

	/**
	 * Schedule booking to be deleted if inactive
	 */
	public function schedule_cart_removal( $booking_id ) {
		wp_clear_scheduled_hook( 'wc-booking-remove-inactive-cart', array( $booking_id ) );
		wp_schedule_single_event( apply_filters( 'woocommerce_bookings_remove_inactive_cart_time', time() + ( 60 * 15 ) ), 'wc-booking-remove-inactive-cart', array( $booking_id ) );
	}

	/**
	 * Add posted data to the cart item
	 *
	 * @param mixed $cart_item_meta
	 * @param mixed $product_id
	 * @return void
	 */
	public function add_cart_item_data( $cart_item_meta, $product_id ) {

		//print_r($cart_item_meta);

		$product = get_product( $product_id );

		if ( 'booking' !== $product->product_type ) {
			return $cart_item_meta;
		}

		$booking_form                       = new PSL_Booking_Form( $product );
		$cart_item_meta['booking']          = $booking_form->get_posted_data( $_POST );

		
		$selected = $booking_form->get_user_selected_settings();
		
		// $POST is wrong here, need to compare with the post from parse_str( $_POST['form'], $posted );
		//$cost = $booking_form->calculate_booking_cost( $_POST, $selected );
		$cost = $booking_form->get_booking_rule( $_POST, $selected );

		/*echo '<pre>';
		print_r( $cost );
		echo '</pre>';*/
		//exit;

		//( isset($cost['has_events']) ) ? $cost = $cost : $cost = $cost[0];


		$add_up_cost = '';

		if( !isset($cost['has_events']) )
		{	
			// NO EVENT REGULAR BOOKING
			foreach ($cost as $key => $each_cost)
			{
				foreach ($each_cost as $ec => $each)
				{
					$add_up_cost += $each['cost'];
				}
			};

		}else{

			// HAS EVENT - BOOK AS EVENT
			foreach ($cost as $key => $each_cost)
			{
				$add_up_cost += $each_cost['cost'];
			};
		}
		


		$cart_item_meta['booking']['_cost'] = $add_up_cost;

		// Create the new booking
		$new_booking = $this->create_booking_from_cart_data( $cart_item_meta, $product_id );

		// Store in cart
		$cart_item_meta['booking']['_booking_id'] = $new_booking->id;

		// Schedule this item to be removed from the cart if the user is inactive
		$this->schedule_cart_removal( $new_booking->id );

		return $cart_item_meta;
	}

	/**
	 * Create booking from cart data
	 */
	private function create_booking_from_cart_data( $cart_item_meta, $product_id, $status = 'in-cart' ) {
		// Create the new booking
		$new_booking_data = array(
			'product_id'    => $product_id, // Booking ID
			'cost'          => $cart_item_meta['booking']['_cost'], // Cost of this booking
			'start_date'    => $cart_item_meta['booking']['_start_date'],
			'end_date'      => $cart_item_meta['booking']['_end_date'],
			'all_day'       => $cart_item_meta['booking']['_all_day']
		);

		// Check if the booking has resources
		if ( isset( $cart_item_meta['booking']['_resource_id'] ) ) {
			$new_booking_data['resource_id'] = $cart_item_meta['booking']['_resource_id']; // ID of the resource
		}

		// Checks if the booking allows persons
		if ( isset( $cart_item_meta['booking']['_persons'] ) ) {
			$new_booking_data['persons'] = $cart_item_meta['booking']['_persons']; // Count of persons making booking
		}

		$new_booking = get_wc_booking( $new_booking_data );
		$new_booking->create( $status );

		return $new_booking;
	}
	
}

new PSL_Booking_Cart_Manager();
