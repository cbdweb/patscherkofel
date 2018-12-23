<?php
// if user clicks clear cart, clear cart logic
function form_submit_logic() {
	// if user click clear cart
	if (isset($_POST['_clear_cart_nonce']) && wp_verify_nonce( $_POST['_clear_cart_nonce'], '_clear_cart_nonce' )) {
		WC()->cart->empty_cart(); 
		wc_add_notice(' All bookings have been cleared.');
	}

	// if add booking range form successful, redirect to add category page.
	if (isset($_POST['_new_members_nonce']) && wp_verify_nonce( $_POST['_new_members_nonce'], '_new_members_nonce' )) {
		
		// clear all sessions
		$_SESSION['member'] = array();
		// clear all cart
		WC()->cart->empty_cart();

		// CURRENT LOGGED IN MEMBER HAS SELECTED A ACCOUNT FROM MULTIPLE ACCOUNTS
		if( isset($_POST['multi_account_number']))
		{
			//$_SESSION['member'][999]['multi_account_number'] = $_POST['multi_account_number'];
			return;
		}

		$datapairs = array( 'name', 'age', 'member_type', 'account_number', 'gender');

		foreach ($_POST['member'] as $person => $val) {
			if ($_POST['member'][$person]['name'] == '') {
				continue;
			}
			foreach ($datapairs as $p => $datakey)
			{
				if( isset($_POST['member'][$person][$datakey]) )
				{
					$_SESSION['member'][$person][$datakey] = stripslashes(sanitize_text_field($val[$datakey]));
				}
			}
	 	}

		// once the date check is ok, we redirect to the shop page
		wp_safe_redirect('/my-account/booking-dates', '301');
		exit;
	}

	// if add booking range form successful, redirect to add category page.
	if (isset($_POST['_booking_date_nonce']) && wp_verify_nonce( $_POST['_booking_date_nonce'], '_booking_date_nonce' )) {
		
		// put too and fro in sessions
		$_SESSION['from'] = $_POST['from'];
		$_SESSION['to'] = $_POST['to'];
		// cal duration
		$from = explode('/',$_SESSION['from']);
		$from = strtotime($from[2].'-'.$from[1].'-'.$from[0]);
		$to = explode('/',$_SESSION['to']);
		$to = strtotime($to[2].'-'.$to[1].'-'.$to[0]);

		$_SESSION['duration'] = floor(($to - $from)/(60*60*24));	

		// this is the part where we need to set all the rules other than those that were set. 
		// ....

		// once the date check is ok, we redirect to the shop page
		wp_safe_redirect(get_permalink(woocommerce_get_page_id('shop')), '301');
		exit;
	}

}
add_action( 'wp_loaded', 'form_submit_logic' );
