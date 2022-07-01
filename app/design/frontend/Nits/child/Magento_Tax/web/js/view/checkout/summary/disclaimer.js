/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'Magento_Customer/js/customer-data',
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/url'

], function (Component, quote, priceUtils, totals, customerData, $, confirmation, url) {
    'use strict';

    return Component.extend({
        initialize: function () {    
           var cartUrl = url.build('checkout/cart/');
           var cartData=customerData.get('cart')();
           var cartItems=cartData['items'];
           var cartProducts=[];
          
           var cartItemsLength=cartData['items'].length;
           for (var i = 0; i < cartItemsLength; i++) {
                var cartOptions=cartItems[i]['options'];
                var cartOptionsLength=cartOptions.length;
                for (var j = 0; j < cartOptionsLength; j++) {
                    var cartOptVal=cartOptions[j]['value'];
                    if(cartOptVal=="pre_purchase")  {
                        cartProducts.push(cartItems[i]['product_name']);     
                    }  
                }  
            }    
            if(cartProducts.length>0){  
                var productContent = cartProducts.toString();
                var contents = '<div class="confirmation-modal-content">';
                contents += '<b>'+productContent+'</b>'+ ' These products are pre-purchased products will be deliver based on stock availability';
                contents += '</div>';
            
            confirmation({
                title: $.mage.__('Information'),
                content:contents,
                buttons: [{
                    text: $.mage.__('Move to Cart'),
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                        window.location.href =cartUrl ;
                    }
                }, {
                    text: $.mage.__('Continue'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });  
        }
        },
        defaults: {
            isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
            template: 'Magento_Tax/checkout/summary/grand-total'
        },
        totals: quote.getTotals(),
        isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;

            if (this.totals()) {
                price = totals.getSegment('grand_total').value;
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @return {*|String}
         */
        getBaseValue: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['base_grand_total'];
            }

            return priceUtils.formatPrice(price, quote.getBasePriceFormat());
        },

        /**
         * @return {*}
         */
        getGrandTotalExclTax: function () {
            var total = this.totals();

            if (!total) {
                return 0;
            }

            return this.getFormattedPrice(total['grand_total']);
        },

        /**
         * @return {Boolean}
         */
        isBaseGrandTotalDisplayNeeded: function () {
            var total = this.totals();

            if (!total) {
                return false;
            }

            return total['base_currency_code'] != total['quote_currency_code']; //eslint-disable-line eqeqeq
        }
    });
});
