<?php

// start session
session_start();
// session_destroy();

require_once('includes/tac_functions.php');

function clear_sessions_on_logout()
{
    if( isset($_SESSION['is_admin_checkout']) ){ unset($_SESSION['is_admin_checkout']); }
    if( isset($_SESSION['member']) ) { unset($_SESSION['member']); }
}
add_action('wp_logout', 'clear_sessions_on_logout');

// REMOVE COLOR SCHEME SELECTOR
remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

function enqueue_main_scripts() {

	wp_enqueue_script( 'main-script', get_stylesheet_directory_uri() . '/js/dist/main.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'prototype-script', get_stylesheet_directory_uri() . '/js/dist/prototype.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'slides-script', get_stylesheet_directory_uri() . '/js/dist/slides.jquery.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'effects-script', get_stylesheet_directory_uri() . '/js/dist/effects.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'builder-script', get_stylesheet_directory_uri() . '/js/dist/builder.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'table-script', get_stylesheet_directory_uri() . '/js/dist/table.min.js', array( 'jquery'), false, true );
	wp_enqueue_script( 'lightbox-script', get_stylesheet_directory_uri() . '/js/dist/lightbox.min.js', array( 'jquery'), false, true );

	wp_enqueue_style('lightbox-style',  get_stylesheet_directory_uri() . '/css/dist/lightbox.min.css', array());

}

add_action('init', 'enqueue_main_scripts');

//Search array
function arraySearchByKey($array, $key) {
	$results = array();

	if (is_array($array)) {
		if (isset($array[$key])) {
			$results[] = $array;
		}

		foreach ($array as $subarray) {
			$results = array_merge($results, arraySearchByKey($subarray, $key));
		}
	}

	return $results;
}

function psl_widgets_init() {

	register_sidebar( array(
		'name'          => __( 'PSL Home Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-home',
		'description'   => __( 'Main sidebar that appears on the left.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'PSL Lodge Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-lodge',
		'description'   => __( 'Additional sidebar that appears on the right.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'PSL Contact Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-contact',
		'description'   => __( 'Appears in the footer section of the site.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'PSL Booking Info Sidebar', 'psl' ),
		'id'            => 'psl-sidebar-booking_info',
		'description'   => __( 'Appears in the footer section of the site.', 'psl' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}

add_action( 'widgets_init', 'psl_widgets_init' );

function psl_initial_setup()
{
	$args = array(
			'flex-width' => true,
			'width' => 900,
			'flex-height' => true,
			'height' => 222,
			'default-image' => get_template_directory_uri() . '/images/slide01.jpg',
			'uploads' => true,
	);
	add_theme_support('custom-header', $args);

	// Make sure featured images are enabled
	add_theme_support('post-thumbnails');

	set_post_thumbnail_size(900, 222);

	// Add featured image sizes
	add_image_size('gallery-thumb', 100, 75, true); // width, height, crop
	add_image_size('banner_img', 900, 222, true); // width, height, crop

}
add_action( 'after_setup_theme', 'psl_initial_setup' );

function psl_custom_header($post){

	$returnUrl = '/wp-content/uploads/2016/04/cropped-slide011.jpg';
	$imageUrl = false;

	if(!empty($post)) {

		//Only archive will be the bed selection page in the booking process
		if(is_archive()) {
			//Get the featured image of the appropriate page
			$bedSelectionPageId = 6;
			$imageUrl = wp_get_attachment_url(get_post_thumbnail_id($bedSelectionPageId));
		}
		else {
			$imageUrl = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
		}
	}

	if($imageUrl) {
		$returnUrl = $imageUrl;
	}

	return $returnUrl;

}

/**
 * From: WP Filters Extra
 * Version: 1.0.1
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Copyright 2012 Amaury Balmer - amaury@beapi.fr
 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
 */
function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;

	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}

	}

	return false;
}

/**
 * From: WP Filters Extra
 * Version: 1.0.1
 * Author: BeAPI
 * Author URI: http://www.beapi.fr
 * Copyright 2012 Amaury Balmer - amaury@beapi.fr
 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
 */
function remove_filters_for_anonymous_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
		return false;

	// Loop on filters registered
	foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
			// Test if object is a class, class and method is equal to param !
			if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
				unset($wp_filter[$hook_name][$priority][$unique_id]);
			}
		}

	}

	return false;
}





function debenture_number_transfer_page_register() {
	add_submenu_page(
		null,
		'Debenture Number Transfer',
		'Debenture Number Transfer',
		'list_users',
		'debenture-number-transfer',
		'debenture_number_transfer_render'
	);
}

add_action( 'admin_menu', 'debenture_number_transfer_page_register' );

function debenture_number_transfer_render() {

	echo '<h1>Transfer Debenture Numbers</h1>';
	echo '<br />';

	if(isset($_POST['debenture_transfer_form'])) {

		$fromMemberId = intval($_POST['from_member_id']);
		$toMemberId = intval($_POST['to_member_id']);
		$debentureNumbers = array();

		if(isset($_POST['debenture_number_1_checkbox'])) {
			$debentureNumbers['account_number_1'] = $_POST['debenture_number_1'];
		}
		if(isset($_POST['debenture_number_2_checkbox'])) {
			$debentureNumbers['account_number_2'] = $_POST['debenture_number_2'];
		}
		if(isset($_POST['debenture_number_3_checkbox'])) {
			$debentureNumbers['account_number_3'] = $_POST['debenture_number_3'];
		}

		$fromDebentureNumbers = $debentureNumbers;

		//Number of debenture numbers toMember has
		$toMemberDebentureNumbers = get_debenture_numbers($toMemberId);

		if(count($debentureNumbers) > (3 - count($toMemberDebentureNumbers))) {

			echo '<h3>Error</h3>';
			echo '<p>Unable to transfer. Member being transferred to does not have enough free slots to accommodate all debenture numbers. Debenture numbers selected: '. count($debentureNumbers) .', slots available: ' . (3 - count($toMemberDebentureNumbers)) . '.</p>';

		}
		else {

			$allSlots = array(
					'account_number_1',
					'account_number_2',
					'account_number_3'
			);

			//While there are debenture numbers to be added, insert into available slot
			foreach($allSlots as $slot) {
				if(!array_key_exists($slot, $toMemberDebentureNumbers) && !empty($debentureNumbers)) {
					$toMemberDebentureNumbers[$slot] = array_shift($debentureNumbers);
				}
			}

			//Update toMember ACF fields
			update_debenture_numbers($toMemberId, $toMemberDebentureNumbers);

			//Clear debenture numbers for fromMember
			update_debenture_numbers($fromMemberId, $fromDebentureNumbers, true);

			//Change the role if necessary
			update_role_based_on_debenture_numbers($toMemberId);

			//Change the role if necessary
			update_role_based_on_debenture_numbers($fromMemberId);

			//See if the fromMember has any debenture numbers remaining, reorder them to ensure first debenture number slot is filled
			//If no debenture numbers, set role to none
			clean_up_debenture_numbers($fromMemberId);

			echo '<h3>Success</h3>';
			echo '<p>Debenture numbers have been transferred. Click here to go back to the <a href="/wp-admin/users.php">users page</a>.</p>';

		}

	}
	else {

		if(!isset($_GET['original_member']) || !(intval($_GET['original_member']) > 1)) {
			echo '<h3>Error</h3>';
			echo '<p>Invalid member ID provided. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';
		}

		//If original user provided as get variable, use that to pre-select
		else {

			$providedId = intval($_GET['original_member']);
			$fromMember = get_userdata($providedId);

			if($fromMember === false) {
				echo '<h3>Error</h3>';
				echo '<p>No members with the specified ID could be found. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';
			}
			else {

				$fromMemberData = get_user_meta($providedId);

				if(empty($fromMember->roles)) {

					echo '<h3>Error</h3>';
					echo '<p>The selected member has no roles assigned. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';

				}
				elseif($fromMemberData['member_type'][0] != 'debenture') {

					echo '<h3>Error</h3>';
					echo '<p>The selected member is not a Debenture Member. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';

				}
				else {

					$debentureNumbers = get_debenture_numbers($fromMember->ID);
					$debentureMembers = get_debenture_members();

					if (!empty($debentureNumbers) && !empty($debentureMembers)) { ?>

						<form id="debenture_number_transfer" action="" method="post">
							<input type="hidden" name="from_member_id" value="<?php echo $fromMember->ID; ?>" />
							<h3>Member to Transfer From</h3>
							<table class="form-table">
								<tbody>
								<tr valign="top">
									<th scope="row" class="titledesc">Transfer From</th>
									<td class="forminp">
										<?php echo $fromMember->data->display_name; ?>
									</td>
								</tr>

								<?php

								$count = 1;

								foreach ($debentureNumbers as $debentureNumber) {

									echo '
								<tr valign="top">
								<th scope="row" class="titledesc">Debenture Number ' . $count . '</th>
								<td class="forminp">
								<input type="text" name="debenture_number_'. $count .'" value="' . $debentureNumber . '" readonly /> <input type="checkbox" name="debenture_number_'. $count .'_checkbox" value="1">
								</td>
								</tr>';

									$count++;
								}

								?>

								</tbody>
							</table>

							<h3>Member To Transfer To</h3>
							<table class="form-table">
								<tbody>
								<tr valign="top">
									<th scope="row" class="titledesc">Transfer To</th>
									<td class="forminp">
										<select name="to_member_id">
											<?php
											foreach ($debentureMembers as $debentureMember) {
												echo '<option value="' . $debentureMember->data->ID . '">' . $debentureMember->data->display_name . '</option>';
											}
											?>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc"><input type="submit" name="debenture_transfer_form" value="Submit" class="button" /></th>
									<td class="forminp"></td>
								</tr>
								</tbody>
							</table>
						</form>

						<?php
					} else {

						echo '<h3>Error</h3>';
						echo '<p>The member specified has no valid debenture numbers to transfer. Please return to the <a href="/wp-admin/users.php">users page</a> and select another member.</p>';

					}
				}
			}
		}
	}
}

function get_debenture_members() {

	//Select and return all users where member type is "Debenture Member"
	$debentureMembers = get_users(
		array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'member_type',
					'value'	=> 'debenture',
					'compare' => '=',
				),
			),
		)
	);

	return $debentureMembers;

}

function get_debenture_numbers($memberId) {

	global $wpdb;

	$memberMetadata = get_user_meta($memberId);

	$debentureNumbers = array();

	//Check for multiple debenture numbers
	foreach ($memberMetadata as $key => $meta) {

		if(preg_match_all('/^account_number_(\d+)/i', $key)) {
			foreach ($meta as $t => $v) {
				if(!empty($v) && $v != '-1') {
					$debentureNumbers[$key] = $v;
				}
			}
		}
	}

	return $debentureNumbers;

}

function update_debenture_numbers($memberId, $debentureNumbersArray, $clear = false) {

	//Update fromMember ACF fields (remove debenture numbers)
	foreach($debentureNumbersArray as $key => $dn) {

		switch($key) {

			case 'account_number_1':
				$fieldKey = 'field_55f2265e72fd0';
				$clearValue = '-1';
				break;
			case 'account_number_2':
				$fieldKey = 'field_5601f2dccda33';
				$clearValue = '';
				break;
			case 'account_number_3':
				$fieldKey = 'field_5601f318cda34';
				$clearValue = '';
				break;

		}

		if($clear) {
			update_user_meta($memberId, $key, $clearValue);
			update_user_meta($memberId, '_' . $key, $fieldKey);
		}
		else {
			update_user_meta($memberId, $key, $dn);
			update_user_meta($memberId, '_' . $key, $fieldKey);
		}

	}

}

function clean_up_debenture_numbers($memberId) {

	$memberDebentureNumbers = get_debenture_numbers($memberId);

	if(!empty($memberDebentureNumbers)) {

		//If there is a debenture number but the first debenture number slot is empty, make sure it is filled
		if (!isset($memberDebentureNumbers['account_number_1'])) {

			if (isset($memberDebentureNumbers['account_number_2'])) {
				$memberDebentureNumbers['account_number_1'] = $memberDebentureNumbers['account_number_2'];
				$memberDebentureNumbers['account_number_2'] = '';
			} elseif (isset($memberDebentureNumbers['account_number_3'])) {
				$memberDebentureNumbers['account_number_1'] = $memberDebentureNumbers['account_number_3'];
				$memberDebentureNumbers['account_number_3'] = '';
			}

			//Update the debenture numbers
			update_debenture_numbers($memberId, $memberDebentureNumbers);

		}

	}

}

function update_role_based_on_debenture_numbers($memberId) {

	$memberDebentureNumbers = get_debenture_numbers($memberId);

	//Member has no more debenture numbers, so set role to none
	if(empty($memberDebentureNumbers)) {
		wp_update_user(
			array(
				'ID' => $memberId,
				'role' => 'none'
			)
		);
	}
	else {

		//If user has debenture numbers but has no role, set role to subscriber
		$memberData = get_userdata($memberId);

		if(empty($memberData->roles)) {
			wp_update_user(
				array(
					'ID' => $memberId,
					'role' => 'subscriber'
				)
			);
		}

	}

}

function logToFile($message) {

	$myfile = fopen("/var/www/psl/public_html/debug-log.txt", "a") or die("Unable to open file!");
	fwrite($myfile, "\n". $message);
	fclose($myfile);

}
