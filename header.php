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
						<a href="/booking-info/" <?php echo is_page('booking-info') ? 'class="current"' : '' ?>>Bookings</a>
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
		

