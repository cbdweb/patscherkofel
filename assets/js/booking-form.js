jQuery(document).ready(function($)
{
    (function($) {

		BookingForm = {

			room_selected: false,
			filters: {
				'ids': [],
				'classes': ['availability-available'] //Default filters
			},
			bed_animating: false,
			people_in_beds: [],
			container: $('.products'),
			getFilterString: function() {

				var classes = this.filters.classes.map(function(input) {
					return '.' + input;
				});

				var ids = this.filters.ids.map(function(input) {
					return '#' + input;
				});

				var activeFilters = classes.join('');
				var activeFilters = activeFilters + ids.join(', ');

				return activeFilters;
			},
			doFiltering: function() {

				var filterString = this.getFilterString();
				var noResults = $('#no-room-results');
				noResults.hide(200);
				this.container.isotope({filter: filterString });

			},
			clearFilters: function(type, needle) {

				switch(type) {

					case 'ids':
						for(var i = 0; i < this.filters.ids.length; i++) {
							if(this.filters.ids[i].indexOf(needle) > -1) {
								this.filters.ids.splice(i, 1);
							}
						}
						break;
					case 'classes':
						for(var i = 0; i < this.filters.classes.length; i++) {
							if(this.filters.classes[i].indexOf(needle) > -1) {
								this.filters.classes.splice(i, 1);
							}
						}
						break;
				}
				
			},
			doLayout: function() {
				this.container.isotope('layout');
			},
			clearPeopleInBeds: function() {
				this.people_in_beds = [];
			},
			addPersonInBed: function(selectName, value) {

				//if($.inArray(currentValue, BookingForm.people_in_beds) === -1 && currentValue !== '') {
				if(value !== '') {
					this.people_in_beds.push([selectName, value]);
				}
			},
			removePersonInBed: function(name) {

				for(var i=0; i < this.people_in_beds.length; i++) {

					if(this.people_in_beds[i][1] == name) {
						this.people_in_beds.splice(i, 1);
					}

				}
			},
			enableDisableOptions: function(container) {

				container.find('.select-wrapper select').find('option').each(function() {
					$(this).prop('disabled', false);
				});

				for(var i=0; i < this.people_in_beds.length; i++) {

					var current = this.people_in_beds[i];

					container.find('.select-wrapper select[name!="'+ current[0] +'"]').find('option').each(function() {
						if($(this).val() !== '' && $(this).val() == current[1]) {
							$(this).prop('disabled', true);
						}
					});

				}

			}

		};

		BookingForm.container.isotope({
            itemSelector: '.type-product'
        });

		BookingForm.container.isotope('on', 'arrangeComplete', function() {

            var noResults = $('#no-room-results');

            if(!$(".type-product:visible").length > 0) {
                noResults.show(100);
            }
        });

        //Initial filtering
		BookingForm.doFiltering();

		$('select.filter').on('change', function() {

			if(BookingForm.room_selected === false) {

				var selectedOption = $(this).find(':selected');

				var target = selectedOption.data('target');

				console.log($(this));
				console.log(BookingForm.filters.classes);

				if ($.inArray(target, BookingForm.filters.classes) === -1) {

					var type = selectedOption.data('type');
					BookingForm.clearFilters('classes', type);

					if (target !== '') {
						BookingForm.filters.classes.push(target);
					}
				}

				BookingForm.doFiltering();
			}

        });

        $('.call-to-action').click(function() {

			BookingForm.room_selected = true;

			var parent = $(this).parents('.type-product');
            var parentId = parent.attr('id');
			var overallViews = parent.find('.bed-views .overall-view');
			var individualViews = parent.find('.bed-views .individual-view');

            if($.inArray(parentId, BookingForm.filters.ids) === -1) {
				BookingForm.filters.ids.push(parentId);
            }

            BookingForm.doFiltering();

			//Elements to hide
			var toHide = $(this);
			toHide = toHide.add(overallViews);
			toHide.hide(200);

			//Elements to show
			var toShow = parent.find('.single_add_to_cart_button, .clear-call-to-action');
			toShow = toShow.add(individualViews);
			toShow.show(200);

			//When animation is finished, get Isotope to layout items again due to changed dimensions
			var animatingElements = toHide.add(toShow);
			animatingElements.filter(':animated').promise().done(function() {
				BookingForm.doLayout();
			});

			//Visually indicate filters are currently disabled
			$('#room-filters').addClass('filters-disabled');
			$('#room-filters select.filter').prop('disabled', true);

        });

        $('.clear-call-to-action').click(function() {

			BookingForm.room_selected = false;
			BookingForm.filters.ids = [];
			BookingForm.doFiltering();

			var parent = $(this).parents('.type-product');
			var overallViews = parent.find('.bed-views .overall-view');
			var individualViews = parent.find('.bed-views .individual-view');

			//Remove active class from all bed links for this room
			parent.find('.bed-link').removeClass('active');
			//Add the active class back to beds that are in the cart
			var inCartBedLinks = parent.find('.bed-link.in-cart');
			inCartBedLinks.addClass('active');

			//Reset select values to values currently in cart where appropriate
			parent.find('select.selectName').each(function(item) {
				var original = $(this).data('original-value');

				if(original != '' && original != 'undefined') {
					$(this).val(original);
				}
				else {
					$(this).val('');
				}
			});

			//Clear the people in beds array
			BookingForm.clearPeopleInBeds();

			//Enable and disable options as necessary
			BookingForm.enableDisableOptions(parent);

			//Disable form submit button
			parent.find('.wc-bookings-booking-cost').unblock();
			parent.find('.single_add_to_cart_button').addClass('disabled');

			//Elements to show
			var toShow = parent.find('.call-to-action');
			toShow = toShow.add(overallViews);
			toShow.show(200);

			//Elements to hide
			var toHide = parent.find('.single_add_to_cart_button, .selectables, .wc-bookings-booking-cost, .unselected-selected-user');
			toHide = toHide.add($(this));
			toHide = toHide.add(individualViews);
			toHide.hide(200);

			//When animation is finished, get Isotope to layout items again due to changed dimensions
			var animatingElements = toHide;
			animatingElements.filter(':animated').promise().done(function() {
				BookingForm.doLayout();
			});

			//Visually indicate filters are now enabled
			$('#room-filters').removeClass('filters-disabled');
			$('#room-filters select.filter').prop('disabled', false);


        });

		//Person added or removed from bed
		$('.wc-bookings-booking-form').on('change', 'select', function() {

			selectedValue = $(this).val();
			selectedName = $(this).attr('name');

			selected_age = $(this).next().attr('name');
			selected_member_type = $(this).next().next().attr('name');
			selected_account_number = $(this).next().next().next().attr('name');
			selected_gender = $(this).next().next().next().next().attr('name');

			$('input[name="'+selected_age+'"]').val('');
			$('input[name="'+selected_member_type+'"]').val('');
			$('input[name="'+selected_account_number+'"]').val('');
			$('input[name="'+selected_gender+'"]').val('');

			for (m in member){
				if (selectedValue == member[m].name){
					//console.log(member[m].name);
					$('input[name="'+selected_age+'"]').val(member[m].age);
					$('input[name="'+selected_member_type+'"]').val(member[m].member_type);
					$('input[name="'+selected_account_number+'"]').val(member[m].account_number);
					$('input[name="'+selected_gender+'"]').val(member[m].gender);
				}
			}

			/*$('input[name="'+selected_age+'"]').val('');
			 $('input[name="'+selected_member_type+'"]').val('');
			 $('input[name="'+selected_account_number+'"]').val('');*/


			var parent = $(this).parents('.bed-item-wrapper');
			var relatedIcon = parent.find('.bed-link');
			var selectedOption = $(this).find(':selected');

			if(selectedOption.val() != '') {
				relatedIcon.addClass('active');
				$(this).siblings('.unselected-selected-user').show();
			}
			else {

				//If we're dealing with a double bed, only show it as inactive when both related select boxes are set to empty value
				if(parent.find('.bed-link .bed').hasClass('bed-double')) {

					var relatedSelectBoxesWithValues = parent.find('select').filter(function() {
						return $(this).val() != '';
					});

					var relatedSelectBoxesWithoutValues = parent.find('select').filter(function() {
						return $(this).val() == '';
					});

					relatedSelectBoxesWithoutValues.each(function() {
						$(this).siblings('.unselected-selected-user').hide();
					});

					if(relatedSelectBoxesWithValues.length < 1) {
						relatedIcon.removeClass('active');
					}
				}
				else {
					relatedIcon.removeClass('active');
					//Find related unselect button and hide it
					$(this).siblings('.unselected-selected-user').hide();
				}
			}


			var container = $(this).parents('.type-product');
			var previousValue = $(this).attr('data-previous');
			var currentValue = $(this).val();

			//Enabling/disabling options
			BookingForm.addPersonInBed($(this).attr('name'), currentValue);

			//If the previous value of the select box was not the default value,
			//remove that value from the array of disabled options
			if(previousValue !== '' && previousValue !== 'undefined' && previousValue !== null) {
				BookingForm.removePersonInBed(previousValue);
			}

			$(this).attr('data-previous', currentValue);

			console.log('previous value', previousValue);
			console.log('current value', currentValue);
			console.log('Disabled Options', BookingForm.people_in_beds);

			BookingForm.enableDisableOptions(container);

			form = $(this).closest('form');
			checkBookingForm( form, $(this).attr('name') );
		});

		//Unselect button that appears next to user selection select boxes
		$('.unselected-selected-user').click(function() {

			//Find related select and set it to default value
			$(this).siblings('select').val('').change();
		});


    })(jQuery);

	if ( ! window.console ) {
		window.console = {
			log : function(str) {
			}
		};
	}

	var xhr = [];

	var checkBookingForm = function($form) {
		
		// var index = $('.wc-bookings-booking-form').index($form);

		// if ( xhr[index] ) {
		// 	xhr[index].abort();
		// }

		var required_fields = $form.find('input.required_for_calculation');
		var filled          = true;
		$.each( required_fields, function( index, field ) {
			var value = $(field).val();
			if ( ! value ) {
				filled = false;
			}
		});
		if ( ! filled ) {
			$form.find('.wc-bookings-booking-cost').hide();
			return;
		}

		$form.find('.wc-bookings-booking-cost').block({message: null, overlayCSS: {background: '#fff', backgroundSize: '16px 16px', opacity: 0.6}}).show();
		BookingForm.doFiltering();

		$.ajax({
			type: 		'POST',
			url: 		booking_form_params.ajax_url,
			data: 		{
				action: 'psl_bookings_calculate_costs',
				//action: 'psl_set_booking_rule',
				form:   $form.serialize(),

				//userCheck : $elem
			},
			success: 	function( code ) {
				if ( code.charAt(0) !== '{' ) {
					code = '{' + code.split(/\{(.+)?/)[1];
				}

				result = $.parseJSON( code );

				if ( result.result == 'ERROR' ) {
					$form.find('.wc-bookings-booking-cost').html( result.html );
					$form.find('.wc-bookings-booking-cost').unblock();
					$form.find('.single_add_to_cart_button').addClass('disabled');
				} else if ( result.result == 'SUCCESS' ) {
					$form.find('.wc-bookings-booking-cost').html( result.html );

					//Only enable form if there is at least 1 non-default select box value selected
					if($form.find('select').filter(function(index) {
								return $(this).val() !== '';
							}).length >= 1) {

						$form.find('.wc-bookings-booking-cost').unblock();
						$form.find('.single_add_to_cart_button').removeClass('disabled');
						setDateFields($form);
					}
					else {
						$form.find('.wc-bookings-booking-cost').hide();
						$form.find('.single_add_to_cart_button').addClass('disabled');
					}

				} else {
					$form.find('.wc-bookings-booking-cost').hide();
					$form.find('.single_add_to_cart_button').addClass('disabled');
				}
			},
			error: function() {
				$form.find('.wc-bookings-booking-cost').hide();
				$form.find('.single_add_to_cart_button').addClass('disabled');
			},
			complete: function() {
				BookingForm.doLayout();
			},
			dataType: 	"html"
		});
	}

	var setDateFields = function( bookingForms )
	{
		for( var i = 0; i < bookingForms.length; i++)
		{
			$form = $(bookingForms[i]).closest('form');
			$form.append('<input type="hidden" value="'+ $('#duration').val() +'" name="wc_bookings_field_duration">');
			$form.append('<input type="hidden" value="'+ $('#from_dd').val() +'" name="wc_bookings_field_start_date_day">');
			$form.append('<input type="hidden" value="'+ $('#from_mm').val() +'" name="wc_bookings_field_start_date_month">');
			$form.append('<input type="hidden" value="'+ $('#from_yy').val() +'" name="wc_bookings_field_start_date_year">');
			$form.append('<input type="hidden" value="'+ $('#to_dd').val() +'" name="wc_bookings_field_start_date_to_day">');
			$form.append('<input type="hidden" value="'+ $('#to_mm').val() +'" name="wc_bookings_field_start_date_to_month">');
			$form.append('<input type="hidden" value="'+ $('#to_yy').val() +'" name="wc_bookings_field_start_date_to_year">');

			//checkBookingForm($form);
		}
	}

	// update all calendar values in all forms.
	//var bookingForms = $('.wc-bookings-booking-form');
	//console.log(bookingForms);
	/*for( var i = 0; i < bookingForms.length; i++){
		$form = $(bookingForms[i]).closest('form');
		$form.find('input[name="wc_bookings_field_duration"]').val($('#duration').val());
		$form.find('input[name="wc_bookings_field_start_date_day"]').val($('#from_dd').val());
		$form.find('input[name="wc_bookings_field_start_date_month"]').val($('#from_mm').val());
		$form.find('input[name="wc_bookings_field_start_date_year"]').val($('#from_yy').val());
		$form.find('input[name="wc_bookings_field_start_date_to_day"]').val($('#to_dd').val());
		$form.find('input[name="wc_bookings_field_start_date_to_month"]').val($('#to_mm').val());
		$form.find('input[name="wc_bookings_field_start_date_to_year"]').val($('#to_yy').val());

		checkBookingForm($form);
	}*/

	$( '.single_add_to_cart_button' ).on( 'click', function( event ) {
		if ( $(this).hasClass('disabled') ) {
			alert( booking_form_params.i18n_choose_options );
			event.preventDefault();
			return false;
		}
		$( this ).addClass( 'disabled' );
	});


	// $('.wc-bookings-booking-form, .wc-bookings-booking-form-button').show().removeAttr( 'disabled' );

});
