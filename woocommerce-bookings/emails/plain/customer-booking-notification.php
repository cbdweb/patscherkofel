<?php
/**
 * Customer booking notification email
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

echo "= " . $email_heading . " =\n\n";

echo strip_tags( $notification_message );

echo __(  'The details of your booking are shown below.', 'woocommerce-bookings' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf( __( 'Booked: %s', 'woocommerce-bookings'), $booking->get_product()->get_title() ) . "\n";
echo sprintf( __( 'Booking ID: %s', 'woocommerce-bookings'), $booking->get_id() ) . "\n";

if ( $booking->has_resources() && ( $resource = $booking->get_resource() ) ) {
	echo sprintf( __( 'Booking Type: %s', 'woocommerce-bookings'), $resource->post_title ) . "\n";
}

echo sprintf( __( 'Booking Start Date: %s', 'woocommerce-bookings'), $booking->get_start_date() ) . "\n";
echo sprintf( __( 'Booking End Date: %s', 'woocommerce-bookings'), $booking->get_end_date() ) . "\n";

if ( $booking->has_persons() ) {
	foreach ( $booking->get_persons() as $id => $qty ) {
		if ( 0 === $qty ) {
			continue;
		}

		$person_type = ( 0 < $id ) ? get_the_title( $id ) : __( 'Person(s)', 'woocommerce-bookings' );
		echo sprintf( __( '%s: %d', 'woocommerce-bookings'), $person_type, $qty ) . "\n";
	}
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
