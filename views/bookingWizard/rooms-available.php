<?php
/*if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}*/
global $bed_types;

$woocommerce_loop = 0;

//print_r($_SESSION);

foreach ($products as $key => $product) 
{
	$classes = array();

	if( 0 == $woocommerce_loop )
	{
		$classes[] = 'first';
	}else{
		$classes[] = 'last';
	}

	$woocommerce_loop++;

	//print_r($booking_form);
?>


	<li <?php post_class( $classes ); ?> style="width:100%">
		<fieldset>
		<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
		<legend><?php echo $product->post->post_name; ?></legend>
		
		<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

		<form class="cart" method="post" enctype='multipart/form-data'>
			
			<div id="wc-bookings-booking-form" class="wc-bookings-booking-form">

				<div class="wc-bookings-booking-cost" style="display:none"></div>

				<?php do_action( 'woocommerce_before_booking_form' ); ?>
				
				<?php
					$psl_booking_form = new PSL_Booking_Form( $product );
					$psl_booking_form->output();
					echo stripslashes($product->post->post_content);
				?>

				<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

				<?php
				// get number of rooms
				$single_bed = get_post_meta( $product->post->ID, '_wc_booking_single_bed', true );
				$double_bed = get_post_meta( $product->post->ID, '_wc_booking_double_bed', true );
				$bunk_bed 	= get_post_meta( $product->post->ID, '_wc_booking_bunk_bed', true );
				
				// we now need to filter the single, double and bunk bed and see if they have been booked.
				// if yes, we should not display the beds
				


				// what is in the cart? get first array value of cart
				$cart = WC()->cart->get_cart();
				$cart =reset($cart)['booking'];
				$members = $_SESSION['member'];

				//check if member has already booked within the date range
				// get members out from array if member already in cart
				foreach ($members as $k => $v) {
					if (PSL_Booking_Cart_Manager::is_member_in_cart($v['name'])) {
						unset($members[$k]);
					}
				}

				


				
				// scan through the booking database with selected date range.
				// if room has been booked, get the bed number that has been booked.
				$selected = $psl_booking_form->get_user_selected_settings();
				$bookable = $psl_booking_form->is_bookable( $selected , true, true);


				// we now know which bed has been booked. Let us remove the beds as we loop thru them.
				if ($single_bed) {
					echo '<h4>Single Beds</h4>';
					
					$bed_num = $psl_booking_form->get_bed_number_from_bookable_array($bookable, 'single');

					for ($i=1; $i<$single_bed+1; $i++) {
						
						foreach ($bed_num as $bed_val) {
							if ($bed_val == $i) {
								continue 2;
							}
						}

						echo '<label>Bed '.$i.': </label>';

						// if there is single bed booking already in the cart, display booking name
//						if (isset($cart['single'][$product->post->ID][$i][1]['name']) && $cart['single'][$product->post->ID][$i][1]['name']) {
//							echo '<p>'.$cart['single'][$product->post->ID][$i][1]['name']."</p>";
//						}
						if(false) {}
						// if the bed is free, show dropdown
						else {
							echo '<select class="selectName" name="single['.$product->post->ID.']['.$i.'][1][name]">';
							echo '<option value="">Select Person</option>';
							foreach ($members as $v) {
								$val = $v['name'];
								echo '<option value="'.$val.'">'.$val.'</option>';
							}
							echo '</select>';
							echo '<input type="hidden" name="single['.$product->post->ID.']['.$i.'][1][age]" value="">';
							echo '<input type="hidden" name="single['.$product->post->ID.']['.$i.'][1][member_type]" value="">';
							echo '<input type="hidden" name="single['.$product->post->ID.']['.$i.'][1][account_number]" value="">';
						}
					}
				}

				if ($double_bed) {
					echo '<h4>Double Beds</h4>';

					$bed_num = $psl_booking_form->get_bed_number_from_bookable_array($bookable, 'double');

					for ($i=1; $i<$double_bed+1; $i++) {
						
						foreach ($bed_num as $bed_val) {
							if ($bed_val == $i) {
								continue 2;
							}
						}

						echo '<label>Bed '.$i.': </label>';
						if (isset($cart['double'][$product->post->ID][$i][1]['name']) && $cart['double'][$product->post->ID][$i][1]['name']) {
							echo '<p>'.$cart['_double'][$product->post->ID][$i][1]['name'].'</p>';
						}
						else {
							echo '<select class="selectName" name="double['.$product->post->ID.']['.$i.'][1][name]">';
							echo '<option value="">Select Person</option>';
							foreach ($members as $v) {
								$val = $v['name'];
								// if there is double bed person 1, select it
								echo '<option value="'.$val.'">'.$val.'</option>';
							}
							echo '</select><br/>';
							echo '<input type="hidden" name="double['.$product->post->ID.']['.$i.'][1][age]" value="">';
							echo '<input type="hidden" name="double['.$product->post->ID.']['.$i.'][1][member_type]" value="">';
							echo '<input type="hidden" name="double['.$product->post->ID.']['.$i.'][1][account_number]" value="">';
						}

						if (isset($cart['double'][$product->post->ID][$i][2]['name']) && $cart['double'][$product->post->ID][$i][2]['name']) {
							echo '<p>'.$cart['double'][$product->post->ID][$i][2]['name'].'</p>';
						}
						else {
							echo '<select class="selectName" name="double['.$product->post->ID.']['.$i.'][2][name]">';
							echo '<option value="">Select Person</option>';
							foreach ($members as $v) {
								$val = $v['name'];
								// if there is double bed booking person 2, select it
								
								echo '<option value="'.$val.'">'.$val.'</option>';
							}
							echo '</select>';
							echo '<input type="hidden" name="double['.$product->post->ID.']['.$i.'][2][age]" value="">';
							echo '<input type="hidden" name="double['.$product->post->ID.']['.$i.'][2][member_type]" value="">';
							echo '<input type="hidden" name="double['.$product->post->ID.']['.$i.'][2][account_number]" value="">';
						}
					}
				}

				if ($bunk_bed) {
					echo '<h4>Bunk Beds</h4>';

					$bed_num = $psl_booking_form->get_bed_number_from_bookable_array($bookable, 'bunk');

					for ($i=1; $i<$bunk_bed+1; $i++) {

						foreach ($bed_num as $bed_val) {
							if ($bed_val == $i) {
								continue 2;
							}
						}

						echo '<label>Bunk '.$i.': </label>';
						if (isset($cart['_bunk'][$product->post->ID][$i][1]['name']) && $cart['_bunk'][$product->post->ID][$i][1]['name']) {
							echo '<p>'.$cart['bunk'][$product->post->ID][$i][1]['name'].'</p>';
						}
						else {
							echo '<select class="selectName" name="bunk['.$product->post->ID.']['.$i.'][1][name]">';
							echo '<option value="">Select Person</option>';
							foreach ($members as $v) {
								$val = $v['name'];
								// if there is bunk bed booking person 1, select it
								echo '<option value="'.$val.'">'.$val.'</option>';
							}
							echo '</select>';
							echo '<input type="hidden" name="bunk['.$product->post->ID.']['.$i.'][1][age]" value="">';
							echo '<input type="hidden" name="bunk['.$product->post->ID.']['.$i.'][1][member_type]" value="">';
							echo '<input type="hidden" name="bunk['.$product->post->ID.']['.$i.'][1][account_number]" value="">';
						}

					}
				}
				?>

			</div>

			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

			<button type="submit" class="wc-bookings-booking-form-button single_add_to_cart_button button alt disabled"><?php echo $product->single_add_to_cart_text(); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		</fieldset>
		</form>

		<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

	</li>

<?php } ?>
