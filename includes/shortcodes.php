<?php


// initiate the bookig form and select members as optional
function psl_add_members( $atts )
{
	$bookingWizard = new bookingWizard;
	$bookingWizard->psl_add_members( $atts );
}


// let customer decide on the date range for booking first
function psl_select_booking_date_range( $atts )
{	
	$bookingWizard = new bookingWizard;
	$bookingWizard->psl_select_booking_date_range($atts);
}

// display available rooms
function psl_available_rooms( )
{
	$bookingWizard = new bookingWizard;
	$bookingWizard->psl_availbe_rooms();
}


add_shortcode( 'psl_add_members', 'psl_add_members' );
add_shortcode( 'psl_select_booking_date_range', 'psl_select_booking_date_range' );
add_shortcode( 'psl_available_rooms', 'psl_available_rooms' );
