/*------------------------------------------------------------------------
 Solidres - Hotel booking extension for Joomla
 ------------------------------------------------------------------------
 @Author    Solidres Team
 @Website   http://www.solidres.com
 @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
 @License   GNU General Public License version 3, or later
 ------------------------------------------------------------------------*/

if (typeof(Solidres) === 'undefined') {
    var Solidres = {};
}

Solidres.context = 'frontend';

Solidres.setCurrency = function(id) {
    Solidres.jQuery.ajax({
        type: 'POST',
        url: window.location.pathname,
        data: 'option=com_solidres&format=json&task=currency.setId&id='+parseInt(id),
        success: function(msg) {
            location.reload(true);
        }
    });
};

Solidres.jQuery(function($) {
    if (document.getElementById('sr-reservation-form-room')) {
        $('#sr-reservation-form-room').validate();
    }

    if (document.getElementById("sr-checkavailability-form")) {
        $("#sr-checkavailability-form").validate();
    }

	var isAtLeastOnRoomTypeSelected = function() {
		var numberRoomTypeSelected = 0;
		$(".roomtype-quantity-selection").each(function() {
			if ($(this).val() > 0) {
				numberRoomTypeSelected ++;
				return;
			}
		});

		if (numberRoomTypeSelected > 0) {
			$('#sr-reservation-form-room button[type="submit"]').removeAttr('disabled');
		} else {
			$('#sr-reservation-form-room button[type="submit"]').attr('disabled', 'disabled');
		}
	};

	isAtLeastOnRoomTypeSelected();

	$(".roomtype-quantity-selection").change(function() {
		isAtLeastOnRoomTypeSelected();
	});

    if (document.getElementById("sr-availability-form")) {
        $("#sr-availability-form").validate();
    }

	function toggleRoomTypeDetails(target) {
		var room_type_details = $('div.' + target );
		if (room_type_details.hasClass('hidden')) {
			room_type_details.removeClass('hidden');
		} else {
			room_type_details.addClass('hidden');
		}
	}

	var currenthash = window.location.hash;
	if (currenthash.indexOf('room_type_details_handler') > -1) {
		toggleRoomTypeDetails(currenthash.substring(1));
	}

    $('a.room_type_details').click(function(e) {
        e.preventDefault();
		toggleRoomTypeDetails($(this).attr('id'));
    });

    $('#coupon_code').blur(function() {
        var self = $(this);
        var coupon_code = self.val();
        if (coupon_code) {
            $.ajax({
                type: 'POST',
                url: window.location.pathname,
                data: 'option=com_solidres&format=json&task=coupon.isValid&coupon_code=' + coupon_code + '&raid=' + $('input[name="id"]').val(),
                success: function(response) {
                    self.next().remove();
                    self.after(response.message);
                    if (!response.status) {
                        $('#apply-coupon').attr('disabled', 'disabled');
                    } else {
                        $('#apply-coupon').removeAttr('disabled');
                    }
                },
                dataType: 'JSON'
            });
        }
    });

    $('.coupon').on('click', '#apply-coupon', function () {
        $.ajax({
            type: 'POST',
            url: window.location.pathname,
            data: 'option=com_solidres&format=json&task=coupon.applyCoupon&coupon_code=' + $('#coupon_code').val() + '&raid=' + $('input[name="id"]').val(),
            success: function(response) {
                if (response.status) {
                    location.reload(true);
                }
            },
            dataType: 'JSON'
        });
    });

    $('#sr-remove-coupon').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: window.location.pathname,
            data: 'option=com_solidres&format=json&task=reservation.removeCoupon&id=' + $(this).data('couponid'),
            success: function(response) {
                if (response.status) {
                    location.reload(true);
                } else {
                    alert(Joomla.JText._('SR_CAN_NOT_REMOVE_COUPON'));
                }
            },
            dataType: 'JSON'
        });
    });

    $('.confirmation').on('click', '#termsandconditions', function() {
        var self = $(this),
            submitBtn = $('.confirmation').find('button[type=submit]');
        if (self.is(':checked')) {
            submitBtn.removeAttr('disabled');
        } else {
            submitBtn.attr('disabled', 'disabled');
        }
    });

    $('#solidres .guestinfo').on('change', '.country_select', function() {
        $.ajax({
            url : 'index.php?option=com_solidres&format=json&task=states.find&id=' + $(this).val(),
            success : function(html) {
                if (html.length > 0) {
                    $('.state_select').empty().html(html);
                }
            }
        });
    });

    $('button.load-calendar').click(function() {
        var self = $(this);
        var id = self.data('roomtypeid');
		var target = $('#availability-calendar-' + id);

		self.empty().html('<i class="icon-calendar uk-icon-calendar fa-calendar"></i> ' + Joomla.JText._('SR_PROCESSING'));
		self.attr('disabled', 'disabled');

		if (target.children().length == 0) {
			$.ajax({
				url : 'index.php?option=com_solidres&task=reservationasset.getAvailabilityCalendar&id=' + id,
				success : function(html) {
					self.removeAttr('disabled');
					if (html.length > 0) {
						target.show().html(html);
						self.empty().html('<i class="icon-calendar uk-icon-calendar fa-calendar"></i> ' + Joomla.JText._('SR_AVAILABILITY_CALENDAR_CLOSE'))
					}
				}
			});
		} else {
			target.empty().hide();
			self.empty().html('<i class="icon-calendar uk-icon-calendar fa-calendar"></i> ' + Joomla.JText._('SR_AVAILABILITY_CALENDAR_VIEW'));
			self.removeAttr('disabled');
		}
    });

    var submitReservationForm = function(form) {
        var self = $(form),
            url = self.attr( 'action'),
            formHolder = self.parent('.reservation-single-step-holder'),
            submitBtn = self.find('button[type=submit]'),
            currentStep = submitBtn.data('step');

        submitBtn.attr('disabled', 'disabled');
        submitBtn.html('<i class="icon-arrow-right uk-icon-arrow-right fa-arrow-right"></i> ' + Joomla.JText._('SR_PROCESSING'));
        $.post( url, self.serialize(), function(data) {
            if (data.status == 1) {
                $.ajax({
                    type: 'GET',
                    cache: false,
                    url: 'index.php?option=com_solidres&task=reservation.progress&next_step='+data.next_step,
                    success: function(response) {
                        formHolder.addClass('nodisplay');
                        submitBtn.removeClass('nodisplay');
                        submitBtn.html('<i class="icon-arrow-right uk-icon-arrow-right fa-arrow-right"></i> ' + Joomla.JText._('SR_NEXT'));
                        var next = $('.' + data.next_step);
                        next.removeClass('nodisplay');
                        next.prev().addClass('nodisplay');
                        next.empty().append(response);
                        if (data.next == 'payment') {
                            $.metadata.setType("attr", "validate");
                        }
						location.hash = '#form';
                        $('.reservation-tab').removeClass('active');
                        $('.reservation-tab-' + currentStep).addClass('complete');
                        $('.reservation-tab-' + currentStep + ' span.badge').removeClass('badge-info').addClass('badge-success');
                        $('.reservation-tab-' + data.next_step).addClass('active');
                        $('.reservation-tab-' + data.next_step + ' span.badge').addClass('badge-info');
                        var next_form = next.find('form.sr-reservation-form');
                        if (next_form.attr('id') == 'sr-reservation-form-guest') {
                            next_form.validate({
                                rules: {
                                    'jform[customer_email]': { required: true, email: true },
                                    'jform[payment_method]': { required: true },
									'jform[customer_password]' : {require: false, minlength: 8},
									'jform[customer_username]': {
										required: false,
										remote: {
											url: 'index.php?option=com_solidres&task=user.check&format=json',
											type: 'POST',
											data: {
												username: function() {
													return $('#username').val();
												}
											}
										}
									}
                                },
								messages: {
									'jform[customer_username]': {
										remote: Joomla.JText._('SR_USERNAME_EXISTS')
									}
								}
                            });
                            $(".popover_payment_methods").popover({
                                "trigger" : "click",
                                "placement" : "bottom"
                            });

							$('.extra_desc_tips').popover('destroy');
							$('.extra_desc_tips').popover({
								html: true,
								placement: "bottom",
								trigger: "click"
							});

                        } else {
                            next_form.validate();
                        }
                    }
                });
            }
        }, "json");
    }

    $('#solidres').on('submit', 'form.sr-reservation-form', function (event) {
        event.preventDefault();
        submitReservationForm(this);
    });

    $('#solidres .room').on('change', '.trigger_tariff_calculating', function(event, updateChildAgeDropdown) {
        var self = $(this);
        var raid = self.data('raid');
        var roomtypeid = self.data('roomtypeid');
        var roomindex = self.data('roomindex');
		var tariffid = self.data('tariffid');
        var adult_number = $('.occupancy_adult_' + roomtypeid + '_' + tariffid + '_' + roomindex).val();
        var child_number = $('.occupancy_child_' + roomtypeid + '_' + tariffid + '_' + roomindex).val();

		if (typeof updateChildAgeDropdown === 'undefined' || updateChildAgeDropdown === null ) {
			updateChildAgeDropdown = true;
		}

		if ( !updateChildAgeDropdown && self.hasClass('reservation-form-child-quantity') ) {
			return;
		}

		if (self.hasClass('reservation-form-child-quantity') && child_number >= 1 ) {
			return;
		}

        var data = {};
        data.raid = raid;
        data.room_type_id = roomtypeid;
        data.room_index = roomindex;
        data.adult_number = adult_number;
        data.child_number = child_number;
		data.tariff_id = tariffid;

        for (var i = 0; i < child_number; i++) {
            var prop_name = 'child_age_' + roomtypeid + '_' + tariffid + '_' + roomindex + '_' + i;
            data[prop_name] = $('.' + prop_name).val();
        }

        $.ajax({
            type: 'GET',
            url: 'index.php?option=com_solidres&task=reservationasset.calculateTariff&format=json',
            data: data,
            success: function(data) {
                var  target = '.tariff_' + roomtypeid + '_' + tariffid + '_' + roomindex;
                if (!data.room_index_tariff.code && !data.room_index_tariff.value) {
                    $( target ).text('0');
                } else {
                    $( target ).text(data.room_index_tariff.formatted);
                    $('.complex_tariff_break_down_'  + roomtypeid + '_' + tariffid + '_' + roomindex).popover('destroy');
                    $('.complex_tariff_break_down_'  + roomtypeid + '_' + tariffid + '_' + roomindex).popover({
                        html: true,
                        content: function() {return data.room_index_tariff_breakdown_html},
                        title: Joomla.JText._('SR_TARIFF_BREAK_DOWN'),
                        placement: "bottom",
                        trigger: "click"
                    });
					$('.extra_desc_tips').popover('destroy');
					$('.extra_desc_tips').popover({
						html: true,
						placement: "bottom",
						trigger: "click"
					});
                }
            },
            dataType: "json"
        });
    });

    function loadRoomForm(self) {
        var rtid = self.data('rtid');
        var raid = self.data('raid');
		var tariffid = self.data('tariffid');
        $.ajax({
            type: 'GET',
            url: 'index.php?option=com_solidres&task=reservationasset.getRoomTypeForm',
            data: {rtid: rtid, raid: raid, tariffid: tariffid, quantity: self.val()},
            success: function(data) {
                self.parent().find('.processing').css({'display': 'none'});
                $('#room-form-' + rtid + '-' + tariffid).empty().show().html(data);
                $('.sr-reservation-form').validate();
				var updateChildAgeDropdown = false; // trigger change at this time will update the child age form too, we dont want that!
                $('#solidres .room #room-form-' + rtid + '-' + tariffid + ' .trigger_tariff_calculating').trigger('change', [updateChildAgeDropdown]);
            }
        });
    }

    // In case the page is reloaded, we have to reload the previous submitted room type selection form
    $('.roomtype-quantity-selection').each(function() {
        var self = $(this);
        if ( self.val() > 0) {
            self.parent().find('.processing').css({'display': 'block'});
			$('#selected_tariff_' + self.data('rtid') + '_' + self.data('tariffid')).removeAttr("disabled");
            loadRoomForm(self);
        }
    });

    $('.roomtype-quantity-selection').change(function() {
        var self = $(this);
		var tariffid = self.data('tariffid');
		var rtid = self.data('rtid');
		var totalRoomsLeft = self.data('totalroomsleft');
		var currentQuantity = parseInt(self.val());
		var currentSelectedRoomTypeRooms = 0;
		var totalSelectableRooms = 0;
        if ( currentQuantity > 0) {
			self.parent().find('.processing').css({'display': 'block'});
			$('#selected_tariff_' + rtid + '_' + tariffid).removeAttr("disabled");
            loadRoomForm(self);
        } else {
            $('#room-form-' + rtid + '-' + tariffid).empty().hide();
			$('input[name="jform[selected_tariffs][' + rtid + ']"]').attr("disabled", "disabled");
			$('#selected_tariff_' + rtid + '_' + tariffid).attr("disabled", "disabled");
        }

		$('.quantity_' + rtid).each(function() {
			var s = $(this);
			var val = parseInt(s.val());
			if (val > 0) {
				currentSelectedRoomTypeRooms += val;
			}
		});

		totalSelectableRooms = totalRoomsLeft - currentSelectedRoomTypeRooms;

		$('.quantity_' + rtid).each(function() {
			var s = $(this);
			var val = parseInt(s.val());
			var from = 0;
			if (val > 0) {
				from = val + totalSelectableRooms;
			} else {
				from = totalSelectableRooms;
			}
			disableOptions(s, from);
		});

		if (totalSelectableRooms > 0 && totalSelectableRooms < totalRoomsLeft) {
			$('#num_rooms_available_msg_' + rtid).empty().text(Joomla.JText._('SR_ONLY_' + totalSelectableRooms + '_LEFT'));
		} else if (totalSelectableRooms == 0) {
			$('#num_rooms_available_msg_' + rtid).empty();
		} else {
			$('#num_rooms_available_msg_' + rtid).empty().text($('#num_rooms_available_msg_' + rtid).data('original-text'));
		}


    });

	function disableOptions(selectEl, from) {
		$('option', selectEl).each(function() {
			var val = parseInt($(this).attr('value'));
			if (val > from) {
				$(this).attr('disabled', 'disabled');
			} else {
				$(this).removeAttr('disabled');
			}
		});
	}

    $('#solidres').on('change', '.reservation-form-child-quantity', function (event, updateChildAgeDropdown) {
		if (typeof updateChildAgeDropdown === 'undefined' || updateChildAgeDropdown === null ) {
			updateChildAgeDropdown = true;
		}
		if (!updateChildAgeDropdown) {
			return;
		}
        var self = $(this);
        var quantity = self.val();
        var html = '';
        var raid = self.data('raid');
        var roomtypeid = self.data('roomtypeid');
        var roomindex = self.data('roomindex');
		var tariffid = self.data('tariffid');

        if (quantity > 0) {
            self.next('.child-age-details').removeClass('nodisplay');
        } else {
            self.next('.child-age-details').addClass('nodisplay');
        }

        for (var i = 0; i < quantity; i ++) {
            html += '<li>' + Joomla.JText._('SR_CHILD') + ' ' + (i + 1) +
				'<select name="jform[room_types][' + roomtypeid + '][' + tariffid + ']['+ roomindex +'][children_ages][]" ' +
                'data-raid="' + raid + '"' +
                'data-roomtypeid="' + roomtypeid + '"' +
                'data-roomindex="' + roomindex + '"' +
				'data-tariffid="' + tariffid + '"' +
                'required ' +
                'class="span6 child_age_' + roomtypeid + '_' + tariffid + '_' + roomindex + '_' + i + ' trigger_tariff_calculating"> ';

			html += '<option value=""></option>';

			for (var age = 1; age <= Solidres.child_max_age_limit; age ++) {
				html += '<option value="' + age + '">' +
					(age > 1 ? age + ' ' + Joomla.JText._('SR_CHILD_AGE_SELECTION_JS') : age + ' ' + Joomla.JText._('SR_CHILD_AGE_SELECTION_1_JS'))  +
					'</option>';
			}

            html += '</select></li>';
        }

        self.next('.child-age-details').find('ul').empty().append(html);
    });

    $('#solidres').on('click', '.reservation-navigate-back', function() {
        $('.reservation-tab').removeClass('active');
        $('.reservation-single-step-holder').removeClass('nodisplay').addClass('nodisplay');
        var self = $(this);
        var currentstep = self.data('step');
        var prevstep = self.data('prevstep');
        var active = $('.' + prevstep).removeClass('nodisplay');
        active.find('button[type=submit]').removeAttr('disabled');
        $('.reservation-tab-' + prevstep).addClass('active').removeClass('complete');
        $('.reservation-tab-' + prevstep + ' span.badge').removeClass('badge-success').addClass('badge-info');
        $('.reservation-tab-' + currentstep + ' span.badge').removeClass('badge-info');
    });

    $('.guestinfo').on('click', 'input:checkbox', function() {
        var self = $(this);
        if (self.is(':checked')) {
            $('.' + self.data('target') ).removeAttr('disabled');
        } else {
            $('.' + self.data('target') ).attr('disabled', 'disabled');
        }
    });

    $('.room-form').on('click', 'input:checkbox', function() {
        var self = $(this);
        if (self.is(':checked')) {
            $('.' + self.data('target') ).removeAttr('disabled');
        } else {
            $('.' + self.data('target') ).attr('disabled', 'disabled');
        }
    });

	$("#reservationnote-form").submit(function(e) {
		e.preventDefault();

		var form = $(this),
			url  = form.attr( 'action'),
			submitBtn = form.find('button[type=submit]'),
			processingIndicator = form.find('div.processing');

		submitBtn.attr('disabled', 'disabled');
		submitBtn.addClass('nodisplay');
		processingIndicator.removeClass('nodisplay');
		processingIndicator.addClass('active');
		$.post(
			url,
			form.serialize(),
			function( data ) {
				if(data.status) {
					submitBtn.removeClass('nodisplay');
					submitBtn.removeAttr('disabled', 'disabled');
					processingIndicator.addClass('nodisplay');
					processingIndicator.removeClass('active');
					$('.reservation-note-holder').append(
						$('<div class="reservation-note-item"><p class="info">'
							+ data.created_date + ' by '
							+ data.created_by_username + '</p>'
							+ '<p>' + Joomla.JText._('SR_RESERVATION_NOTE_NOTIFY_CUSTOMER') + ': ' + data.notify_customer +  ' | '
							+ '' + Joomla.JText._('SR_RESERVATION_NOTE_DISPLAY_IN_FRONTEND') + ': ' + data.visible_in_frontend +  '</p>'
							+ '<p>' + data.text  + '</p></div>'
						)) ;
					form.children('textarea').val('');
				}
			},
			"json"
		);
	});

	$('.trigger_checkinoutform').click(function() {
		var self = $(this);
		var tariffId = self.data('tariffid');
		var roomtypeId = self.data('roomtypeid');
		var oldLabel = self.text();

		if (tariffId != '') {
			$('.checkinoutform').empty();
			self.text(Joomla.JText._('SR_PROCESSING'));
			$.ajax({
				type: 'GET',
				data: {Itemid : self.data('itemid'), id: self.data('assetid'), roomtype_id: roomtypeId, tariff_id : tariffId},
				url: 'index.php?option=com_solidres&task=reservationasset.getCheckInOutForm',
				success: function(data) {
					$('.checkinoutform').empty();
					$('#checkinoutform-' + roomtypeId + '-' + tariffId).show().empty().html(data);
					$('#room-form-' + roomtypeId + '-' + tariffId).empty();
					self.text(oldLabel);
				}
			});
		}
	});

	$('#solidres').on('click', '.searchbtn', function() {
		var tariffid = $(this).data('tariffid');
		var roomtypeid = $(this).data('roomtypeid');
		$('#sr-checkavailability-form-component').attr('action', $('#sr-checkavailability-form-component').attr('action') + '#tariff-box-' + roomtypeid + '-' + tariffid);
		$('#sr-checkavailability-form-component input[name=checkin]').val($('#tariff-box-' + roomtypeid + '-' + tariffid + ' input[name="checkin"]').val());
		$('#sr-checkavailability-form-component input[name=checkout]').val($('#tariff-box-' + roomtypeid + '-' + tariffid + ' input[name="checkout"]').val());
		$('#sr-checkavailability-form-component input[name=ts]').val($('input[name=fts]').val());
		$('#sr-checkavailability-form-component').submit();
	});

	$('.toggle_more_desc').click(function() {
		var self = $(this);
		$('#more_desc_' + self.data('target')).toggle();
		if ($('#more_desc_' + self.data('target')).is(':visible')) {
			self.empty().html('<i class="icon-eye-close uk-icon-eye fa-eye"></i> ' + Joomla.JText._('SR_HIDE_MORE_INFO'));
		} else {
			self.empty().html('<i class="icon-eye-open uk-icon-eye-slash fa-eye-slash"></i> ' + Joomla.JText._('SR_SHOW_MORE_INFO'));
		}

	});

	$('#sr-reservation-form-room').on('click', '.checkin_roomtype', function() {
		if (!$(this).hasClass("disabledCalendar")) {
			$('.checkin_datepicker_inline').slideToggle('slow', function() {
				if ($(this).is(":hidden")) {
					$(".checkout_roomtype").removeClass("disabledCalendar");
				} else {
					$(".checkout_roomtype").addClass("disabledCalendar");
				}
			});
		}
	});

	$('#sr-reservation-form-room').on('click', '.checkout_roomtype', function() {
		if (!$(this).hasClass("disabledCalendar")) {
			$('.checkout_datepicker_inline').slideToggle('slow', function() {
				if ($(this).is(":hidden")) {
					$(".checkin_roomtype").removeClass("disabledCalendar");
				} else {
					$(".checkin_roomtype").addClass("disabledCalendar");
				}
			});
		}
	});

	$('.guestinfo').on('click', '#register_an_account_form', function() {
		var self = $(this);
		if (self.is(':checked')) {
			$('.' + self.attr('id') ).show();
		} else {
			$('.' + self.attr('id') ).hide();
		}
	});

	$('.guestinfo').on('blur', '#username', function() {
		$.ajax({
			type: 'POST',
			data: {username : this.value},
			url: 'index.php?option=com_solidres&task=user.check&format=json',
			success: function(data) {
				if (data.status == false) {
					console.log('username exists');
				}
			},
			dataType: 'JSON'
		});
	});
});
