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

namespace Plumrocket\RMA\Model\Returns\Address;

use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Validator as AddressValidator;

/**
 * Class Validator
 */
class Validator extends AddressValidator
{
    /**
     * @var array
     */
    protected $required = [
        'order_id' => 'Order Id',
        // 'postcode' => 'Zip code',
        'lastname' => 'Last name',
        'street' => 'Street',
        'city' => 'City',
        // 'email' => 'Email',
        'telephone' => 'Phone Number',
        'country_id' => 'Country',
        'firstname' => 'First Name',
    ];

    /**
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    public function validate(Address $address)
    {
        $warnings = [];
        foreach ($this->required as $code => $label) {
            if (! $address->hasData($code)) {
                $warnings[] = sprintf('%s is a required field', $label);
            }
        }

        return $warnings;
    }
}
