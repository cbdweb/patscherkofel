<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// if user not login, redirect to account login
if (!is_user_logged_in()) {
	wp_safe_redirect( wc_get_page_permalink( 'myaccount') );
}

// if there is no date range session, redirect to new booking page
if ( !$_SESSION['from'] || !$_SESSION['to'] ) {
	wp_safe_redirect('/my-account/');
}

get_header( 'shop' ); ?>

	<?php

		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );

		/**
		 * PROCESS THE CART SUMMERY
		 *
		 */
		$cart_size = sizeof($woocommerce->cart->get_cart());

		$booking_plural = ( $cart_size > 1 ) ? 's' : '';

		$cart_summery = ( $cart_size > 0 ) ? 'View Your Booking'.$booking_plural : '';

		$members = ( isset($_SESSION['member']) ) ? (new PSL_Booking_Form())->is_account_number_booked( $_SESSION['member'] ) : '';

	?>

		<?php
		if( !empty($members) ){

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

			<div id="cart-summary">
				<span class="cart-summary-change-dates"><a href="<?php echo get_home_url(); ?>/booking-dates/">Change Dates</a></span>
	
					<span class="cart-summary-basket">
					
						<a class="next-cart-btn <?php if( $cart_size > 0 ){ ?> active-btn <?php } ?> " href="<?php echo get_home_url(); ?>/cart/">Continue</a></span>



				<span class="cart-summary-total">Total: <?php echo WC()->cart->get_cart_total();?></span>
			</div>


			<!-- <div id="cart-summary">
				<span class="cart-summary-change-dates"> -->
				<div id="cart-dates" class="selected">
				<?php 
					$from 		= $_SESSION['from'];
					$to 		= $_SESSION['to'];
					$fromDate 	= date("jS F, Y", strtotime( str_replace('/', '-', $from) ) );
					$toDate 	= date("jS F, Y", strtotime( str_replace('/', '-', $to) ) );

					echo '<span><strong>Selected dates:</strong></span>';
					echo '<span>'.$fromDate.'</span>';
					echo '<span>'.$toDate.'</span>';
				?>
				</div>
				<!-- </span>
			</div> -->
			

			<div id="room-filters">
				<span class="filter-by">Filter By</span>
				<span class="filter-wrapper">
					<select class="filter">
						<option value="available" data-target="availability-available" data-type="availability">Show Available</option>
						<option value="unavailable" data-target="availability-unavailable" data-type="availability">Show Unavailable</option>
					</select>
				</span>

				<span class="filter-wrapper">
					<select class="filter">
						<option value="any" data-target="" data-type="gender">Any</option>
						<option value="male" data-target="gender-male" data-type="gender">Male</option>
						<option value="female" data-target="gender-female" data-type="gender">Female</option>
					</select>
				</span>

				<span class="filter-wrapper">
					<select class="filter">
						<option value="any" data-target="" data-type="age">Any</option>
						<option value="group-1" data-target="age-group-1" data-type="age">Under 5</option>
						<option value="group-2" data-target="age-group-2" data-type="age">5 to 16</option>
						<option value="group-3" data-target="age-group-3" data-type="age">Over 16</option>
					</select>
				</span>
			</div>

			<div id="bed-key">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/single.png" class="bed-key-icon" />
				Available
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/single-active.png" class="bed-key-icon" />
				Selected
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/single-booked.png" class="bed-key-icon" />
				Unavailable
			</div>
		<?php } ?>

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

			<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

			<div id="no-room-results">
				No rooms match your criteria.
			</div>

		<?php endif; ?>

		<?php
			/**
			 * woocommerce_archive_description hook
			 * 
			 * @hooked woocommerce_taxonomy_archive_description - 10
			 * @hooked woocommerce_product_archive_description - 10
			 */
			do_action( 'woocommerce_archive_description' );
		?>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>
			<?php
			// add hidden div based on from and to session var
			$from = explode('/', $_SESSION['from']);
			$to = explode('/', $_SESSION['to']);
			?>
			<input type="hidden" id="duration" value="<?php echo $_SESSION['duration']; ?>">
			<input type="hidden" id="from_dd" value="<?php echo $from[0]; ?>">
			<input type="hidden" id="from_mm" value="<?php echo $from[1]; ?>">
			<input type="hidden" id="from_yy" value="<?php echo $from[2]; ?>">
			<input type="hidden" id="to_dd" value="<?php echo $to[0]; ?>">
			<input type="hidden" id="to_mm" value="<?php echo $to[1]; ?>">
			<input type="hidden" id="to_yy" value="<?php echo $to[2]; ?>">
			
			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>
				<!-- <form class="cart" method="post">
					<button type="submit" class="wc-bookings-booking-form-button button alt">Clear All Bookings</button>
					<input type="hidden" name="_clear_cart_nonce" value="<?php echo wp_create_nonce('_clear_cart_nonce'); ?>">
				</form> -->


				<?php if( !empty($members) ){ ?>

					<?php while ( have_posts() ) : the_post(); ?>

						<?php wc_get_template_part( 'content', 'product' ); ?>

					<?php endwhile; // end of the loop. ?>

				<?php }else{ ?>

					<form class="cart" action="/my-account/booking-dates/" method="post">
						<p>The Membership number(s) used for your current booking already have bookings made.<br />Please go back and select different Check-in and Check-out dates.</p>
						<button type="submit" class="wc-bookings-booking-form-button button alt">Change Dates</button>
						<input type="hidden">
						<p><a href="/my-account/add-members/">Start Over</a>
					</form>

				<?php } ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>

	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>

<script>

<?php
// display all user in arrays so that we can retrieve them later.
echo 'var member = [];';
foreach ($_SESSION['member'] as $k => $v) {
	$gender = isset($v['gender']) ? "gender:'{$v['gender']}'," : "gender:'',";
	echo "
	var obj = {name:'".addslashes($v['name'])."', age:'{$v['age']}', member_type:'{$v['member_type']}' , ".$gender." account_number:'{$v['account_number']}'};
	member[$k] = obj;
	";
}

echo "
function getMember(name) {
	for (x in member) {
		if (x.name == name) {
			return x;
		}
	}
}
";
?>
</script>




<?php get_footer( 'shop' ); ?>

