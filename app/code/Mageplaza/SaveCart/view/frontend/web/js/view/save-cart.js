/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'uiComponent',
        'Magento_Ui/js/modal/modal',
        'Magento_Customer/js/model/customer'
    ],
    function ($, Component, modal, customer) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_SaveCart/checkout/cart/save-cart'
            },
            modalWindow: null,

            setModalElement: function (element) {
                var options = {
                    'type': 'popup',
                    'responsive': true,
                    'innerScroll': true,
                    'modalClass': 'mp-save-cart-content',
                    'buttons': []
                };

                this.modalWindow = element;
                modal(options, $(this.modalWindow));
            },

            moveButton: function () {
                $('.cart.main.actions').prepend($('.mpsavecart-toggle'));
            },

            showModal: function () {
                if (!customer.isLoggedIn()) {
                    $('.block-authentication').modal('openModal');
                } else {
                    $(this.modalWindow).modal('openModal');
                }
            },

            saveCartModal: function () {
                var cartName    = $('#mpsavecart-name'),
                    description = $('#mpsavecart-description');

                this.saveCartAction(cartName.val(), description.val());
                this.closeModal();
            },

            closeModal: function () {
                $(this.modalWindow).modal('closeModal');
            }

        });
    }
);
