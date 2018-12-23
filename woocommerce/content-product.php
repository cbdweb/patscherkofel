<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop, $bed_types;

$psl_booking_form = new PSL_Booking_Form( $product );
$isBookable 	= $psl_booking_form->is_bookable();

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 1 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count
$woocommerce_loop['loop']++;


// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}

//print_r( WC()->cart->get_cart() );

//$classes[] = 'accordion';
$classes[] = '';
$id = 'post-'. $product->post->ID;

$description = preg_split('/\r\n|\r|\n/', stripslashes($product->post->post_content));

//$psl_booking_form = new PSL_Booking_Form( $product );

$classes[] = ($isBookable == true ) ? 'availability-available' : 'availability-unavailable';

// get number of rooms
$single_bed = get_post_meta( $product->post->ID, '_wc_booking_single_bed', true );
$double_bed = get_post_meta( $product->post->ID, '_wc_booking_double_bed', true );
$bunk_bed 	= get_post_meta( $product->post->ID, '_wc_booking_bunk_bed', true );

// we now need to filter the single, double and bunk bed and see if they have been booked.
// if yes, we should not display the beds

// what is in the cart? get first array value of cart
$cart = WC()->cart->get_cart();
$cart = reset($cart)['booking'];
$members = $_SESSION['member'];


// check if any members have already booked these dates by debenture number
// if so, remover from member list
$members = $psl_booking_form->is_account_number_booked( $members );

foreach ($members as $k => $v) {
	if (PSL_Booking_Cart_Manager::is_member_in_cart($v['name'])) {
		unset($members[$k]);
	}
}

// scan through the booking database with selected date range.
// if room has been booked, get the bed number that has been booked.
$selected = $psl_booking_form->get_user_selected_settings();
$bookable = $psl_booking_form->is_bookable( $selected , true);
$whos_booked = $psl_booking_form->whos_booked($selected);

$bookedBeds = array();

//If certain beds have already been booked, grab their data
if( !empty($whos_booked) )
{
	if( isset($whos_booked[0]) )
	{

		foreach($whos_booked as $whoHasBooked) {

			if (!empty($whoHasBooked->booking_items)) {

				foreach ($whoHasBooked->booking_items as $roomId => $who) {

					//Not all people who have been booked into beds show up here

					$bookedBedId = '';
					$bookedBedId = $who[$product->post->post_title]['bed'];


//					unset($who[$product->post->post_title]['bed']);

					//Index by bed id
//					$bookedBeds[$bookedBedId][] = $who[$product->post->post_title];

					reset($who);
					$first_key = key($who);

					$bookedBeds[$first_key][$bookedBedId][] = $who;

				}

			}

		}

	}
}

//Get names of the people currently in cart so they can be passed to the getBedData function later
$cart = WC()->cart->get_cart();
$peopleInCartOriginal = arraySearchByKey($cart, 'name');
$peopleInCart = array();

foreach($peopleInCartOriginal as $item) {
	$peopleInCart[] = $item['name'];
}

$outputBedsSingle = '';
$outputBedsDouble = '';
$outputBedsBunk = '';
$outputBedsSelects = '';

$productTitle = $product->post->post_title;

// we now know which bed has been booked. Let us remove the beds as we loop thru them.
if ($single_bed)
{

	$single_booked = array();
    $outputBedsSingle .= '<div class="beds-wrapper">';

	$bed_num = $psl_booking_form->get_bed_number_from_bookable_array($bookable, 'single');
    $dataSingle = getBedData($single_bed, 'single', $bookedBeds, $peopleInCart, $productTitle);
    $outputBedsSingle .= $dataSingle['bed_data'];
	$outputBedsSingle .= '<div class="bed-item-wrapper"><div class="bed-box"><h4>Single Beds</h4></div></div>';
    $outputBedsSingle .= '</div>';
}

if ($double_bed) {

    $outputBedsDouble .= '<div class="beds-wrapper">';

	$bed_num = $psl_booking_form->get_bed_number_from_bookable_array($bookable, 'double');
    $dataDouble = getBedData($double_bed, 'double', $bookedBeds, $peopleInCart, $productTitle);
    $outputBedsDouble .= $dataDouble['bed_data'];
	$outputBedsDouble .= '<div class="bed-item-wrapper"><div class="bed-box"><h4>Double Beds</h4></div></div>';
    $outputBedsDouble .= '</div>';
}

if ($bunk_bed) {

    $outputBedsBunk .= '<div class="beds-wrapper">';

	$bed_num = $psl_booking_form->get_bed_number_from_bookable_array($bookable, 'bunk');
    $dataBunk = getBedData($bunk_bed, 'bunk', $bookedBeds, $peopleInCart, $productTitle);
    $outputBedsBunk .= $dataBunk['bed_data'];
	$outputBedsBunk .= '<div class="bed-item-wrapper"><div class="bed-box"><h4>Bunk Beds</h4></div></div>';
    $outputBedsBunk .= '</div>';
}

//Determine which genders are in the room
$bookedMale = 0;
$bookedFemale = 0;

$group1 = 0;
$group2 = 0;
$group3 = 0;

foreach($bookedBeds as $roomBookedBeds) {

	foreach ($roomBookedBeds as $bed) {

		foreach ($bed as $occupants) {

			foreach ($occupants as $occupant) {

				switch (strtolower($occupant['gender'])) {

					case 'male':
						$bookedMale += 1;
						break;
					case 'female':
						$bookedFemale += 1;
						break;
				}

				switch (strtolower($occupant['age'])) {

					case 'under 5':
						$group1 += 1;
						break;
					case '5 to 16':
						$group2 += 1;
						break;
					case 'above 16':
						$group3 += 1;
						break;
				}
			}
		}
	}
}

if($bookedMale > 0 && $bookedFemale < 1) {
	$classes[] = 'gender-male';
}
if($bookedFemale > 0 && $bookedMale < 1) {
	$classes[] = 'gender-female';
}
if($group1 > 0 && (($group2 + $group3) < 1)) {
	$classes[] = 'age-group-1';
}
if($group2 > 0 && (($group1 + $group3) < 1)) {
	$classes[] = 'age-group-2';
}
if($group3 > 0 && (($group1 + $group2) < 1)) {
	$classes[] = 'age-group-3';
}

?>

<li id="<?php echo $id; ?>" <?php post_class( $classes ); ?> style="width:100%">
	<fieldset>

		<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
		<legend><?php echo $product->post->post_title; ?></legend>
	
		<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form id="<?php echo $woocommerce_loop['loop']; ?>" class="cart" method="post" enctype='multipart/form-data'>


		<div class="room-wrapper-inner">

			<div class="wc-bookings-booking-cost" style="display:none"></div>

			<div id="wc-bookings-booking-form" class="wc-bookings-booking-form">
				<h4>Room Layout</h4>
				<?php do_action( 'woocommerce_before_booking_form' ); ?>

				<?php
				$psl_booking_form->output();
				?>
				<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

				<?php
					echo $outputBedsSingle;
					echo $outputBedsDouble;
					echo $outputBedsBunk;

					echo '<div class="selectables">';
					echo $outputBedsSelects;
					echo '</div>';
				?>

			</div>

			<div class="room-description">
				<h4>Room Information</h4>
				<?php
				echo '<ul>';
				foreach ($description as $d => $desc) {
					echo '<li class="order">
						'.$desc.'
						</li>';
				};
				echo '</ul>';
				?>
			</div>
		</div>

		<div class="call-to-action-wrapper">
			<a href="javascript:void(0);" class="call-to-action button alt float-right">Select Room</a>
			<button type="submit" class="wc-bookings-booking-form-button single_add_to_cart_button button alt disabled float-right">Update</button>
			<a href="javascript:void(0);" class="clear-call-to-action button float-right button-white">Cancel</a>
		</div>

		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />
	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</fieldset>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

</li>
