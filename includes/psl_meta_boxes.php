<?php

// create the bed boxes under general tab
add_action( 'woocommerce_product_options_general_product_data', 'psl_add_general_data');
// create new columns under cost tab
add_action( 'woocommerce_bookings_after_booking_pricing_cost', 'psl_add_cost_data');

// let us add menu_order to the table columns
add_action('manage_edit-booking-rules_columns', 'add_booking_rules_menu_order_column');
add_action('manage_booking-rules_posts_custom_column','show_booking_rules_menu_order_column');
add_filter('manage_edit-booking-rules_sortable_columns','order_booking_rules_menu_order_sortable');

// remove woocommerce bookings availability tab using javascript
add_action( 'woocommerce_product_write_panels', 'psl_remove_menus' );

function psl_remove_menus() {
	echo '<script>';
	echo 'jQuery(function($) {$(\'.bookings_tab\').remove();});';
	echo '</script>';
}

function add_booking_rules_menu_order_column($columns) {

  $new_columns['cb'] = '<input type="checkbox" />';
  $new_columns['title'] = 'Title';
  $new_columns['menu_order'] = 'Booking Rules Order';
  $new_columns['from'] = 'From';
  $new_columns['to'] = 'To';
  $new_columns['date'] = 'Date Created';
  return $new_columns;
}

function show_booking_rules_menu_order_column($name){
  global $post;

  switch ($name) {
    case 'menu_order':
      $order = $post->menu_order;
      echo $order;
      break;
  	case 'from':
  		echo get_field('from_start_date', $post->ID);
		break;
  	case 'to':
  		echo get_field('to_end_date', $post->ID);
  		break;
   default:
      break;
   }
}

function order_booking_rules_menu_order_sortable($columns){
  	$columns['menu_order'] = 'menu_order';
	return $columns;
}

// save the boxes. do this after woocommerce-bookings save the data
add_action( 'woocommerce_process_product_meta', 'psl_save_data', 21 );

function psl_add_general_data() {
	global $post;
	echo '<div class="options_group show_if_booking">';
	woocommerce_wp_text_input( array(
		'id'                => '_wc_booking_single_bed',
		'label'             => __( 'Single Bed (sleeps 1)', 'woocommerce-bookings' ),
		'description'       => __( 'Number of Single Beds.', 'woocommerce-bookings' ),
		'value'             => max( absint( get_post_meta( $post->ID, '_wc_booking_single_bed', true ) ), 0 ),
		'desc_tip'          => true,
		'type'              => 'number',
		'custom_attributes' => array(
			'min'  => '',
			'step' => '1'
		)
	) );
	woocommerce_wp_text_input( array(
		'id'                => '_wc_booking_double_bed',
		'label'             => __( 'Double Bed (sleeps 2)', 'woocommerce-bookings' ),
		'description'       => __( 'Number of Double Beds.', 'woocommerce-bookings' ),
		'value'             => max( absint( get_post_meta( $post->ID, '_wc_booking_double_bed', true ) ), 0 ),
		'desc_tip'          => true,
		'type'              => 'number',
		'custom_attributes' => array(
			'min'  => '',
			'step' => '1'
		)
	) );
	woocommerce_wp_text_input( array(
		'id'                => '_wc_booking_bunk_bed',
		'label'             => __( 'Single Bunk Bed (sleeps 1)', 'woocommerce-bookings' ),
		'description'       => __( 'Number of Single Bunk Beds.', 'woocommerce-bookings' ),
		'value'             => max( absint( get_post_meta( $post->ID, '_wc_booking_bunk_bed', true ) ), 0 ),
		'desc_tip'          => true,
		'type'              => 'number',
		'custom_attributes' => array(
			'min'  => '',
			'step' => '1'
		)
	) );
	echo '</div>';
}

function psl_add_cost_data($pricing, $post_id) {

	if ( ! empty( $pricing['cost_member'] ) ) {
		$member = $pricing['cost_member'];	
	}
	if ( ! empty( $pricing['cost_reciprocal'] ) ) {
		$reciprocal = $pricing['cost_reciprocal'];	
	}
	echo '<input type="number" step="0.01" name="wc_booking_pricing_cost_reciprocal[]" value="'.$reciprocal.'" placeholder="Reciprocal Price" />';
	echo '<input type="number" step="0.01" name="wc_booking_pricing_cost_member[]" value="'.$member.'" placeholder="Member Price" />';

}

function psl_save_data($post_id) {
	global $wpdb;

	$product_type         = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );
	$has_additional_costs = false;

	if ( 'booking' !== $product_type ) {
		return;
	}
	update_post_meta( $post_id, '_wc_booking_single_bed', absint($_POST[ '_wc_booking_single_bed' ]));
	update_post_meta( $post_id, '_wc_booking_double_bed', absint($_POST[ '_wc_booking_double_bed' ]));
	update_post_meta( $post_id, '_wc_booking_bunk_bed', absint($_POST[ '_wc_booking_bunk_bed' ]));

	
	// $pricing = get_post_meta( $post_id, '_wc_booking_pricing', true );
	// $row_size     = isset( $_POST[ "wc_booking_pricing_type" ] ) ? sizeof( $_POST[ "wc_booking_pricing_type" ] ) : 0;
	// for ( $i = 0; $i < $row_size; $i ++ ) {
	// 	$pricing[ $i ]['cost_member']          = wc_clean( $_POST[ "wc_booking_pricing_cost_member" ][ $i ] );
	// 	$pricing[ $i ]['cost_reciprocal']          = wc_clean( $_POST[ "wc_booking_pricing_cost_reciprocal" ][ $i ] );
	// }

	// update_post_meta( $post_id, '_wc_booking_pricing', $pricing );

	// // custom logic: is has person, max bookings per block should be = max person
	// $product = new WC_Product_Booking(get_post($post_id));
	// if ($product->has_persons()) {
	// 	update_post_meta( $post_id, '_wc_booking_qty', absint($_POST['_wc_booking_max_persons_group']) );
	// }
}
