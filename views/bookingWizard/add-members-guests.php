<?php

global $current_user, $age_types;

get_currentuserinfo();

$current_user->account_number = ( isset($_POST['multi_account_number']) ) ? $_POST['multi_account_number'] : $account_ids;

$is_admin_checkout = FALSE;


// This is tied down to ACF in the Patscherkofel User Config Settings
?>

<div class="timeline-holder">
	<div class="timeline">
		<div id="step1" class="step active">1. <span>Members/Guests</span></div>
		<div id="step2" class="step">2. <span>Dates</span></div>
		<div id="step3" class="step">3. <span>Beds</span></div>
		<div id="step4" class="step">4. <span>Payment</span></div>
	</div>
</div>


<form id="psl_add_members" class="cart woocommerce" method="post">
	<div id="member_wrapper">
		
		<?php
		$tac_functions = new tac_functions;

		// if session exists, run a loop, else display standard
		if ( isset($_SESSION['member']) && sizeof($_SESSION['member']) > 0 && (isset($_SESSION['member']['name']) && $_SESSION['member']['name'] != ' ') )
		{
			echo '<div class="">';

			foreach ($_SESSION['member'] as $person => $val)
			{
				//if( in_array('accounts', $current_user->roles) || in_array('administrator', $current_user->roles) ){ $person = ($person - 1); }

				if ($person == 0)
				{
					echo '<div class="fieldset">
						
							<fieldset><legend>Primary Member</legend>';

					echo '<h3>Name:</h3> <span>'.$_SESSION['member'][$person]['name'].'</span>';
					echo '<input type="hidden" name="member['.$person.'][name]" value="'.$_SESSION['member'][$person]['name'].'">';
					echo '<input type="hidden" name="member['.$person.'][age]" value="'.$_SESSION['member'][$person]['age'].'">';
					echo '<input type="hidden" name="member['.$person.'][gender]" value="'.$_SESSION['member'][$person]['gender'].'">';
					echo '<input type="hidden" name="member['.$person.'][member_type]" value="'.$_SESSION['member'][$person]['member_type'].'">';
					echo '<input type="hidden" name="member['.$person.'][account_number]" value="'.$_SESSION['member'][$person]['account_number'].'">';
					echo '<br><hr /><br>';
					echo '<button type="button" class="add_guest button">Add Guest</button> ';
					echo '<button type="button" class="add_member button">Add Member</button>';
					echo '</fieldset>';

					echo '</div>';
				}
				else {
						
						$guest_class = ( !isset($_SESSION['member'][$person]['account_number']) ) ? 'guestitems' : 'memberitems';

						echo '<div class="'.$guest_class.' guest-count">';
							
							
							if( !isset($_SESSION['member'][$person]['account_number']) )
							{
								// guest / acc member
								echo '<div class="name_title"><h3>Guest Name:</h3></div> <div class="name_aac"><h3>AAC Member:</h3></div> <br>';
								echo '<input type="text" name="member['.$person.'][name]" value="'.$_SESSION['member'][$person]['name'].'">';
								// ACC MEMBER
								echo '<select class="js--member-type" name="member['.$person.'][acc_member]">';
									echo '<option value="aac">No</option>';
									echo '<option value="guest">Yes</option>';
								echo '</select>';

								// AGE
								echo '<select name="member['.$person.'][age]" style="display:none;">';
								foreach ($age_types as $age) {
									$selected = ($age == $val['age']) ? 'selected' : '';
									echo '<option value="'.$age.'" '.$selected.'>'.$age.'</option>';
								}
								echo '</select>';

								
							}else{
								// member
								echo '<div class="name_title"><h3>Member #:</h3></div> <br>';
								
								echo '<input type="hidden" name="member['.$person.'][name]" value="'.$_SESSION['member'][$person]['name'].'">';
								echo '<input type="hidden" name="member['.$person.'][age]" value="'.$_SESSION['member'][$person]['age'].'">';
								echo '<input type="hidden" name="member['.$person.'][gender]" value="'.$_SESSION['member'][$person]['gender'].'">';
								echo '<input type="hidden" name="member['.$person.'][account_number]" value="'.$_SESSION['member'][$person]['account_number'].'">';
								
								echo '<input type="text" name="member['.$person.'][account_number]" value="'.$_SESSION['member'][$person]['account_number'].'" placeholder="Enter Membership #" class="js-member-id">';
								
								//echo '<input id="member-res-'.$person.'" type="text" name="member['.$person.'][name]" value="'.$_SESSION['member'][$person]['name'].'" disabled>';
								//echo '<label class="member_error"></label>';

								echo '<div id="member-res-'.$person.'" class="member-holder"a>
										<div class="s-member-first"><b>Name: </b><span>' . $_SESSION['member'][$person]['name'] .'</span></div>
									 </div>';

									
							}


							echo '<input type="hidden" name="member['.$person.'][member_type]" value="'.$_SESSION['member'][$person]['member_type'].'">';
							echo '<input type="hidden" name="member['.$person.'][account_number]" value="'.$_SESSION['member'][$person]['account_number'].'">';
							echo '<button type="button" class="delete"><i class="fa fa-fw"></i></button>';

						echo '</div>';
					
				}
			}
			echo "</div>";
		}
		else {

			echo '<script>var is_admin_checkout = 0; </script>';

			if( in_array('accounts', $current_user->roles) || in_array('administrator', $current_user->roles) )
			{
				$is_admin_checkout = TRUE;

				$_SESSION['is_admin_checkout'] = $is_admin_checkout;

				echo '<script>var is_admin_checkout = 1; </script>';
			}

			if( !$is_admin_checkout )
			{
				$_SESSION['member'] = array();
				$_SESSION['member'][0]['name'] = trim($current_user->first_name).' '.trim($current_user->last_name);
				$_SESSION['member'][0]['age'] = $current_user->age;
				$_SESSION['member'][0]['gender'] = $current_user->gender;
				$_SESSION['member'][0]['member_type'] = $current_user->member_type;
				$_SESSION['member'][0]['account_number'] = ( !is_array($current_user->account_number) ) ? $current_user->account_number : '';

		    	echo '<div class="fieldset">
								
						<fieldset><legend>Primary Member</legend>';

				echo '<h3>Name:</h3> <span>'.trim($current_user->first_name).' '.trim($current_user->last_name.'</span>');
				echo '<input type="hidden" name="member[0][name]" value="'.$_SESSION['member'][0]['name'].'">';
				echo '<input type="hidden" name="member[0][age]" value="'.$_SESSION['member'][0]['age'].'">';
				echo '<input type="hidden" name="member[0][gender]" value="'.$_SESSION['member'][0]['gender'].'">';
				echo '<input type="hidden" name="member[0][member_type]" value="'.$_SESSION['member'][0]['member_type'].'">';
				echo '<input type="hidden" name="member[0][account_number]" value="'.$_SESSION['member'][0]['account_number'].'">';
				echo '<br><hr /><br>';
				echo '<button type="button" class="add_guest button">Add Guest</button> ';
				echo '<button type="button" class="add_member button">Add Member</button>';
				echo '</fieldset>';

				echo '</div>';
			}
			else{

				echo '<div class="fieldset super-admin">
								
						<fieldset><legend>Primary Member</legend>';

				echo '<h3>Name:</h3> <span>'.trim($current_user->first_name).' '.trim($current_user->last_name.'</span>');
				echo '<br><hr /><br>';
				echo '<button type="button" class="add_guest button">Add Guest</button> ';
				echo '<button type="button" class="add_member button">Add Member</button>';
				echo '</fieldset>';

				echo '</div>';
			}
    	}

    	?>
	</div>
	<br>
	<div id="member_cache"></div>
    <input type="hidden" name="_new_members_nonce" value="<?php echo wp_create_nonce('_new_members_nonce'); ?>">
	<button type="submit" class="wc-bookings-booking-form-button button alt float-right">Next</button>
</form>


<script>

	jQuery(function($)
	{
		if( is_admin_checkout )
		{
			$('.wc-bookings-booking-form-button').prop('disabled', true);
		}

		$("#psl_add_members").on("click", ".add_guest", function (e)
		{
			$(".add_guest").prop('disabled', true);
			$(".add_member").prop('disabled', true);
			$('.wc-bookings-booking-form-button').prop('disabled', true);

			// get current number of guest
			guest = $(this).closest('.fieldset').children('.guest-count').length + 1;

			if (guest > 3) {
				alert('Sorry, you can only add a maximum of 3 guests. please contact the committee to increase your quota.');
			}
			else {



				$(this).closest('.fieldset').append('<div class="guestitems guest-count" data-id="' + guest + '">'+
						'<div class="name_title"><h3>Guest Name:</h3><input type="text" name="member['+guest+'][name]" placeholder="Enter Guest Name" class="js-guest-name"></div> <div class="name_aac"><h3>AAC Member:</h3><select class="guest-member-type js--member-type" name="member['+guest+'][acc_member]"><option value="guest" selected>No</option><option value="aac">Yes</option></select></div> <div class="name_aac"><h3>Age:</h3><select class="guest-member-type" name="member['+guest+'][age]"><?php foreach ($age_types as $age) {echo '<option value="'.$age.'">'.$age.'</option>';}?></select></div> <div class="name_aac"><h3>Gender:</h3><select class="guest-member-type" name="member['+guest+'][gender]"><<option value="Male">Male</option><<option value="Female">Female</option></select></div><div class="name_aac delete-wrapper"><button type="button" class="delete"><i class="fa fa-fw"></i></button></div>'+
						'<input id="hidden-member-type-'+ guest +'" type="hidden" name="member['+guest+'][member_type]" value="">'+
						'<input type="hidden" name="member['+guest+'][account_number]" value="">'+
						'</div>');


				$('.js-guest-name').on('keyup', function()
				{
					if( $(this).val().length > 1 )
					{
						$(".add_guest").prop('disabled', false);
						$(".add_member").prop('disabled', false);
						$('.wc-bookings-booking-form-button').prop('disabled', false);
					}else{
						$(".add_guest").prop('disabled', true);
						$(".add_member").prop('disabled', true);
						$('.wc-bookings-booking-form-button').prop('disabled', true);

					}
				});

				$('.js--member-type').on('change', function(){
					$('#hidden-member-type-'+guest).val( $(this).val() );
				});
		 	}

		 });

		rearrangeIDS();

		$("#psl_add_members").on("click", ".delete", function (e)
		{
			$(this).parent().parent("div").remove();
			$(".add_guest").prop('disabled', false);
			$(".add_member").prop('disabled', false);
			$('.wc-bookings-booking-form-button').prop('disabled', false);

			rearrangeIDS();
		});


		$("#psl_add_members").on("click", ".add_member", function (e)
		{
			$(".add_guest").prop('disabled', true);
			$(".add_member").prop('disabled', true);
			$('.wc-bookings-booking-form-button').prop('disabled', true);

			guest = $(this).closest('.fieldset').children('.guest-count').length;


			if (guest > 3) {
				alert('Sorry, you can only add a maximum of 3 guests. please contact the committee to increase your quota.');
			}
			else {
				var session = 'member['+parseInt(guest)+'][account_number]';

				$(this).closest('.fieldset').append('<div class="memberitems guest-count" data-id="' + guest + '">'+
					'<div class="name_title"><h3>Member #:</h3>'+
					'<input type="text" name="'+session+'" placeholder="Enter Membership #" class="js-member-id"><label class="member_error"></label></div>'+
					'<div class="name_aac delete-wrapper"><button type="button" class="delete member-add-only"><i class="fa fa-fw"></i></button></div>'
					);


				$('.js-member-id').on('keyup', function()
				{
					if( $(this).val().length > 1 )
					{
						$(".add_guest").prop('disabled', false);
						$(".add_member").prop('disabled', false);
						jQuery('.wc-bookings-booking-form-button').attr( 'disabled', 'false' );
					}else{
						$(".add_guest").prop('disabled', true);
						$(".add_member").prop('disabled', true);
						jQuery('.wc-bookings-booking-form-button').attr( 'disabled', 'true' );
					}
				});

				$('.js-member-id').keyup( function(){ memberKeyup( $(this) ); });
		 	}

		 	
		 	rearrangeIDS();

		 	$(".delete").on("click", function (e)
		 	{
		 		

		 		var id = $(this).parent().data('id');

		 		$('#js-member-cache-' + id).remove();
		 		//console.log(guest);
				$(this).parent().parent("div").remove();
				$(".add_guest").prop('disabled', false);
				$(".add_member").prop('disabled', false);
				$('.wc-bookings-booking-form-button').prop('disabled', false);

				rearrangeIDS();
			});

		 });


		$(this).find('.js-member-id').keyup( function(){ memberKeyup( $(this) ); });

	    var memberKeyup = function( elem, guest )
	    {
	        	if( guest == undefined )
	        	{
	        		var guest = $(elem).closest('.fieldset').children('.guest-count').length;
	        	}

	        	if( jQuery('.super-admin').length )
	        	{
	        		guest = guest-1;
	        	}


	        	if( $(elem).val().length > 1 )
				{
		        	var $parent = $(elem).parent();
		        	var $this 	= $(elem);

		        	$('#member-res-' + guest).remove();
		        	$('.load-spinner').remove();
		        	$($parent).find('.member_error').text('');
	    			$($this).before('<span class="load-spinner"><img src="' + getBaseURL() + '/wp-admin/images/loading.gif" /></span>');
	    			$('.wc-bookings-booking-form-button').prop('disabled', true);

	    			var members_added = '';
	    			$('#member_cache').children('input').each( function(i, v){
	    				members_added += $(v).val() + ',';
	    			});

		            key_up_delay(function()
		            {
		            	$.ajax({
							type: 		'POST',
							url: 		getBaseURL() + '/wp-admin/admin-ajax.php',

							data: 		{
								action		: 'get_member_details',
								member_id 	: $($parent).find('.js-member-id').val(),
								members_added : members_added,
							},
							dataType: 	"json",

							success: 	function( res )
							{
								if( res.success == '1' )
								{
									if( res.member != undefined )
									{	
										//console.log(res.member);
										//console.log(guest);

										$($this).before('<input type="hidden" name="member[' + guest + '][member_type]" value="' + res.member.member_type + '" >');
										$($this).before('<input type="hidden" name="member[' + guest + '][age]" value="' + res.member.age + '" >');
										$($this).before('<input type="hidden" name="member[' + guest + '][gender]" value="' + res.member.gender + '" >');
										$($this).before('<input type="hidden" name="member[' + guest + '][account_number]" value="' + res.member.account_number + '" >');
										$($this).before('<input type="hidden" name="member[' + guest + '][name]" value="' + res.member.first_name + ' ' + res.member.last_name + '" >');
										
										//$($this).after('<input id="member-res-' + guest + '" type="text" name="member[' + guest + '][name]" value="' + res.member.first_name + ' ' + res.member.last_name + '" disabled>');
										$($this).after('<div id="member-res-' + guest + '" class="member-holder"></div>');

										$('#member-res-' + guest).append(
											'<div class="s-member-first"><b>First Name: </b><span>' + res.member.first_name + '<span></div>' + 
											'<div class="s-member-last"><b>Surname: </b><span>' + res.member.last_name + '</span></div>' 
										);

										$('.wc-bookings-booking-form-button').prop('disabled', false);


										var memberCache = '';
										$('#member_cache').html('');

										$('.memberitems').find('.js-member-id').each(function(i,v)
										{
											$('#member_cache').append('<input id="js-member-cache-' + (i+1) + '" type="hidden" name="member_cache" value="' + $(this).val() + '">');
										});
									}
								}
								// error / no results
								if( res.success == '0' )
								{
									var txt = $($parent).find('.member_error').text( res.error );
									$('.wc-bookings-booking-form-button').prop('disabled', false);
								}

								$('.load-spinner').remove();
							},
							error: function() {

							},
							
						});

		            }, 1000 );
	        	}
	        }

	});

	// when deleted reorder ID's
	var rearrangeIDS = function()
	{
		jQuery('.guest-count').each( function(i, v)
		{
			var new_id = (i+1);

			if( is_admin_checkout )
			{
				new_id--;
			}

	 		jQuery(v).attr('data-id', new_id );

	 		jQuery(v).find('.member-holder').attr('id', 'member-res-' + new_id );

	 		jQuery( v ).find('input').each( function(n, e)
			{
				var new_name = jQuery(e).attr('name').replace( new RegExp("[0-9]", "g"), new_id );

				jQuery(e).attr('name', new_name);

				//console.log(new_id);
		 	});

	 	});




	}

	var key_up_delay = (function()
	{
	  	var timer = 0;
	  
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	var getBaseURL = function()
	{
	    var url 	= location.href;
	    var baseURL = url.substring(0, url.indexOf('/', 14));


	    if (baseURL.indexOf('http://localhost') != -1) {
	        // Base Url for localhost
	        var url 			= location.href;
	        var pathname 		= location.pathname;
	        var index1 			= url.indexOf(pathname);
	        var index2 			= url.indexOf("/", index1 + 1);
	        var baseLocalUrl 	= url.substr(0, index2);

	        return baseLocalUrl;
	    }
	    else {
	        return baseURL;
	    }

	}
</script>
