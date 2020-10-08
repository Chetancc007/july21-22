/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'jquery/fileUploader/jquery.fileupload',
    'mage/tooltip',
    'domReady!'
], function($, __, confirm, alert) {
    'use strict';

    /**
     * Confirm.
     */
    window.actionConfirm = function(title, message, url) {
        confirm({
            title: title,
            content: message,
            actions: {
                confirm: function () {
                    setLocation(url);
                }
            }
        });

        return false;
    };

    /**
     * Info.
     */
    $('input[name=direct_link_for_customer]').on('focus click', function () {
        $(this).select();
    });

    /**
     * Tracking.
     */
    var newTrackIndex = 0;
    $('#track-add').on('click', function() {
        if (! $.trim($('#track_number').val())) {
            alert({
                title: __('Tracking Information'),
                content: __('Tracking number field cannot be empty')
            });
            return;
        }

        var tmpl = window.trackingRowTemplate({
            data: {
                carrier_code: $('#carrier_code').val(),
                track_number: $('#track_number').val(),
                index: newTrackIndex
            }
        });

        $(tmpl).appendTo('#returns_tracking_table tbody');
        $('#track_number').val('');
        newTrackIndex++;
    });

    $('#returns_tracking_table tbody').on('click', '.track-remove', function() {
        var $row = $(this).closest('tr');

        if ($row.data('id')) {
            confirm({
                title: __('Are you sure?'),
                content: __('If you confirm, your tracking number will be removed after saving this return'),
                actions: {
                    confirm: function() {
                        $row.hide();
                        $row.find('.col-action input[type=hidden]').val(1);
                    }
                }
            });
        } else {
            // Unsaved track.
            $row.remove();
        }
    });

    /**
     * Address.
     */
    $('#address-edit').on('click', function() {
        var addressUrl = this.href;
        $.ajax({
            url: window.rememberFormUrl,
            data: $('#edit_form').serialize(),
            method: 'POST',
            showLoader: true,
            dataType: 'json',
            complete: function() {
                setLocation(addressUrl);
            }
        });
        return false;
    });

    /**
     * Items.
     */
    var numberFields = [
        'qty_requested',
        'qty_authorized',
        'qty_received',
        'qty_approved'
    ];

    // Change max value for numbers.
    $('.prrma-returns-items table tbody').on('change input', 'input[type=number]', function() {
        var $childField = $(this);

        if ('' === this.value) {
            return;
        }

        // Check min and max limits.
        this.value = Math.max(this.value, 0);
        this.value = Math.min(this.value, this.max);

        // Set max limit to the next number in a row.
        // var $next = $childField.closest('td').next('td').find('input[type=number]');
        // $next.prop('max', this.value);

        // Set max limit to the all next numbers in a row.
        var $nextAll = $childField.closest('td').nextAll('td').find('input[type=number]');
        // $nextAll.prop('max', this.value);
        var maxValue = this.value;
        $nextAll.each(function() {
            $(this).prop('max', maxValue);
            if (parseInt(this.value) > maxValue) {
                this.value = maxValue;
            }
            maxValue = Math.min(this.value, maxValue);
        });

        // Modify parent number if child number is changed.
        if ($childField.closest('tr').hasClass('row-cloned')) {
            var prev = parseInt($childField.data('prev')) || 0;
            var current = parseInt(this.value) || 0;
            $childField.data('prev', current);

            var $child = $childField.closest('tr');
            var $parent = $child.parent().find('tr[data-item="' + $child.data('item') + '"]:not(.row-cloned)');
            var $parentField = $parent.find('.' + $childField.closest('td').attr('class') + ' input');
            var val = parseInt($parentField.val());
            if (NaN != val) {
                $parentField.val(
                    val - (current - prev)
                )
                .trigger('change')
                .trigger('input');
            }
        }
    });

    $('.prrma-returns-items table tbody .col-qty_requested input[type=number]')
        .trigger('change')
        .trigger('input');


    // Split.
    $('.prrma-returns-items').on('click', '.col-action a.row-clone', function() {
        var $parent = $(this).closest('tr');
        var $child = $parent.clone().addClass('row-cloned');

        // Prepare new item.
        $child.html($child.html().replace(/items\[[^\]]+\]/g, 'items[' + Date.now() + ']'));
        $child.find('input[type=hidden][id$="_entity_id"]').val('');
        $child.find('.col-status span').hide();

        // Decrement number fields.
        $child.find('input[type=number]').val('');
        // Work with reversed array because in original order changing left column can change right column.
        $.each(numberFields.slice().reverse(), function(i, name) {
            var $parentField = $parent.find('.col-' + name + ' input');
            var $childField = $child.find('.col-' + name + ' input');
            var n = $parentField.val();
            if (n > 1) {
                $parentField.val(n - 1)/*.data('prev', n - 1)*/.trigger('change');
                $childField.val(1).data('prev', 1).trigger('change');
            }

            if ('qty_requested' != name) {
                $childField.prop('max', 1);
            }
        });

        $parent.after($child);

        return false;
    });

    // Cancel split.
    $('.prrma-returns-items').on('click', '.col-action a.row-clone-cancel', function() {
        var $child = $(this).closest('tr');
        var $parent = $child.parent().find('tr[data-item="' + $child.data('item') + '"]:not(.row-cloned)');

        // Increase parent number fields.
        $.each(numberFields, function(i, name) {
            var $childfield = $child.find('.col-' + name + ' input');
            var n = parseInt($childfield.val()) || 0;
            if (n > 0) {
                var $parentField = $parent.find('.col-' + name + ' input');

                if ($parentField.val()) {
                    n += parseInt($parentField.val());
                    $parentField.val(n)/*.data('prev', n)*/.trigger('change');
                }
            }
        });

        $child.remove();

        return false;
    });

    $('.prrma-returns-items table tbody .col-reason_id select').on('change', function() {
        var $hint = $(this).closest('.col-reason_id').find('.payer-hint');
        if (this.value) {
            if (-1 != window.payerOwnerReasons.indexOf(parseInt(this.value))) {
                $hint.find('.payer-owner').show();
                $hint.find('.payer-customer').hide();
            } else {
                $hint.find('.payer-owner').hide();
                $hint.find('.payer-customer').show();
            }
            $hint.show();
        } else {
            $hint.hide();
        }
    })
    .trigger('change');

    $('.prrma-returns-items table th').tooltip({
        position: { my: 'bottom', at: 'top' }
    });

    /**
     * Messages.
     */
    $('#mark_as_read').on('click', function() {
        $.ajax({
            url: window.markAsReadUrl,
            // data: {form_key: FORM_KEY},
            method: 'GET',
            showLoader: true,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#mark_as_read').hide();
                }
            }
        });
        return false;
    });

    $('#returns_comment_template').on('change', function() {
        if (! this.value) {
            return;
        }

        var ajax = {
            url: window.responseTemplateUrl,
            data: {id: this.value},
            method: 'GET',
            showLoader: true,
            dataType: 'json',
            success: function(data) {
                if (data.message) {
                    $('#returns_comment').val(data.message);
                    var $editor = tinyMCE.get('returns_comment');
                    if ($editor != undefined) {
                        $editor.setContent(data.message);
                    }
                }
            },
            complete: function() {
                $('#returns_comment_template').val('0');
            }
        };

        if ($('#returns_comment').val()) {
            confirm({
                title: 'Are you sure?',
                content: 'Your current message will be changed',
                actions: {
                    confirm: function() {
                        $.ajax(ajax);
                    },
                    cancel: function() {
                        $('#returns_comment_template').val('0');
                    }
                }
            });
        } else {
            $.ajax(ajax);
        }
    });

    $('#returns_comment_internal').on('change', function() {
        $('#returns_comments_form').toggleClass('comment-internal', this.checked);
        $('#returns_comment_send_email').prop('disabled', this.checked);
        if (this.checked) {
            $('#returns_comment_send_email').prop('checked', false);
        }
    })
    .change();

});