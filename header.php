<?php
/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
global $current_user;

?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->

<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/dist/html5.min.js"></script>
	<![endif]-->
	<?php wp_head(); ?>

	<meta name="keywords" content="Patscherkofel Lodge">
	<link href="/wp-content/themes/psl/css/dist/font-awesome.min.css" type="text/css" rel="stylesheet">
	<link href="/wp-content/themes/psl/css/dist/form.min.css" type="text/css" rel="stylesheet">
	<link href="/wp-content/themes/psl/css/dist/fonts.min.css" type="text/css" rel="stylesheet">
	<link href="/wp-content/themes/psl/css/dist/style.min.css" type="text/css" rel="stylesheet">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/2.2.2/isotope.pkgd.js"></script>
	<script>
		jQuery(document).ready(function(){
			jQuery('.hideshow').on('click', function(event) {
				jQuery(this).siblings('.member_gallery_images').toggle('show');
				jQuery(this).parents('.member_gallery_tab').find('.plus_minus').toggleClass('minus');
			});
		});
	</script>
</head>

<body <?php body_class(); ?>>
	<div id="wrap">
		<div id="header">
			<div id="logo">
				<img src="/wp-content/themes/psl/images/header.png" alt="" border="0" width="900">
			</div>

			<div id="banner">
				<div class="slides_container" style="overflow: hidden; position: relative; display: block;">
					<div class="slides_control" style="position: relative; height: 222px;">
						<div class="slide">
			            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
			                <img src="<?php echo psl_custom_header($post) ?>" alt="Patscherkofel" />
			            </a>
						</div>
					</div>
				</div>
			</div>

			<div id="primary_nav">
				<?php
					$bookingPageSlugs = array(
						'add-members',
						'booking-dates',
						'available-rooms',
						'cart',
						'checkout',
						'room-1',
						'room-2',
						'room-3',
						'room-4',
						'room-5',
						'room-6',
						'room-7',
						'room-8',
						'room-9',
						'room-10',
						'room-11',
						'room-12',
						'room-13',
						'room-14',
						'room-15',
						'room-16',
						'room-17',
						'room-18',
						'room-19',
						'room-20',
						'room-21',
					);
				?>
				<ul>
					<li>
						<a href="/" <?php echo is_home() || is_front_page() ? 'class="current"' : '' ?>>Home</a>
					</li>
					<li>
						<a href="/the-lodge/" <?php echo is_page('the-lodge') ? 'class="current"' : '' ?>>The Lodge</a>
					</li>
					<li>
						<a href="/members/" <?php echo is_page('members') ? 'class="current"' : '' ?>>Member News</a>
					</li>
					<li>
						<a href="/my-account/add-members/" <?php echo in_array($post->post_name, $bookingPageSlugs) ? 'class="current"' : '' ?>>Book Online</a>
					</li>
					<li>
						<a href="/booking-info/" <?php echo is_page('booking-info') ? 'class="current"' : '' ?>>Booking Info</a>
					</li>
					<li>
						<a href="/membership/" <?php echo is_page('membership') ? 'class="current"' : '' ?>>Membership</a>
					</li>
					<li>
						<a href="/contact-us/" <?php echo is_page('contact-us') ? 'class="current"' : '' ?>>Contact</a>
					</li>
				</ul>
			</div>
		</div>

		<div id="primary_nav_mobile_toggle_wrapper">
			<a href="javascript:void(0);">MENU<span class="hamburger"><i class="fa fa-bars"></i></span></a>
		</div>
		<div id="primary_nav_mobile">
			<ul>
				<li>
					<a href="/" <?php echo is_home() ? 'class="current"' : '' ?>>Home</a>
				</li>
				<li>
					<a href="/the-lodge/" <?php echo is_page('the-lodge') ? 'class="current"' : '' ?>>The Lodge</a>
				</li>
				<li>
					<a href="/members/" <?php echo is_page('members') ? 'class="current"' : '' ?>>Member News</a>
				</li>
				<li>
					<a href="/my-account/add-members/" <?php echo in_array($post->post_name, $bookingPageSlugs) ? 'class="current"' : '' ?>>Book Online</a>
				</li>
				<li>
					<a href="/booking-info/" <?php echo is_page('booking-info') ? 'class="current"' : '' ?>>Bookings Info</a>
				</li>
				<li>
					<a href="/membership/" <?php echo is_page('membership') ? 'class="current"' : '' ?>>Membership</a>
				</li>
				<li>
					<a href="/contact-us/" <?php echo is_page('contact-us') ? 'class="current"' : '' ?>>Contact</a>
				</li>
			</ul>
		</div>
		
		<?php if( is_user_logged_in() ){ ?>
		<div class="user-account">
			<div class="header-left">
				<?php
					$ski_locker = get_field( "ski_locker_main", 'user_'.get_current_user_id() );
					$locker     = get_field( "locker_main", 'user_'.get_current_user_id() );
					$door_code  = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'door-code' AND post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 0,1");
					$door_code  = ( isset($door_code[0]) ) ? $door_code[0]->post_title.' - '.get_field_object('door_code', $door_code[0]->ID)['value'] : '';
					//(new tac_functions)->echoPre($ski_locker);
				?>
				<div class="nav">
					<li>
						<a href="#">Lockers #</a>
						<ul>
							<?php if( !empty($ski_locker) || ( isset($ski_locker[0]['ski_locker']) && !empty($ski_locker[0]['ski_locker']) ) ){ ?>
								<?php foreach ($ski_locker as $sk => $sv) {
									echo '<li>';
									if( $sk == 0 ) echo '<span><strong>Ski Lockers</strong></span>';
									echo '<a>'.$sv['ski_locker'].'</a>';
									echo '</li>';
								}?>
							<?php }else{
								echo '<span><strong>No Ski Lockers</strong></span><br />';
							}?>

							<?php if( !empty($locker) ){ ?>
								<?php foreach ($locker as $lk => $lv) {
									echo '<li>';
									if( $lk == 0 ) echo '<span><strong>Lockers</strong></span>';
									echo '<a>'.$lv['locker'].'</a>';
									echo '</li>';
								}?>
							<?php }else{
									echo '<span><strong>No Lockers</strong></span>';
							}?>
						</ul>
					</li>
				</div>
				<div class="nav">
					<li>
						<a><?php echo $door_code; ?></a>
					</li>
				</div>
			</div>
			<div class="header-right">
				<a href="<?php echo get_home_url().'/my-account/'; ?>">My Account</a>
				<a href="<?php echo get_home_url().'/my-account/add-members/'; ?>">Make a Booking</a>
				<a href="<?php echo wp_logout_url( get_home_url().'/my-account/' ); ?>">Logout</a>
				<!-- 
				<?php global $woocommerce; $cart = ( $woocommerce->cart->get_cart_total() ) ? $woocommerce->cart->get_cart() : array(); ?>
				<?php if( !empty($cart) ){ ?>
						<a href="<?php echo get_home_url(); ?>/cart/">View Cart - <?php echo sizeof($woocommerce->cart->get_cart()).' booking'; ?></a>
				<?php } ?>
				 -->
			</div>
		</div>
		<div class="clearfix bg-light-grey"></div>
		<?php } ?>
