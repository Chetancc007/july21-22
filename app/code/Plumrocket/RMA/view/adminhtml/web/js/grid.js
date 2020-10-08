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
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/adminhtml/grid'
], function (alert, confirm) {
    'use strict';

    // Rewrite the apply method because need to show confirm title and message.
    varienGridMassaction.prototype.apply = function () {
        var self = this;

        if(varienStringArray.count(this.checkedString) == 0) {
            alert({
                content: this.errorText
            });

            return;
        }

        var item = this.getSelectedItem();
        if(!item) {
            jQuery(this.form).valid();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);

        if (this.currentItem.confirm) {
            confirm({
                title: this.currentItem.confirm.title,
                content: this.currentItem.confirm.message,
                actions: {
                    confirm: this.onConfirm.bind(this, fieldName, item)
                }
            });
        } else {
            this.onConfirm(fieldName, item);
        }
    };
});
