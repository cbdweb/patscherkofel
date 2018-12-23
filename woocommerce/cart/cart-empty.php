<?php
/**
 * Empty cart page
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices();

?>

			<div class="timeline-holder">
				<div class="timeline">
					<div id="step1" class="step active">1. <span>Members/Guests</span></div>
					<div id="step2" class="step active">2. <span>Dates</span></div>
					<div id="step3" class="step active">3. <span>Beds</span></div>
					<div id="step4" class="step">4. <span>Payment</span></div>
				</div>
			</div>

<p class="cart-empty"><?php _e( 'You don\'t currently have any bookings.', 'woocommerce' ) ?></p>

<?php do_action( 'woocommerce_cart_is_empty' ); ?>

<p class="return-to-shop">
	<a class="wc-bookings-booking-form-button button wc-backward" href="<?php echo get_home_url().'/my-account/available-rooms';?>"><?php _e( 'Back To Bed Selection', 'woocommerce' ) ?></a>
	<a class="button alt wc-backward" href="/my-account/add-members"><?php _e( 'Create A New Booking', 'woocommerce' ) ?></a>
</p>
