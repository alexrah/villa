/*------------------------------------------------------------------------
 Solidres - Hotel booking extension for Joomla
 ------------------------------------------------------------------------
 @Author    Solidres Team
 @Website   http://www.solidres.com
 @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
 @License   GNU General Public License version 3, or later
 ------------------------------------------------------------------------*/

Solidres.context = 'backend';

Solidres.jQuery(function($) {
    $.fn.srRoomType = function(params) {
        params = $.extend( {}, params);

        var bindDeleteRoomRowEvent = function() {
            $('.delete-room-row').unbind().click(function() {
                removeRoomRow(this);
            });
        };

        bindDeleteRoomRowEvent();

        removeRoomRow = function(delBtn) {
            var thisDelBtn  = $(delBtn),
                nextSpan    = thisDelBtn.next(),
                btnId       = thisDelBtn.attr('id');

            nextSpan.addClass('ajax-loading');
            if(btnId != null) {
                roomId = btnId.substring(16);
                $.ajax({
                    url     : 'index.php?option=com_solidres&task=roomtype.checkRoomReservation&tmpl=component&format=json&id=' + roomId,
                    context : document.body,
                    dataType: "JSON",
                    success : function(rs){
                        nextSpan.removeClass('ajax-loading');
                        if(!rs) {
                            // This room can NOT be deleted
                            nextSpan.addClass('delete-room-row-error');
                            nextSpan.html(Joomla.JText._('SR_FIELD_ROOM_CAN_NOT_DELETE_ROOM') +
                                ' <a class="room-confirm-delete" data-roomid="' + roomId + '" href="#">Yes</a> | <a class="room-cancel-delete" href="#">No</a>');
                            $('.tier-room').on('click', '.room-confirm-delete', function() {
                                $.ajax({
                                    url     : 'index.php?option=com_solidres&task=roomtype.removeRoomPermanently&tmpl=component&format=json&id=' + roomId,
                                    context : document.body,
                                    dataType: "JSON",
                                    success : function(rs){
                                        if(!rs) {

                                        } else {
                                            // This room can be deleted
                                            thisDelBtn.parent().parent().remove();
                                        }
                                    }
                                });
                            });
                            $('.tier-room').on('click', '.room-cancel-delete', function() {
                                nextSpan.html('');
                            });
                        } else {
                            // This room can be deleted
                            thisDelBtn.parent().parent().remove();
                        }
                    }
                });
            } else {
                // New room, can be deleted since it has not had any relationship with Reservation yet
                thisDelBtn.parent().parent().remove();
            }
        },

        initRoomRow = function() {
            var rowIdRoom   = params.rowIdRoom,
                currentId   = 'tier-room-' + rowIdRoom,
                htmlStr     = '';
            $('#room_tbl tbody').append('<tr id="' + currentId + '" class="tier-room"></tr>');
            var a   = $('#' + currentId);
            htmlStr += '<td><a class="delete-room-row btn"><i class="icon-minus-sign"></i></a></td>';
            htmlStr += '<td><input type="text" name="jform[rooms][' + rowIdRoom + '][label]" required />';
            htmlStr += '<input type="hidden" name="jform[rooms][' + rowIdRoom + '][id]" value="new" /></td>';

            a.append(htmlStr);
            bindDeleteRoomRowEvent();
        };

        $('#new-room-tier').click( function(event) {
            event.preventDefault();
            initRoomRow();
            params.rowIdRoom ++;
        });

        $('#jformreservation_asset_id').change( function(event) {
            $.ajax({
                url : 'index.php?option=com_solidres&format=json&task=coupons.find&id=' + $(this).val(),
                success : function(html) {
                    $('#coupon-selection-holder').empty().html(html);
                }
            });
            $.ajax({
                url : 'index.php?option=com_solidres&format=json&task=extras.find&id=' + $(this).val(),
                success : function(html) {
                    $('#extra-selection-holder').empty().html(html);
                }
            });
        });

        return this;
    };

    $( '#jform_partner_name' ).autocomplete({
        source: 'index.php?option=com_solidres&task=customers.find&format=json',
        minLength: 3,
        select: function(event, ui) {
            var a = $('#jform_partner_id');
            if( a.length ) {
                a.val(ui.item.id);
            } else {
                var b = $('<input />', {
                    'type'  : 'hidden',
                    'value' : ui.item.id,
                    'name'  : 'jform[partner_id]',
                    'id'    : 'jform_partner_id'
                });
                b.insertAfter( $( this ) );
            }
        }
    });

    $(".filter_checkin_checkout").datepicker({
        minDate : 0,
        numberOfMonths : 1,
        showButtonPanel : true,
        dateFormat : "dd-mm-yy",
		firstDay: 1
    });

	$(".ui-datepicker").addClass("notranslate");

    $('#customer-modal-form').submit(function(event) {
        event.preventDefault();
        var form = $(this),
            url  = form.attr( 'action' );
        $.post(
            url,
            form.serialize(),

            function( data )
            {
                if(data.saved)
                {
                    var msg = [
                        '<div id="system-message-container">',
                        '<dl id="system-message">',
                        '<dt class="message">Message</dt>',
                        '<dd class="message message">',
                        '<ul><li>Item successfully saved.</li></ul>',
                        '</dd></dl></div>',
                        '<input type="hidden" id="partner_id" name="partner_id" value="'+ data.customer_id + '" />',
                        '<input type="hidden" value="'+ data.firstname + ' ' + data.middlename + ' ' + data.lastname + '" name="jform[partner_name]" id="partner_name"/>'
                    ].join("");

                    $('#customer-modal-form').before(msg);

                    $.ajax({
                        url : 'index.php?option=com_solidres&format=json&task=customer.sendEmail&cId=' + data.customer_id,
                        success : function(jsonObj) {
                            if (jsonObj == true) {
                                var msg = [
                                    '<div id="system-message-container">',
                                    '<dl id="system-message">',
                                    '<dt class="message">Message</dt>',
                                    '<dd class="message message">',
                                    '<ul><li>Email send successfully</li></ul>',
                                    '</dd></dl></div>'
                                ].join("");
                                $('#customer-modal-form').before(msg);
                            } else {
                                var msg = [
                                    '<div id="system-message-container">',
                                    '<dl id="system-message">',
                                    '<dt class="message">Message</dt>',
                                    '<dd class="message message">',
                                    '<ul><li>Can not send email</li></ul>',
                                    '</dd></dl></div>'
                                ].join("");
                                $('#customer-modal-form').before(msg);
                            }
                        }
                    });
                }
                else {

                }
            },
            "json"
        );
    });

    $('#insert-customer').click(function() {
        var partner_name   = $('#partner_name').val();
        var partner_id     = $('#partner_id').val();
        $('#jform_partner_name', 	window.parent.document).val(partner_name);
        $('#jform_partner_id', 		window.parent.document).val(partner_id);
        parent.jQuery.fn.colorbox.close();
    });

    $(".close-colorbox").click(function() {
        parent.jQuery.fn.colorbox.close();
    });

    $('#media-library-insert').click(function(e) {
        e.preventDefault();
        $('#medialibrary input:checked').each(function() {
            if(window.parent !== null) {
                // Only insert if it was not inserted before
                var media = $(this).next();
                var mediaCssID = media.attr('id');
                var mediaName = media.attr('title');
                if($('#'+mediaCssID, window.parent.document).length == 0) {
                    var a = $('<li>');
                    var b = media.clone();
                    var c = $('<input/>',{
                        'type' : 'checkbox',
                        'name' : 'jform[deleteMediaId][]',
                        'value': mediaCssID.substring(9)
                    });
                    a.append(c).append(b).append(mediaName);
                    $('#media-holder', window.parent.document).append(a);
                    $('#item-form', window.parent.document).append($('<input/>',{
                        'type' : 'hidden',
                        'name' : 'jform[mediaId][]',
                        'value': mediaCssID.substring(9)
                    })
                    );
                }
            }
        });
        parent.jQuery.fn.colorbox.close();
    });

    $('#medialibraryform .pagination ul li a').click(function (e) {
        e.preventDefault();
        $('#medialibraryform .pagination ul li').removeClass('active');
        var self = $(this);
        self.parent().addClass('active');
        $.ajax({
            url: 'index.php?option=com_solidres&task=medialist.show&format=json',
            data: {start: self.data('start'), limit: 5},
            dataType:"html",
            success: function(data) {
                $( "#medialibrary").empty().html(data);
            }
        });
    });

    $('#media-library-delete').click(function(e) {
        var form = $('#medialibraryform');
        form.off('submit').on('submit', function(event) {
            event.preventDefault();
            var self = $(this), url = self.attr( 'action' );
            $.post( url, self.serialize(), function(response) {
                $.each(response, function(key, val) {
                    $('#sr_media_' + val).parent().remove();
                });
                mediaTabs.tabs("load", 1);
                $('#media-messsage').empty().append(
                    '<div class="alert alert-success">' + response.length + ' media deleted.' + '</div>'
                );
            }, 'json');
        });
    });

    $('.media-sortable').sortable({
        placeholder: "media-sortable-placeholder"
    });

    $('.media-sortable').disableSelection();

	var changeTaxSelectStatus = function() {
		if ($(".asset_tax_select").length) {
			if ($(".asset_tax_select").val() > 0) {
				$('.tax_select').removeAttr('disabled');
			} else {
				$('.tax_select').attr('disabled', 'disabled');
			}
		}

		if ($(".country_select").length) {
			if ($(".country_select").val() > 0) {
				$('.tax_select').removeAttr('disabled');
			} else {
				$('.tax_select').attr('disabled', 'disabled');
			}
		}
	};

	changeTaxSelectStatus();

	$(".asset_tax_select").change(function() {
		$.ajax({
			url : 'index.php?option=com_solidres&task=taxes.find&id=' + $(this).val(),
			success : function(html) {
				$('.tax_select').empty().html(html);
			}
		});

		changeTaxSelectStatus();
	});

    $(".country_select").change(function() {
        $.ajax({
            url : 'index.php?option=com_solidres&format=json&task=states.find&id=' + $(this).val(),
            success : function(html) {
                $('.state_select').empty().html(html);
            }
        });

		$.ajax({
			url : 'index.php?option=com_solidres&task=taxes.find&country_id=' + $(this).val(),
			success : function(html) {
				$('.tax_select').empty().html(html);
			}
		});

		changeTaxSelectStatus();
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

    $('#media-select-all').click(function() {
        $('.media-checkbox').prop('checked', true);
    });

    $('#media-deselect-all').click(function() {
        $('.media-checkbox').prop('checked', false);
    });


    $('#solidres').on('click', '.tariff-modal', function(e) {
        e.preventDefault();
        $.colorbox({href: $(this).attr('href'), inline: false, width:"80%", height:"80%", iframe: true});
    });
});


/*!
 * jQuery UI Widget-factory plugin boilerplate (for 1.8/9+)
 * Author: @addyosmani
 * Further changes: @peolanha
 * Licensed under the MIT license
 */

;(function ( $, window, document, undefined ) {
    $.widget( "solidres.sidenav" , {
        options: {
            selector: '#sr_side_navigation'
        },

        _create: function () {
            var self = this;
            self._restore(self);
            $(self.options.selector + ' li').on('click', 'a.sr_indicator', function() {
                self._run(this);
            });
        },

        _run: function(el) {
            var self = $(el);
            var id = self.parent().attr('id');
            var state = $.cookie(id);
            var menu = self.parent().find('ul');
            if (state == 1) {
                menu.hide();
                $.cookie(id, 0);
            } else {
                menu.show();
                $.cookie(id, 1);
            }
        },

        _restore: function (elm) {
            $(elm.options.selector + ' li').each(function(idx,elm) {
                var state = $.cookie($(elm).attr('id'));
                var menu = $(elm).find('ul');
                if (state == 1) {
                    menu.show();
                } else {
                    menu.hide();
                }
            });
        },

        destroy: function () {
            $.Widget.prototype.destroy.call(this);
        },

        _setOption: function ( key, value ) {
            $.Widget.prototype._setOption.apply( this, arguments );
        }
    });



})( Solidres.jQuery, window, document );