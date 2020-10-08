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
    'mage/validation',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'jquery/fileUploader/jquery.fileupload',
    'domReady!'
], function($, __, validation, confirm, alert, modal) {
    'use strict';

    /**
     * Print.
     */
    window.popWinPrint = function(url) {
        var win = window.open(url, '_blank');
        win.focus();
        setTimeout(function() {
            win.print();
        }, 2000);

        return false;
    }

    /**
     * Confirm.
     */
    window.setLocation = function(url) {
        window.location.href = url;
    };

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
     * Tracking.
     */
    $('#track-add').on('click', function() {
        $.ajax({
            url: window.trackingSubmitUrl,
            data: {
                carrier_code: $('#carrier_code').val(),
                track_number: $('#track_number').val(),
            },
            method: 'GET',
            showLoader: true,
            dataType: 'json',
            success: function(response) {
                if (response.messages) {
                    alert({
                        title: __('Tracking Information'),
                        content: response.messages.join('<br />')
                    });
                }
                if (response.success && window.trackingRowTemplate) {
                    var tmpl = window.trackingRowTemplate({
                        data: response.data
                    });

                    $(tmpl).appendTo('#returns_tracking_table tbody');
                    $('#track_number').val('');
                }
            }
        });
    });

    $('#returns_tracking_table tbody').on('click', '.track-remove', function() {
        var $row = $(this).closest('tr');
        var trackId = $row.data('id');
        var ajax = {
            url: window.trackingRemoveUrl,
            data: {track_id: trackId},
            showLoader: true,
            dataType: 'json',
            success: function(response) {
                if (response.messages) {
                    alert({
                        title: __('Tracking Information'),
                        content: response.messages.join('<br />')
                    });
                }
                if (response.success) {
                    $row.remove();
                }
            }
        };

        confirm({
            title: __('Are you sure?'),
            content: __('Your tracking number will be removed'),
            actions: {
                confirm: function() {
                    $.ajax(ajax);
                }
            }
        });
    });

    $('#carrier_code, #track_number').keypress(function(e) {
        // If enter pressed.
        if (e.which == 13) {
            $('#track-add').click();
            return false;
        }
    });

    /**
     * Address.
     */
    $('#address-edit').on('click', function() {
        var addressUrl = this.href;
        $.ajax({
            url: window.rememberFormUrl,
            data: $('#returns_form').serialize(),
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
    $('#returns_container_items .col-active input[type=checkbox]').on('change', function() {
        var $row = $(this).closest('tr');
        $row.find('.col-params .fieldset').toggle(this.checked);
    })
    .trigger('change');

    $('#returns_container_items .col-name .options').on('click', function() {
        var $this = $(this);
        if ($this.hasClass('active')) {
            $this.removeClass('active');
            $this.find('.content').hide();
        } else {
            $this.addClass('active');
            $this.find('.content').show();
        }
    });

    $('#returns_container_items .col-params select.reason_id').on('change', function() {
        var $hint = $(this).closest('fieldset.fieldset').find('.payer-hint');
        if (this.value) {
            if (-1 != window.payerOwnerReasons.indexOf(parseInt(this.value))) {
                $hint.find('.payer-owner').show();
                $hint.show();
            } else {
                $hint.find('.payer-owner').hide();
                $hint.hide();
            }
        } else {
            $hint.hide();
        }
    })
    .trigger('change');

    /**
     * Submit.
     */
     $('#returns_submit_block .returns_policy label a').on('click', function() {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: __('Return Policy'),
            buttons: [{
                text: __('Continue'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#returns_policy_popup'));

        $('#returns_policy_popup').modal('openModal');

        return false;
    });

    /**
     * Form.
     */
    var $form = $('#returns_form');
    $form.submit(function () {
        if ($($form.get(0)).validation() && $($form.get(0)).validation('isValid')) {
            $form.find('button[type="submit"]').prop('disabled', 'disabled');
            return true;
        }

        return false;
    });

});