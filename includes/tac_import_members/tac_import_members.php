<?php
/*
Plugin Name: Threes a crowd Import Members CSV
*/
/*if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}*/

class tac_import_members //extends WP_List_Table
{

	private $csv_data;

	private $tracer;


	function __construct()
	{
		//$file = dirname(__DIR__).'/tac_import_members/Juniors.csv';
		$file = dirname(__DIR__).'/tac_import_members/FullMembers.csv';

		$csv = array_map( 'str_getcsv', file($file) );

		$header = $csv[0];

		unset( $csv[0] );

		foreach( $csv as $i => $data )
		{
			foreach( $data as $d => $column )
			{
				$email 		= $data[2];
				$account 	= $data[1];

				$this->csv_data[ $email ][ $account ][ str_replace( '*', '', $header[$d] ) ] = $column;
			}
		}

		
		foreach ( $this->csv_data as $key => $data )
		{
			$this->insert_user( $data );
		}

		//$this->insert_user( $this->csv_data[ 'camilla.clemente@ashurst.com' ] );
		

		//exit;
	}



	private function insert_user( $data )
	{
		if( $data[ key($data) ]['EmailAddress'] == '' )
		{
			print_r( $this->trace(__('No email address user not inserted!!!')) );

			return;
		}

		$user = new StdClass();
		$user->user_id 				= email_exists($data[ key($data) ]['EmailAddress']);
		$user->user_email 			= $data[ key($data) ]['EmailAddress'];
		$user->user_contact_name 	= $data[ key($data) ]['ContactName'];
		$user->firstName 			= $data[ key($data) ]['FirstName'];
		$user->lastName 			= $data[ key($data) ]['LastName'];

		if ( empty($user->user_id) && email_exists( $user->user_email ) == false )
		{
			// INSERT / CREAT NEW USER
			$this->insert_new_user( $user, $data );

		}else
		{
			// UPDATE EXISTING USER
			$this->update_user( $user, $data );
		}
	}



	// CREATE A NEW USER
	private function insert_new_user( $user, $data )
	{
		$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

		$user_id = wp_create_user( $user->user_email, $random_password, $user->user_email );

		// UPDATE ACCOUNT / DEBENTURE NUMBERS
		$this->add_acount_numbers( $user_id, $data );

		// UPDATE BILLING ADDRESS
		$this->add_billing_address( $user_id, $data );

		// UPDATE SHIPPING ADDRESS
		$this->add_shipping_address( $user_id, $data );

		print_r( $this->trace(__('<h1>'.$user->user_contact_name.'</h1>')) );
		print_r( $this->trace(__('<p>'.$user->user_email.'</p>')) );
		//print_r( $this->trace(__('User <strong>'.$user->user_contact_name.'</strong> inserted. id '.$user_id)) );
	}



	// UPDATE EXISTING USER
	private function update_user( $user, $data )
	{
		$user_id = wp_update_user( 
			array( 
				'ID' 			=> $user->user_id,
				'user_login' 	=> $user->user_email,
				'user_email' 	=> $user->user_email,
				'display_name' 	=> $user->user_contact_name,
			) 
		);

		print_r( $this->trace(__('<h1>'.$user->user_contact_name.'</h1>')) );
		print_r( $this->trace(__('<p>'.$user->user_email.'</p>')) );
		print_r( $this->trace(__('User already exists.  Updated usser <strong>'.$user->user_contact_name.'</strong> id '.$user_id)) );

		// UPDATE WP USER META DATA
		update_user_meta($user_id, 'first_name', 		$user->firstName );
		update_user_meta($user_id, 'last_name', 		$user->lastName );
		update_user_meta($user_id, 'wp_capabilities', array('customer'=>true));
		update_user_meta($user_id, 'member_type', 		'debenture' );
		update_user_meta($user_id, '_member_type', 		'field_55f22cb944238' );

		// UPDATE ACCOUNT / DEBENTURE NUMBERS
		$this->add_acount_numbers( $user_id, $data );

		// UPDATE BILLING ADDRESS
		$this->add_billing_address( $user_id, $data );

		// UPDATE SHIPPING ADDRESS
		$this->add_shipping_address( $user_id, $data );
		
	}



	// UPDATE ACCOUNT / DEBENTURE NUMBERS
	private function add_acount_numbers( $user_id, $data )
	{
		$deb_fields 		= array( 'field_55f2265e72fd0', 'field_5601f2dccda33', 'field_5601f318cda34' );
		$debenture_count 	= 0;

		foreach ( $data as $deb_num => $user_accounts )
		{
			update_user_meta( $user_id, 'account_number_'.($debenture_count+1), $deb_num );

			if( isset($deb_fields[$debenture_count]) ){
				update_user_meta( $user_id, '_account_number_'.($debenture_count+1), $deb_fields[$debenture_count] );
			}

			print_r( $this->trace(__( $data[ key($data) ]['ContactName'].' - Debenture Count: '.($debenture_count+1) )) );

			$debenture_count++;
		}
	}



	// UPDATE BILLING ADDRESS META DATA
	private function add_billing_address( $user_id, $data )
	{
		$bill_fields 		= array( 
			'billing_first_name' 	=> $data[ key($data) ]['FirstName'], 
			'billing_last_name' 	=> $data[ key($data) ]['LastName'],
			'billing_address_1' 	=> $data[ key($data) ]['POAddressLine1'],
			'billing_address_2' 	=> $data[ key($data) ]['POAddressLine2'],
			'billing_city' 			=> $data[ key($data) ]['POCity'],
			'billing_postcode' 		=> $data[ key($data) ]['POPostalCode'],
			'billing_country' 		=> $data[ key($data) ]['POCountry'],
			'billing_state' 		=> $data[ key($data) ]['PORegion'],
			'billing_phone' 		=> $data[ key($data) ]['PhoneNumber'],
			'billing_email' 		=> $data[ key($data) ]['EmailAddress'],
		);

		foreach ( $bill_fields as $key => $value )
		{
			update_user_meta( $user_id, $key, $value );
		}
	}



	// UPDATE SHIPPING ADDRESS META DATA
	private function add_shipping_address( $user_id, $data )
	{
		$bill_fields 		= array( 
			'shipping_first_name' 	=> $data[ key($data) ]['FirstName'], 
			'shipping_last_name' 	=> $data[ key($data) ]['LastName'],
			'shipping_address_1' 	=> $data[ key($data) ]['POAddressLine1'],
			'shipping_address_2' 	=> $data[ key($data) ]['POAddressLine2'],
			'shipping_city' 		=> $data[ key($data) ]['POCity'],
			'shipping_postcode' 	=> $data[ key($data) ]['POPostalCode'],
			'shipping_country' 		=> $data[ key($data) ]['POCountry'],
			'shipping_state' 		=> $data[ key($data) ]['PORegion'],
		);

		foreach ( $bill_fields as $key => $value )
		{
			update_user_meta( $user_id, $key, $value );
		}
	}




	// OUTPUT A STRING TRACE
	private function trace( $str )
	{
		return $this->tracer = $str.'<br />';
	}

}