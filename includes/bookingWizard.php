<?php
class bookingWizard
{

	private $view_template;


	public function __construct()
	{
		if (!is_user_logged_in()) {
		?>
			<script type="text/javascript">
	      		window.location= <?php echo "'" . wc_get_page_permalink( 'myaccount') . "'"; ?>;
	  		</script>
		<?php
		}

		global $post;

		$this->view_template = explode( '/', Get_page_uri($post->ID) );

		$this->view_template = end( $this->view_template );

	}


	public function psl_select_booking_date_range( $atts )
	{

		include_once(get_theme_root().'/psl/views/bookingWizard/'.$this->view_template.'.php');
		// use wordpress jquery ui datepicker
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}


	public function psl_add_members( $atts )
	{
		$tac_functions 		= new tac_functions;

		$member 			= wp_get_current_user();

		$member_meta 		= get_user_meta( $member->ID );

		$account_ids 		= array();


		// CHECK IF THE CURRENT LOGGED IN MEMBER HAS MULTIPLE ACCOUNTS
		foreach ($member_meta as $key => $meta)
		{
			if( preg_match_all('/account_number_(\d+)/i', $key) )
			{
				if( substr($key, 0, 1) != "_" )
				{
					foreach ($meta as $t => $v)
					{
						if( !empty($v) )
						{
							array_push($account_ids, $v);
						}
					}
				}
			}
		}

		if( sizeof($account_ids) <= 1 || isset($_POST['multi_account_number']) )
		{
			//psl_add_members_section( $account_ids[0], $atts );
			include_once(get_theme_root().'/psl/views/bookingWizard/'.$this->view_template.'-guests.php');
		}
		else{

			//psl_choose_id_section( $account_ids, $atts );
			include_once(get_theme_root().'/psl/views/bookingWizard/'.$this->view_template.'-multiple-ids.php');
		}
	}


	public function psl_availbe_rooms()
	{
		global $wpdb;

		$allObject 		= new stdClass();
		$postObject 	= new stdClass();
		$products 		= array();
		$booking_form 	= array();


		$productssql = $wpdb->get_results("SELECT * FROM wp_posts
											WHERE post_type = 'product';", ARRAY_A
					);

		foreach ($productssql as $key => $value)
		{
			$post = wc_get_product( $key );

			if ( isset( $post->product_type ) && $post->product_type !== 'booking' ) {
				continue;
			}

			$booking_product     = new WC_Product_Booking( $value['ID'] );

			$booking_form[]    	= new WC_Booking_Form( $booking_product );

			$products[] 		= $booking_product;

		}

		include_once(get_theme_root().'/psl/views/bookingWizard/'.$this->view_template.'.php');
	}
}
?>
