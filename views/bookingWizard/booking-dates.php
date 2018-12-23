<?php

//Make sure there are no guest names that conflict with member names or each other
//print_r($_SESSION['member']);
$memberNames = array();

foreach($_SESSION['member'] as $key=>$member) {

	$theName = $member['name'];

	if(in_array($member['name'], $memberNames)) {

		//Only modify names if the member has been added as a guest
		if(isset($member['account_number']) && $member['account_number'] == '') {
			$count = 1;
			$originalName = $theName;
			while(in_array($theName, $memberNames)) {
				$theName = $originalName . '-' . $count;
				$count += 1;
			}
			$_SESSION['member'][$key]['name'] = $theName;
		}
	}

	$memberNames[] = $theName;
}



	// if session from and to session exists, populate the input fields
	$from 	= (isset($_SESSION['from']) && $_SESSION['from'] != '') ? $_SESSION['from'] : '';
	$to 	= (isset($_SESSION['to']) && $_SESSION['to'] != '') ? $_SESSION['to'] : '';

	$from_unix = $to_unix = '';

	if( '' !== $from )
	{
		$fu 		= new DateTime( str_replace('/', '-', $from) );
		$from_unix 	= $fu->format("U");
	}

	if( '' !== $to )
	{
		$tu 		= new DateTime( str_replace('/', '-', $to) );
		$to_unix 	= $tu->format("U");
	}

?>

<!-- <div class="woocommerce">
<form id="new_booking_form" class="cart" action="/my-account/rooms-available/" method="post">
	<fieldset>
		<legend>Check Availability</legend>
		<p class="form-row form-row form-row-first">
			<label>Arriving:</label> <input type="text" name="from" id="from" class="input-text " value="<?php echo $from; ?>">
		</p>
		<p class="form-row form-row-last">			
			<label>Departing: </label> <input type="text" name="to" id="to" class="input-text " value="<?php echo $to; ?>">
		</p>
	</fieldset>
	<button type="submit" class="wc-bookings-booking-form-button button alt float-right">Search</button>
	<input type="hidden" name="_booking_date_nonce" value="<?php echo wp_create_nonce('_booking_date_nonce'); ?>">
</form>
</div> -->

<div class="timeline-holder">
	<div class="timeline">
		<div id="step1" class="step active">1. <span>Members/Guests</span></div>
		<div id="step2" class="step active">2. <span>Dates</span></div>
		<div id="step3" class="step">3. <span>Beds</span></div>
		<div id="step4" class="step">4. <span>Payment</span></div>
	</div>
</div>
<div class="woocommerce">
<form id="new_booking_form" class="cart" action="/my-account/rooms-available/" method="post">
	<fieldset>
		<legend>Check Availability</legend>
		<p class="form-row form-row form-row-first">
			<label>Arriving:</label> <input type="text" name="from" id="from" class="input-text " value="<?php echo $from; ?>">
		</p>
		<p class="form-row form-row-last">			
			<label>Departing: </label> <input type="text" name="to" id="to" class="input-text " value="<?php echo $to; ?>">
		</p>
	</fieldset>
	<a href="<?php echo get_home_url(); ?>/add-members/" class="wc-bookings-booking-form-button button float-left">Update Guests</a>
	<button type="submit" class="wc-bookings-booking-form-button button alt float-right">Search</button>
	<input type="hidden" name="_booking_date_nonce" value="<?php echo wp_create_nonce('_booking_date_nonce'); ?>">
</form>
</div>

<script>
jQuery(function($) {
	
	var checkAvailable = function(date) {
		today = new Date();
		<?php

		$dates = PSL_Booking_Form::checkComingEvents();

		//print_r($dates);

		//$dates = '';
			
			echo 'dateRange = [];'."\n";

			foreach ($dates as $k => $v)
			{
				echo 'var startDate'.$k.' = "'.$v['from'].'";'."\n";
				echo 'var toDate'.$k.' = "'.$v['to'].'";'."\n";

				echo 'var d'.$k.' = new Date(startDate'.$k.');'."\n";
				echo 'if (today >= d'.$k.'.setDate(d'.$k.'.getDate() - '.$v['incubation'].')) {'."\n";
				echo 'for (d'.$k.' = new Date(startDate'.$k.'); d'.$k.' <= new Date(toDate'.$k.'); d'.$k.'.setDate(d'.$k.'.getDate() + 1)) {'."\n";
    			echo 'if (dateRange.indexOf($.datepicker.formatDate("yy-mm-dd", d'.$k.')) == -1) {';
    			echo 'dateRange.push($.datepicker.formatDate("yy-mm-dd", d'.$k.'));}'."\n";
				echo '}};'."\n";
			}


			foreach ($dates as $k => $v)
			{
				$to_off_peak = date("Y-m-d", strtotime($v['to_off_peak'].' +1 day')); // weird strange bug javascript removes the last day in for loop

				echo 'var startDate'.$k.' = "'.$v['from_off_peak'].'";'."\n";
				echo 'var toDate'.$k.' = "'.$to_off_peak.'";'."\n";
				//echo 'console.log('.  .');';

				echo 'var d'.$k.' = new Date(startDate'.$k.');'."\n";
				echo 'if (today >= d'.$k.'.setDate(d'.$k.'.getDate() - '.$v['incubation_off_peak'].')) {'."\n";
				echo 'for (d'.$k.' = new Date(startDate'.$k.'); d'.$k.' <= new Date(toDate'.$k.'); d'.$k.'.setDate(d'.$k.'.getDate() + 1)) { '."\n";
    			echo 'if (dateRange.indexOf($.datepicker.formatDate("yy-mm-dd", d'.$k.')) == -1) {';
    			echo 'dateRange.push($.datepicker.formatDate("yy-mm-dd", d'.$k.'));}'."\n";
				echo '}};'."\n";
			}


			//print_r($dates);

		?>
		//console.log(dateRange);
		// now we can display only the selected days
		if (dateRange.indexOf($.datepicker.formatDate('yy-mm-dd', date)) == -1) {
			return [false];
		}
		else {
			return [true];
		}
	}

	$( "#from" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		minDate: 0,
		dateFormat: "dd/mm/yy",
		beforeShowDay: checkAvailable,
		// maxDate:
		onClose: function( selectedDate ) {
			var from = selectedDate.split("/");
			//console.log(from);
			var minDate = new Date(from[2],from[1] - 1 ,from[0]);
         		minDate.setDate(minDate.getDate() + 1);
			$( "#to" ).datepicker( "option", "minDate", minDate );

			//$('#ftime').val('<?php echo time(' + $(this).val() + '); ?>');
			//$('#ftime').val('<?php echo time(' + $(this).val() + '); ?>');
			//var unixii = $(this).val().replace(/\//g, '-');
			//console.log( unixii );
			//
		}
	});
	$( "#to" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		minDate: 0,
		dateFormat: "dd/mm/yy",
		beforeShowDay: checkAvailable,
		onClose: function( selectedDate ) {
		}
	});
	// check for errors before submission
	$('#new_booking_form').on('submit', function() {
		if (!$('#from').val.length || !$('#to').val().length ) {
			alert('Please choose a valid date range.');
			return false;
		}
		else {
			this.submit();
		}
	});

	
	/*$('#from, #to').on('change', function() 
	{
		$.ajax({
			type: 		'POST',
			url: 		getBaseURL()+'/wp-admin/admin-ajax.php',
			data: 		{
				action	: 'psl_set_booking_rule',
				from 	: $('#from').val(),
				to 		: $('#to').val()
			},
			success: 	function( code ) {
				if ( code.charAt(0) !== '{' ) {
					code = '{' + code.split(/\{(.+)?/)[1];
				}

				result = $.parseJSON( code );
			},
			error: function() {

			},

			dataType: 	"html"
		});
	});*/


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
});
</script>
