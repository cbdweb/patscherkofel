<?php
/**
 * Proceed to checkout button
 *
 * Contains the markup for the proceed to checkout button on the cart
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
echo '<a href="'. get_home_url().'/my-account/available-rooms" class="wc-bookings-booking-form-button button" value="Room Selection">BACK TO BED SELECTION</a>';
echo '<a href="' . esc_url( WC()->cart->get_checkout_url() ) . '" class="button alt wc-forward float-right">' . __( 'Proceed to Checkout', 'woocommerce' ) . '</a>';
