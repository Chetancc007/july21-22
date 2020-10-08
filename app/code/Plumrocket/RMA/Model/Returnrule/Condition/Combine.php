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

namespace Plumrocket\RMA\Model\Returnrule\Condition;

use Magento\Rule\Model\Condition\Combine as CombineCondition;
use Magento\Rule\Model\Condition\Context;
use Plumrocket\RMA\Model\Returnrule\Condition\Cart;
use Plumrocket\RMA\Model\Returnrule\Condition\Customer;
use Plumrocket\RMA\Model\Returnrule\Condition\General;
use Plumrocket\RMA\Model\Returnrule\Condition\Product;

class Combine extends CombineCondition
{
    /**
     * Condition Product
     * @var Product
     */
    protected $conditionProduct;

    /**
     * Constructor
     * @param Context $context
     * @param Product $conditionProduct
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Product $conditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType('Plumrocket\RMA\Model\Returnrule\Condition\Combine');
        $this->conditionProduct = $conditionProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->conditionProduct->loadAttributeOptions()->getAttributeOption();
        $product = [];
        foreach ($productAttributes as $code => $label) {
            $product[] = [
                'value' => 'Plumrocket\RMA\Model\Returnrule\Condition\Product|' . $code,
                'label' => $label
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['label' => __('Current Product Page'), 'value' => $product],
        ]);

        return $conditions;
    }
}
