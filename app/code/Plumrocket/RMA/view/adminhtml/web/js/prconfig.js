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
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require([
    'jquery',
    'domReady!'
], function($) {
    'use strict';

    $("#prrma_general_return_placement").change(function () {
        var optionSelected = $(this).find("option:selected");
        if (optionSelected.val() == "none") {
            optionSelected.each(function() {
                this.selected = false;
            });

            $(this).children("option[value=none]").prop("selected", true);
        }
    });
});
