
jQuery(document).ready(function($) 
{
	$( "form#post #publish" ).hide();

	$( "form#post #publish" ).after("<input type=\'button\' value=\'Publish/Update\' class=\'sb_publish button-primary\' /><span style='\ display: block;text-align: center;padding: 20px;\' class=\'sb_js_errors\'></span>");

		$( ".sb_publish" ).click(function() {
			
			var error 			= true;

			var error_message 	= '';
			
			var acf_fields 		= {};


			$('input', $('#acf-date-field-ids') ).each( function(i, v)
			{
				var acf_field_id = $(v).attr('id');

				var field = $('#acf-date-field-ids')[0].children[i];

				var field_value = $( '#acf-' + acf_field_id ).val();

				var field_name = '';

				switch( i ){
					case 0 : field_name = 'from_start_date'; break;
					case 1 : field_name = 'to_end_date'; break;
					case 2 : field_name = 'from_start_date_off_peak'; break;
					case 3 : field_name = 'to_end_date_off_peak'; break;
				}

				acf_fields[field_name] = field_value;

			});


			var overlap = intervalsOverlap(
								acf_fields.from_start_date, acf_fields.to_end_date, 
								acf_fields.from_start_date_off_peak, acf_fields.to_end_date_off_peak
							);


			if( overlap > 0 )
			{
				error 			= true;

				error_message 	= 'There is an error on peak and off peak fields!\nPeak period and off peak period dates can not overlap.';

				trowException();

			}else{

				error = false;
			}


			if( acf_fields.from_start_date >= acf_fields.to_end_date )
			{
				error 			= true;

				error_message 	= 'There is an error on peak season start date!\nStart date must be set to an ealier date than the peak season end date.';

				trowException();
			}

			if( acf_fields.to_end_date <= acf_fields.from_start_date )
			{
				error 			= true;

				error_message 	= 'There is an error on peak season end date!\nEnd date must be set to a later date than the peak season start date.';

				trowException();
			}

			if( acf_fields.from_start_date_off_peak >= acf_fields.to_end_date_off_peak )
			{
				error 			= true;

				error_message 	= 'There is an error on off peak season start date!\nStart date must be set to an ealier date than the off peak season end date.';

				trowException();
			}

			if( acf_fields.to_end_date_off_peak <= acf_fields.from_start_date_off_peak )
			{
				error 			= true;

				error_message 	= 'There is an error on off peak season end date!\nEnd date must be set to a later date than the off peak season start date.';

				trowException();
			}


			if (!error)
			{
				$( "form#post #publish" ).click();

			} else {

				$(".sb_js_errors").text( error_message );
			}


			function intervalsOverlap(from1, to1, from2, to2) 
			{
			    return (to2 === null || from1 <= to2) && (to1 === null || to1 >= from2);
			}

			function trowException()
			{
				$('#major-publishing-actions').css('border', '3px solid red');

				$('li', $('.acf-tab-group') ).each( function(t, tab){

					if( $(tab).find('a').text() == 'Dates' ){

						$(tab).find('a').css('border-color', 'red');
						$(tab).find('a').click();

						$('input', $('#acf-date-field-ids') ).each( function(i, v)
						{
							var acf_field_id = $(v).attr('id');

							var field = $('#acf-date-field-ids')[0].children[i];

							var field_value = $( '#acf-' + acf_field_id );

							$(field_value).next().css('border-color', 'red');
						});
					}
				});
			}

		});


		/*var isNewBookingPage = pageMatch( 'post-new.php' );

		if( isNewBookingPage )
		{
			var meta_box = $('#normal-sortables').find(">:first-child");
				$(meta_box).css('z-index', '19000');

			$('body').prepend('<div class="blackBG"></div>');
			$('.blackBG').css('width', '100%');
			$('.blackBG').css('height', '100%');
			$('.blackBG').css('background', 'rgba(0,0,0,0.4)');
			$('.blackBG').css('position', 'absolute');
			$('.blackBG').css('z-index', '19000');

			$('.blackBG').click( function(){
				$(this).remove();
			});
		}*/

});


function pageMatch( page )
{
	var is_page = false;
    var str 	= location.href;
    var re 		= new RegExp(page, 'g');
	var res 	= str.match(re);

	if( null !== res && res.length )
	{
		is_page = true;
	}

    return is_page;
}
