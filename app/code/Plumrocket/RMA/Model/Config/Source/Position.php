<?php
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

namespace Plumrocket\RMA\Model\Config\Source;

class Position extends AbstractSource
{
    // const CATEGORY              = 'category';
    const PRODUCT               = 'product';
    const SHOPPING_CART         = 'shopping_cart';
    const CHECKOUT              = 'checkout';
    const PM_ORDER_SUCCESS      = 'pm_order_success';
    const CUSTOMER_ORDER        = 'customer_order';
    const ORDER_CONFIRMATION    = 'order_confirmation';
    const INVOICE               = 'invoice';
    const SHIPMENT              = 'shipment';
    const ADMINPANEL_ORDER      = 'adminpanel_order';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $checkoutspageEnabled = $this->dataHelper
                ->moduleCheckoutspageEnabled();

            $this->options = [
                [
                    'label' => __('None'),
                    'value' => 'none',
                ],
                [
                    'label' => __('Frontend Pages'),
                    'value' => [
                        /*[
                            'value' => self::CATEGORY,
                            'label' => __('Category Page')
                        ],*/
                        [
                            'value' => self::PRODUCT,
                            'label' => __('Product Page')
                        ],
                        [
                            'value' => self::SHOPPING_CART,
                            'label' => __('Shopping Cart Page')
                        ],
                        [
                            'value' => self::CHECKOUT,
                            'label' => __('Checkout Page')
                        ],
                        [
                            'value' => self::PM_ORDER_SUCCESS,
                            'label' => __(
                                'Plumrocket Checkout Success Page' . (!$checkoutspageEnabled? ' (Not installed)' : '')
                            ),
                            'style' => (!$checkoutspageEnabled? 'color: #999;' : '')],
                        [
                            'value' => self::CUSTOMER_ORDER,
                            'label' => __('Customer Account > Order Page')
                        ],
                    ]
                ],
                [
                    'label' => __('Emails'),
                    'value' => [
                        [
                            'value' => self::ORDER_CONFIRMATION,
                            'label' => __('Order Confirmation')
                        ],
                        [
                            'value' => self::INVOICE,
                            'label' => __('Invoice')
                        ],
                        [
                            'value' => self::SHIPMENT,
                            'label' => __('Shipment')
                        ],
                    ]
                ],
                [
                    'label' => __('Admin Panel'),
                    'value' => [
                        [
                            'value' => self::ADMINPANEL_ORDER,
                            'label' => __('Order Page')
                        ],
                    ]
                ],
            ];
        }

        return $this->options;
    }
}
