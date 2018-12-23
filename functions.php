<?php

// start session
session_start();
// session_destroy();

// define bed types.
global $bed_types, $age_types;

// GLOBAL VAR
$bed_types = array('single' => 'Single Bed', 'double' => 'Double Bed', 'bunk' => 'Bunk Bed');

// If you change the age here, remember to change it in ACF group settings as well!
$age_types = array('Above 16', '5 to 16', 'Under 5');

// declare woocommerce support. This is the new wocommerce standard.
add_action( 'after_setup_theme', 'woocommerce_support' );

function woocommerce_support() {
    add_theme_support( 'woocommerce');
}



require_once('includes/tac_functions.php');

// if woocommerce-bookings is turned on
if ( in_array( 'woocommerce-bookings/woocommmerce-bookings.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
{
	// add psl custom fields in backend
	require_once('includes/psl_meta_boxes.php');
	// init booking wizard
	require_once('includes/bookingWizard.php');
	// init shortcodes
	require_once('includes/shortcodes.php');
	// include psl booking form class.
	require_once('includes/class-psl-booking-form.php');
	// include psl custom cart manager
	require_once('includes/class-psl-booking-cart-manager.php');
	// process psl form submission logic.
	require_once('includes/process-psl-form-logic.php');
	// incl. all logic in my-account
	require_once('includes/my-account.php');

	//$result = add_role( 'accounts', __('Accounts') );
}


if( isset($_SESSION['is_admin_checkout']) )
{
	function unset_unwanted_checkout_fields( $fields )
	{ 
	    // list of the billing field keys to remove
	    $billing_keys = array(
	        'billing_company',
	        'billing_phone',
	        'billing_address_1',
	        'billing_address_2',
	        'billing_city',
	        'billing_postcode',
	        'billing_country',
	        'billing_state',
	    );

	    // unset each of those unwanted fields
	    foreach( $billing_keys as $key ) {
	        unset( $fields['billing'][$key] );
	    }
	    
	    return $fields;
	}
	add_filter( 'woocommerce_checkout_fields', 'unset_unwanted_checkout_fields' );
	add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}

function clear_sessions_on_logout()
{
    if( isset($_SESSION['is_admin_checkout']) ){ unset($_SESSION['is_admin_checkout']); }
    if( isset($_SESSION['member']) ) { unset($_SESSION['member']); }
}
add_action('wp_logout', 'clear_sessions_on_logout');



if (basename($_SERVER['SCRIPT_NAME']) == 'edit.php')
{
    if( isset($_GET['post_type']) && $_GET['post_type'] == 'importmemberscsv' ){
    	require_once('includes/tac_import_members/tac_import_members.php');
    	$tac_import_members = new tac_import_members;
    }

    // HOOK INTO CREATE NEW BOOKING / ADMIN SECTION WITHOUT ANY HOOKS
    if( (isset($_GET['post_type']) && $_GET['post_type'] == 'wc_booking') &&
    	(isset($_GET['page']) && $_GET['page'] == 'create_booking')
    	)
    {
    	wp_safe_redirect( get_site_url().'/add-members/' );
    	exit;

    	/*if( isset($_POST['create_booking']) && $_POST['create_booking'] == 'Next' )
    	{
    		if( isset($_POST['bookable_product_id']) && !empty($_POST['bookable_product_id']) )
    		{
    			echo '<script>var global_product_beds = "'.$_POST['product_beds'].'";</script>';
    		}

    	}*/



    	/*if( isset($_POST['create_booking_2']) && $_POST['create_booking_2'] == 'Create Booking' )
    	{
    		if( isset($_POST['product_beds']) && !empty($_POST['product_beds']) )
    		{
    			$last = $wpdb->get_row("SHOW TABLE STATUS LIKE 'wp_posts'");
				$order_id = $last->Auto_increment;

				$product = wc_get_product($_POST['bookable_product_id']);

				//$order = wc_get_order($order_id)->add_product($product, $quantity);


    		}
    	}*/
    }
}


/**
 * Redirect users to custom URL based on their role after login
 *
 * @param string $redirect
 * @param object $user
 * @return string
 */
function wc_custom_user_redirect( $redirect, $user )
{
	if(!isset($user->roles) || empty($user->roles)) {
		wp_logout();
		$redirect = home_url();
	}
	else {

		$role = strtolower($user->roles[key($user->roles)]);

		$dashboard = admin_url();

		$myaccount = get_permalink(wc_get_page_id('myaccount')) . 'add-members/';

		if ($role == 'administrator') {
			//Redirect administrators to the dashboard
			$redirect = $dashboard;
		} elseif ($role == 'shop-manager') {
			//Redirect shop managers to the dashboard
			$redirect = $dashboard;
		} elseif ($role == 'editor') {
			//Redirect editors to the dashboard
			$redirect = $dashboard;
		} elseif ($role == 'author') {
			//Redirect authors to the dashboard
			$redirect = $dashboard;
		} elseif ($role == 'customer' || $role == 'subscriber') {
			//Redirect customers and subscribers to the "My Account" page
			$redirect = $myaccount;
		} else {
			//Redirect any other role to the previous visited page or, if not available, to the home
			$redirect = wp_get_referer() ? wp_get_referer() : home_url();
		}
	}

	return $redirect;
}

add_filter( 'woocommerce_login_redirect', 'wc_custom_user_redirect', 10, 2 );



// use our own cost calculation logic
add_action( 'wp_ajax_psl_bookings_calculate_costs', 		'PSL_Booking_Form::psl_calculate_costs_json' );
add_action( 'wp_ajax_nopriv_psl_bookings_calculate_costs', 	'PSL_Booking_Form::psl_calculate_costs_json' );
add_action( 'wp_ajax_get_member_details', 					'PSL_Booking_Form::get_member_details' );
add_action( 'wp_ajax_psl_set_booking_rule', 				'PSL_Booking_Form::set_booking_rule' );


add_action( 'wp_ajax_psl_get_product_beds', 		'PSL_Booking_Form::psl_get_product_beds' );
add_action( 'wp_ajax_nopriv_psl_get_product_beds', 	'PSL_Booking_Form::psl_get_product_beds' );



// REMOVE COLOR SCHEME SELECTOR
remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
// TRANSFER MEMBERS
add_action( 'edit_user_profile', 'transfer_member_banner' );
add_action( 'profile_update', 'transfer_member_update', 10, 2 );
add_action( 'user_register', 'transfer_member_save' );
add_action( 'user_new_form', 'transfer_member_init' );

function transfer_member_banner( $user )
{
	$member_data = get_user_meta( $user->ID );

	echo '
		<div class="updated">
			<h3>Transfer Member Debenture Numbers</h3>
			<p>
				Transfer member\'s debenture numbers to another member
				<a href="/wp-admin/admin.php?page=debenture-number-transfer&original_member='.$user->ID.'">Transfer now</a>
			</p>
		</div>
	';
}

function transfer_member_update( $user_id, $old_user_data )
{
	//print_r($user_id);
}

function transfer_member_init( $user_id )
{
	global $wpdb;

	$member_meta = get_user_meta( $_GET['member-transfer'] );

	$account_ids 		= array();

	// CHECK IF THE CURRENT LOGGED IN MEMBER HAS MULTIPLE ACCOUNTS
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
						array_push($account_ids, $v);
					}
				}
			}
		}
	}

	foreach( $account_ids as $index => $value )
	{
		$field_id = 
		$wpdb->get_var("
	        SELECT post_name
	        FROM $wpdb->posts
	        WHERE post_type='acf-field' AND post_excerpt='account_number_".($index+1)."'
	    ;");


	    $field_id = '#acf-'.$field_id;

		echo "
			<script>
			jQuery(function($){
		    	$('".$field_id."').val( '".$value."' ).attr('disabled', 'disabled');
		    	$('".$field_id."').parent().append('<input type=\"checkbox\" name=\"acf-id-transfer[account_number_".($index+1)."]\">');
			});
			</script>";
	}

}


function transfer_member_save( $user_id )
{
	global $wpdb;

	if( isset($_GET['member-transfer']) && !empty($_GET['member-transfer']) )
	{
		if( isset($_POST['acf-id-transfer']) && !empty($_POST['acf-id-transfer']) )
		{
			foreach ($_POST['acf-id-transfer'] as $key => $on)
			{
				$wpdb->update(
				    'wp_usermeta',
				    	array( 'user_id' => $user_id ),
				    	array( 'meta_key' => $key, 'user_id' => $_GET['member-transfer'] )
				);
			}


			// CHECK IF THE OLD MEMBER HAS GIVEN UP ALL THERE ACCOUNTS
			$member_meta = get_user_meta( $_GET['member-transfer'] );

			$account_ids 		= array();

			// CHECK IF THE CURRENT LOGGED IN MEMBER HAS MULTIPLE ACCOUNTS
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
								array_push($account_ids, $v);
							}
						}
					}
				}
			}



			if( !$account_ids )
			{
				$old_member = new WP_User( $_GET['member-transfer'] );
	
				if ( !empty( $old_member->roles ) && is_array( $old_member->roles ) ) {
					//print_r($old_member->roles);
					$old_member->remove_role( 'subscriber' );
					$old_member->add_role( 'account disabeled' );
				}
			}
			
		}

	}
}

// redirect credit card purchases
function wc_custom_redirect_after_purchase()
{
	global $wp;

	global $wpdb;

	if ( is_checkout() && ! empty( $wp->query_vars['order-received'] ) )
	{
		if( $wp->query_vars['pagename'] != 'checkout' && isset($wp->query_vars['order-received']) )
		{
			$order_key = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_order_key' AND post_id = '".$wp->query_vars['order-received']."'; " );

			wp_redirect( '/checkout/order-received/'.$wp->query_vars['order-received'].'?key='.$order_key );
		}
	}
}

add_action( 'template_redirect', 'wc_custom_redirect_after_purchase' );



// PREVENT DOUBLE PRODUCT POSTS
function resolve_add_to_cart_redirect($url = false) {
 
     // If another plugin beats us to the punch, let them have their way with the URL
     if(!empty($url)) { return $url; }
 
     // Redirect back to the original page, without the 'add-to-cart' parameter.
     // We add the `get_bloginfo` part so it saves a redirect on https:// sites.
     return get_bloginfo('wpurl').add_query_arg(array(), remove_query_arg('add-to-cart'));
 
}
add_action('woocommerce_add_to_cart_redirect', 'resolve_add_to_cart_redirect');



// BOOKINGS DATE VALIDATION ON post.php SAVE
function sb_post_validation()
{
	$date_fields = array('from_start_date', 'to_end_date', 'from_start_date_off_peak', 'to_end_date_off_peak');

	echo '<div id="acf-date-field-ids">';
		foreach ($date_fields as $acf_field_name)
		{
			$acf_object = get_field_object( $acf_field_name );

			echo '<input id="'.$acf_object['key'].'" type="hidden" >';
		}
	echo '</div>';

	wp_enqueue_script(
		'booking-rules-validate',
		get_stylesheet_directory_uri() . '/assets/js/booking_rules_validate.js',
		array( 'jquery' )
	);

}
add_action('admin_footer', 'sb_post_validation');



// WHEN VIEWING A BOOKING IN BACKEND / ADMIN
function booking_admin_details()
{
	print_r('called...');
}
add_action('woocommerce_admin_booking_data_after_booking_details', 'booking_admin_details');




function init_remove_support(){
    $post_type = 'door-code';
	//following line is remove the body text from wordpress backend on door code.
    //remove_post_type_support( $post_type, 'editor');
}
add_action('init', 'init_remove_support',100);


if (basename($_SERVER['SCRIPT_NAME']) == 'index.php') {
	if( isset($_GET['post_type']) && $_GET['post_type'] == 'door-code' ) {
//		global $wpdb;
//
//		$door_code = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'door-code' AND post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 0,1");
//		$door_code = (isset($door_code[0])) ? $door_code[0]->post_title . ' - ' . get_field_object('door_code', $door_code[0]->ID)['value'] : '';
		//echo $door_code;
	}
}

//function init_door_code_page() {
//	global $wpdb;
//
//	$door_code  = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'door-code' AND post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 0,1");
//	$door_code  = ( isset($door_code[0]) ) ? $door_code[0]->post_title.' - '.get_field_object('door_code', $door_code[0]->ID)['value'] : '';
//echo $door_code;
//}
//
//
//
//function create_routes( $router ) {
//	$router->add_route('Door-Code', array(
//			'path' => '^Door-Code',
//			'access_callback' => true,
//			'page_callback' => 'init_door_code_page',
//			'template' => array(
//					'page.php',
//					dirname( __FILE__ ) . '/page.php'
//			)
//	));
//}
//add_action( 'wp_router_generate_routes', 'create_routes' );



function enqueue_main_scripts() {

	wp_enqueue_script( 'main-script', get_stylesheet_directory_uri() . '/js/dist/main.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'prototype-script', get_stylesheet_directory_uri() . '/js/dist/prototype.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'slides-script', get_stylesheet_directory_uri() . '/js/dist/slides.jquery.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'effects-script', get_stylesheet_directory_uri() . '/js/dist/effects.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'builder-script', get_stylesheet_directory_uri() . '/js/dist/builder.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'table-script', get_stylesheet_directory_uri() . '/js/dist/table.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'lightbox-script', get_stylesheet_directory_uri() . '/js/dist/lightbox.min.js', array( 'jquery'), false, true );

	wp_enqueue_style('lightbox-style',  get_stylesheet_directory_uri() . '/css/dist/lightbox.min.css', array());

}

add_action('init', 'enqueue_main_scripts');

function thankyou_title_order_received( $title, $id ) {
	if ( is_order_received_page() && get_the_ID() === $id ) {
		$title = "BOOKING RECEIVED";
	}
	return $title;
}
add_filter( 'the_title', 'thankyou_title_order_received', 10, 2 );





function getBedData($beds, $type, $bookedBeds, $peopleInCart, $productTitle) {

	global $product;

	$members = $_SESSION['member'];
	$bedData = array();
	$cart = WC()->cart->get_cart();

	//Loop through number of beds provided

	for($i = 1; $i <= $beds; $i++) {

		$bedClasses = array();

		//Is this particular bed currently booked?
		$key = $type . '_' . $i;
		$isBooked = false;

		//This should not be the loop that wraps everything. Will only go through the booked beds to outpu what is needed.
		//Instead a check for booked should be independent from the rest of the logic
		foreach ($bookedBeds as $roomId => $roomBookedBeds) {

			//If the booked bed room and bed id match the this particular room and bed id, this bed has been booked
			if ($roomId == $productTitle && array_key_exists($key, $roomBookedBeds)) {

				$isBooked = true;
				$bedClasses[] = 'booked';

			}

			if($isBooked) {
				break;
			}
		}

		//Determine if the bed is already in the cart
		$inCart = false;
		$inCartPeople = array();

		foreach ($cart as $cartItem) {
			//Check if the bed is already in the cart
			if (isset($cartItem['booking'][$type][$product->post->ID][$i][1]['name']) || ($type == 'double' && isset($cartItem['booking'][$type][$product->post->ID][$i][2]['name']))) {

				$inCart = true;
				$bedClasses[] = 'in-cart active';

				if (isset($cartItem['booking'][$type][$product->post->ID][$i][1]['name'])) {
					$inCartPeople[0] = $cartItem['booking'][$type][$product->post->ID][$i][1]['name'];
				}
				if ($type == 'double' && isset($cartItem['booking'][$type][$product->post->ID][$i][2]['name'])) {
					$inCartPeople[1] = $cartItem['booking'][$type][$product->post->ID][$i][2]['name'];
				}
				break;
			}
		}

		//Construct the actual bed link
		$bedDataString = '';
		$bedDataString .= '<div class="bed-item-wrapper">';
		$bedDataString .= '<div class="bed-box"><a href="javascript:void(0);" class="bed-link ' . implode(' ', $bedClasses) . '"><span class="bed bed-' . $type . '"></span></a></div>';
		$bedDataString .= '<div class="bed-views">';

		if ($isBooked) {

			$bookedGroup = array();
			if ($type == 'double') {

				if (isset($roomBookedBeds[$key][0])) {
					$bookedGroup[] = '<li class="booked ' . getBedGenderClass($roomBookedBeds[$key][0][$productTitle]['gender']) . '">' . $roomBookedBeds[$key][0][$productTitle]['age'] . ' ' . getBedGenderIcon($roomBookedBeds[$key][0][$productTitle]['gender']) . '</li>';
				} else {
					$bookedGroup[] = '<li class="unavailable">Unavailable (single occupant booked, additional occupants cannot be added)</li>';
				}

				if (isset($roomBookedBeds[$key][1])) {
					$bookedGroup[] = '<li class="booked ' . getBedGenderClass($roomBookedBeds[$key][1][$productTitle]['gender']) . '">' . $roomBookedBeds[$key][1][$productTitle]['age'] . ' ' . getBedGenderIcon($roomBookedBeds[$key][1][$productTitle]['gender']) . '</li>';
				} else {
					$bookedGroup[] = '<li class="unavailable">Unavailable (single occupant booked, additional occupants cannot be added)</li>';
				}


			} else {

					$bookedGroup[] = '<li class="booked ' . getBedGenderClass($roomBookedBeds[$key][0][$productTitle]['gender']) . '">' . $roomBookedBeds[$key][0][$productTitle]['age'] . ' ' . getBedGenderIcon($roomBookedBeds[$key][0][$productTitle]['gender']) . '</li>';
			}

			$bedDataString .= '<div class="overall-view"><ul>';
			$bedDataString .= implode('', $bookedGroup);
			$bedDataString .= '</ul></div>';
			$bedDataString .= '<div class="individual-view"><ul>';
			$bedDataString .= implode('', $bookedGroup);
			$bedDataString .= '</ul></div>';

		} else {

			if ($inCart) {
				$bedDataString .= '<div class="overall-view in-cart">';
				$bedDataString .= getBedInCartList($inCartPeople, $type);
				$bedDataString .= '</div>';
			} else {
				$bedDataString .= '<div class="overall-view">';
				$bedDataString .= getBedAvailableList($type);
				$bedDataString .= '</div>';
			}

			$bedDataString .= '<div class="individual-view">';
			$bedDataString .= '<ul>';

			if ($inCart) {
				$bedDataString .= '<li><span class="in-cart">' . (empty($inCartPeople[0]) ? 'N/A' : $inCartPeople[0] . ' | <a href="' . get_site_url() . '/cart">Edit Booking</a>') . '</span></li>';
			} else {
				$bedDataString .= '<li class="select-wrapper">';
				//First select box for this bed (for single beds and bunks, this is the only one that would be created)
				$bedDataString .= '<select class="selectName ' . $type . '[' . $product->post->ID . '][' . $i . '][1]" name="' . $type . '[' . $product->post->ID . '][' . $i . '][1][name]"' . (!empty($inCartPeople) ? ' data-original-value="' . $inCartPeople[0] . '"' : '') . ' data-previous="">';
				$bedDataString .= '<option value="">Select Person</option>';

				foreach ($members as $v) {
					$val = $v['name'];
					if (!in_array($val, $peopleInCart)) {
						$bedDataString .= '<option value="' . $val . '" ' . (isset($inCartPeople[0]) && $val == $inCartPeople[0] ? 'selected="selected"' : '') . '>' . $val . '</option>';
					}
				}

				$bedDataString .= '<input type="hidden" name="' . $type . '[' . $product->post->ID . '][' . $i . '][1][age]" value="">';
				$bedDataString .= '<input type="hidden" name="' . $type . '[' . $product->post->ID . '][' . $i . '][1][member_type]" value="">';
				$bedDataString .= '<input type="hidden" name="' . $type . '[' . $product->post->ID . '][' . $i . '][1][account_number]" value="">';
				$bedDataString .= '<input type="hidden" name="' . $type . '[' . $product->post->ID . '][' . $i . '][1][gender]" value="">';
				$bedDataString .= '</select>';

				$bedDataString .= '<span class="unselected-selected-user" data-target="' . $type . '[' . $product->post->ID . '][' . $i . '][1]"><i class="fa fa-fw"></i></span>';
				$bedDataString .= '</li>';
			}

			//Second select box (double beds only)
			if ($type == 'double') {

				if ($inCart) {
					$bedDataString .= '<li><span class="in-cart">' . (empty($inCartPeople[1]) ? 'N/A' : $inCartPeople[1] . ' | <a href="' . get_site_url() . '/cart">Edit Booking</a>') . '</span></li>';
				} else {

					$bedDataString .= '<li class="select-wrapper">';
					$bedDataString .= '<select class="selectName ' . $type . '[' . $product->post->ID . '][' . $i . '][2]" name="' . $type . '[' . $product->post->ID . '][' . $i . '][2][name]"' . (!empty($inCartPeople) ? ' data-original-value="' . $inCartPeople[1] . '"' : '') . ' data-previous="">';
					$bedDataString .= '<option value="">Select Person</option>';
					foreach ($members as $v) {
						$val = $v['name'];
						if (!in_array($val, $peopleInCart)) {
							// if there is double bed booking person 2, select it
							$bedDataString .= '<option value="' . $val . '" ' . (isset($inCartPeople[1]) && $val == $inCartPeople[1] ? 'selected="selected"' : '') . '>' . $val . '</option>';
						}
					}

					$bedDataString .= '<input type="hidden" name="double[' . $product->post->ID . '][' . $i . '][2][age]" value="">';
					$bedDataString .= '<input type="hidden" name="double[' . $product->post->ID . '][' . $i . '][2][member_type]" value="">';
					$bedDataString .= '<input type="hidden" name="double[' . $product->post->ID . '][' . $i . '][2][account_number]" value="">';
					$bedDataString .= '<input type="hidden" name="double[' . $product->post->ID . '][' . $i . '][2][gender]" value="">';
					$bedDataString .= '</select>';

					$bedDataString .= '<span class="unselected-selected-user" data-target="' . $type . '[' . $product->post->ID . '][' . $i . '][2]"><i class="fa fa-fw"></i></span>';
					$bedDataString .= '</li>';
				}
			}

			$bedDataString .= '</ul>';
			$bedDataString .= '</div>';
		}
		$bedDataString .= '</div></div>';
		$bedData[$i] = $bedDataString;

	}

	$returnArray = array(
		'bed_data' => implode('', $bedData)
	);

	return $returnArray;

}

function getBedGenderClass($userGender) {

	//What gender is the person who booked it
	switch (strtolower($userGender)) {
		case 'male':
			return 'male';
		case 'female':
			return 'female';
		default:
			return '';
	}

}

function getBedGenderIcon($userGender) {

	//What gender is the person who booked it
	switch (strtolower($userGender)) {
		case 'male':
			return '<i class="fa fa-mars"></i>';
		case 'female':
			return '<i class="fa fa-venus"></i>';
		default:
			return '';
	}

}

function getBedInCartList($inCartPeople, $type) {

	$returnString = '<ul>';

	$num = $type == 'double' ? 2 : 1;

	for($i = 0; $i < $num; $i++) {

		if(isset($inCartPeople[$i])) {
			$returnString .= '<li class="in-cart">'. $inCartPeople[$i] . ' | <a href="'. get_site_url() .'/cart">Edit Booking</a></li>';
		}
		else {
			$returnString .= '<li class="in-cart">N/A</li>';
		}
	}

	$returnString .= '</ul>';

	return $returnString;

}

function getBedAvailableList($type) {

	$returnString = '<ul>';
	$returnString .= '<li class="available">Available</li>';

	if($type == 'double') {
		$returnString .= '<li class="available">Available</li>';
	}

	$returnString .= '</ul>';

	return $returnString;

}

//Move the bed information to the top of the array
function reorderItemData($item_data) {

	$returnArray = $item_data;
	$bedItems = array();

	for($i = 0; $i < count($item_data); $i++) {

		if(strpos(strtolower($item_data[$i]['key']), 'bed') !== false) {
			$bedItems[] = $item_data[$i];
			unset($returnArray[$i]);
		}

	}

	foreach($bedItems as $key => $bedItem) {
		$bedItems[$key]['display'] = '<span class="red-text">'. $bedItem['display'] .'</span>';
	}

	$returnArray = array_merge($bedItems, $returnArray);

	return $returnArray;

}

//Search array
function arraySearchByKey($array, $key) {
	$results = array();

	if (is_array($array)) {
		if (isset($array[$key])) {
			$results[] = $array;
		}

		foreach ($array as $subarray) {
			$results = array_merge($results, arraySearchByKey($subarray, $key));
		}
	}

	return $results;
}

function allPeopleInCart() {

	$cart = WC()->cart->get_cart();

	$personNamesInCart = array();

	$peopleInCartOriginal = arraySearchByKey($cart, 'name');

	foreach($peopleInCartOriginal as $person) {
		$personNamesInCart[] = $person['name'];
	}

	foreach($_SESSION['member'] as $person) {

		 if(!in_array($person['name'], $personNamesInCart)) {
			 return false;
		 }
	}

	return true;
}


function psl_widgets_init() {

	register_sidebar( array(
		'name'          => __( 'PSL Home Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-home',
		'description'   => __( 'Main sidebar that appears on the left.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'PSL Lodge Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-lodge',
		'description'   => __( 'Additional sidebar that appears on the right.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'PSL Contact Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-contact',
		'description'   => __( 'Appears in the footer section of the site.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'PSL Booking Info Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-booking_info',
		'description'   => __( 'Appears in the footer section of the site.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}

add_action( 'widgets_init', 'psl_widgets_init' );

function psl_initial_setup()
{
	$args = array(
			'flex-width' => true,
			'width' => 900,
			'flex-height' => true,
			'height' => 222,
			'default-image' => get_template_directory_uri() . '/images/slide01.jpg',
			'uploads' => true,
	);
	add_theme_support('custom-header', $args);

	// Make sure featured images are enabled
	add_theme_support('post-thumbnails');

	set_post_thumbnail_size(900, 222);

	// Add featured image sizes
	add_image_size('gallery-thumb', 100, 75, true); // width, height, crop
	add_image_size('banner_img', 900, 222, true); // width, height, crop

}
add_action( 'after_setup_theme', 'psl_initial_setup' );

function psl_custom_header($post){

	$returnUrl = '/wp-content/uploads/2016/04/cropped-slide011.jpg';
	$imageUrl = false;

	if(!empty($post)) {

		//Only archive will be the bed selection page in the booking process
		if(is_archive()) {
			//Get the featured image of the appropriate page
			$bedSelectionPageId = 6;
			$imageUrl = wp_get_attachment_url(get_post_thumbnail_id($bedSelectionPageId));
		}
		else {
			$imageUrl = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
		}
	}

	if($imageUrl) {
		$returnUrl = $imageUrl;
	}

	return $returnUrl;

}

/**
 * From: WP Filters Extra
 * Version: 1.0.1
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Copyright 2012 Amaury Balmer - amaury@beapi.fr
 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
 */
function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;

	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}

	}

	return false;
}

/**
 * From: WP Filters Extra
 * Version: 1.0.1
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Copyright 2012 Amaury Balmer - amaury@beapi.fr
 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
 */
function remove_filters_for_anonymous_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;

	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class, class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}

	}

	return false;
}











function debenture_number_transfer_page_register() {
	add_submenu_page(
		null,
		'Debenture Number Transfer',
		'Debenture Number Transfer',
		'list_users',
		'debenture-number-transfer',
		'debenture_number_transfer_render'
	);
}

add_action( 'admin_menu', 'debenture_number_transfer_page_register' );

function debenture_number_transfer_render() {

	echo '<h1>Transfer Debenture Numbers</h1>';
	echo '<br />';

	if(isset($_POST['debenture_transfer_form'])) {

		$fromMemberId = intval($_POST['from_member_id']);
		$toMemberId = intval($_POST['to_member_id']);
		$debentureNumbers = array();

		if(isset($_POST['debenture_number_1_checkbox'])) {
			$debentureNumbers['account_number_1'] = $_POST['debenture_number_1'];
		}
		if(isset($_POST['debenture_number_2_checkbox'])) {
			$debentureNumbers['account_number_2'] = $_POST['debenture_number_2'];
		}
		if(isset($_POST['debenture_number_3_checkbox'])) {
			$debentureNumbers['account_number_3'] = $_POST['debenture_number_3'];
		}

		$fromDebentureNumbers = $debentureNumbers;

		//Number of debenture numbers toMember has
		$toMemberDebentureNumbers = get_debenture_numbers($toMemberId);

		if(count($debentureNumbers) > (3 - count($toMemberDebentureNumbers))) {

			echo '<h3>Error</h3>';
			echo '<p>Unable to transfer. Member being transferred to does not have enough free slots to accommodate all debenture numbers. Debenture numbers selected: '. count($debentureNumbers) .', slots available: ' . (3 - count($toMemberDebentureNumbers)) . '.</p>';

		}
		else {

			$allSlots = array(
					'account_number_1',
					'account_number_2',
					'account_number_3'
			);

			//While there are debenture numbers to be added, insert into available slot
			foreach($allSlots as $slot) {
				if(!array_key_exists($slot, $toMemberDebentureNumbers) && !empty($debentureNumbers)) {
					$toMemberDebentureNumbers[$slot] = array_shift($debentureNumbers);
				}
			}

			//Update toMember ACF fields
			update_debenture_numbers($toMemberId, $toMemberDebentureNumbers);

			//Clear debenture numbers for fromMember
			update_debenture_numbers($fromMemberId, $fromDebentureNumbers, true);

			//Change the role if necessary
			update_role_based_on_debenture_numbers($toMemberId);

			//Change the role if necessary
			update_role_based_on_debenture_numbers($fromMemberId);

			//See if the fromMember has any debenture numbers remaining, reorder them to ensure first debenture number slot is filled
			//If no debenture numbers, set role to none
			clean_up_debenture_numbers($fromMemberId);

			echo '<h3>Success</h3>';
			echo '<p>Debenture numbers have been transferred. Click here to go back to the <a href="/wp-admin/users.php">users page</a>.</p>';

		}

	}
	else {

		if(!isset($_GET['original_member']) || !(intval($_GET['original_member']) > 1)) {
			echo '<h3>Error</h3>';
			echo '<p>Invalid member ID provided. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';
		}

		//If original user provided as get variable, use that to pre-select
		else {

			$providedId = intval($_GET['original_member']);
			$fromMember = get_userdata($providedId);

			if($fromMember === false) {
				echo '<h3>Error</h3>';
				echo '<p>No members with the specified ID could be found. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';
			}
			else {

				$fromMemberData = get_user_meta($providedId);

				if(empty($fromMember->roles)) {

					echo '<h3>Error</h3>';
					echo '<p>The selected member has no roles assigned. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';

				}
				elseif($fromMemberData['member_type'][0] != 'debenture') {

					echo '<h3>Error</h3>';
					echo '<p>The selected member is not a Debenture Member. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';

				}
				else {

					$debentureNumbers = get_debenture_numbers($fromMember->ID);
					$debentureMembers = get_debenture_members();

					if (!empty($debentureNumbers) && !empty($debentureMembers)) { ?>

						<form id="debenture_number_transfer" action="" method="post">
							<input type="hidden" name="from_member_id" value="<?php echo $fromMember->ID; ?>" />
							<h3>Member to Transfer From</h3>
							<table class="form-table">
								<tbody>
								<tr valign="top">
									<th scope="row" class="titledesc">Transfer From</th>
									<td class="forminp">
										<?php echo $fromMember->data->display_name; ?>
									</td>
								</tr>

								<?php

								$count = 1;

								foreach ($debentureNumbers as $debentureNumber) {

									echo '
								<tr valign="top">
								<th scope="row" class="titledesc">Debenture Number ' . $count . '</th>
								<td class="forminp">
								<input type="text" name="debenture_number_'. $count .'" value="' . $debentureNumber . '" readonly /> <input type="checkbox" name="debenture_number_'. $count .'_checkbox" value="1">
								</td>
								</tr>';

									$count++;
								}

								?>

								</tbody>
							</table>

							<h3>Member To Transfer To</h3>
							<table class="form-table">
								<tbody>
								<tr valign="top">
									<th scope="row" class="titledesc">Transfer To</th>
									<td class="forminp">
										<select name="to_member_id">
											<?php
											foreach ($debentureMembers as $debentureMember) {
												echo '<option value="' . $debentureMember->data->ID . '">' . $debentureMember->data->display_name . '</option>';
											}
											?>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc"><input type="submit" name="debenture_transfer_form" value="Submit" class="button" /></th>
									<td class="forminp"></td>
								</tr>
								</tbody>
							</table>
						</form>

						<?php
					} else {

						echo '<h3>Error</h3>';
						echo '<p>The member specified has no valid debenture numbers to transfer. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';

					}
				}
			}
		}
	}
}

function get_debenture_members() {

	//Select and return all users where member type is "Debenture Member"
	$debentureMembers = get_users(
		array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'member_type',
					'value'	=> 'debenture',
					'compare' => '=',
				),
			),
		)
	);

	return $debentureMembers;

}

function get_debenture_numbers($memberId) {

	global $wpdb;

	$memberMetadata = get_user_meta($memberId);

	$debentureNumbers = array();

	//Check for multiple debenture numbers
	foreach ($memberMetadata as $key => $meta) {

		if(preg_match_all('/^account_number_(\d+)/i', $key)) {
			foreach ($meta as $t => $v) {
				if(!empty($v) && $v != '-1') {
					$debentureNumbers[$key] = $v;
				}
			}
		}
	}

	return $debentureNumbers;

}

function update_debenture_numbers($memberId, $debentureNumbersArray, $clear = false) {

	//Update fromMember ACF fields (remove debenture numbers)
	foreach($debentureNumbersArray as $key => $dn) {

		switch($key) {

			case 'account_number_1':
				$fieldKey = 'field_55f2265e72fd0';
				$clearValue = '-1';
				break;
			case 'account_number_2':
				$fieldKey = 'field_5601f2dccda33';
				$clearValue = '';
				break;
			case 'account_number_3':
				$fieldKey = 'field_5601f318cda34';
				$clearValue = '';
				break;

		}

		if($clear) {
			update_user_meta($memberId, $key, $clearValue);
			update_user_meta($memberId, '_' . $key, $fieldKey);
		}
		else {
			update_user_meta($memberId, $key, $dn);
			update_user_meta($memberId, '_' . $key, $fieldKey);
		}

	}

}

function clean_up_debenture_numbers($memberId) {

	$memberDebentureNumbers = get_debenture_numbers($memberId);

	if(!empty($memberDebentureNumbers)) {

		//If there is a debenture number but the first debenture number slot is empty, make sure it is filled
		if (!isset($memberDebentureNumbers['account_number_1'])) {

			if (isset($memberDebentureNumbers['account_number_2'])) {
				$memberDebentureNumbers['account_number_1'] = $memberDebentureNumbers['account_number_2'];
				$memberDebentureNumbers['account_number_2'] = '';
			} elseif (isset($memberDebentureNumbers['account_number_3'])) {
				$memberDebentureNumbers['account_number_1'] = $memberDebentureNumbers['account_number_3'];
				$memberDebentureNumbers['account_number_3'] = '';
			}

			//Update the debenture numbers
			update_debenture_numbers($memberId, $memberDebentureNumbers);

		}

	}

}

function update_role_based_on_debenture_numbers($memberId) {

	$memberDebentureNumbers = get_debenture_numbers($memberId);

	//Member has no more debenture numbers, so set role to none
	if(empty($memberDebentureNumbers)) {
		wp_update_user(
			array(
				'ID' => $memberId,
				'role' => 'none'
			)
		);
	}
	else {

		//If user has debenture numbers but has no role, set role to subscriber
		$memberData = get_userdata($memberId);

		if(empty($memberData->roles)) {
			wp_update_user(
				array(
					'ID' => $memberId,
					'role' => 'subscriber'
				)
			);
		}

	}

}

function logToFile($message) {

	$myfile = fopen("/var/www/psl/public_html/debug-log.txt", "a") or die("Unable to open file!");
	fwrite($myfile, "\n". $message);
	fclose($myfile);

}
