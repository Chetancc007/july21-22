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

namespace Plumrocket\RMA\Plugin;

use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Model\Config\Source\Position;

class ProductViewAttributes
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Returnrule
     */
    protected $returnruleHelper;

    /**
     * @param Data             $dataHelper
     * @param Returnrule       $returnruleHelper
     */
    public function __construct(
        Data $dataHelper,
        Returnrule $returnruleHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnruleHelper = $returnruleHelper;
    }

    /**
     * Method calls after product attributes were retrieved
     */
    public function afterGetAdditionalData($subject, $data)
    {
        if ($this->dataHelper->moduleEnabled()) {
            switch (true) {
                case $this->returnruleHelper->showPosition(Position::PRODUCT):
                    if (is_array($data) && $product = $subject->getProduct()) {
                        if ($options = $this->getOptions($product)) {
                            foreach ($options as $n => $option) {
                                $code = 'prrmaresolution' . $n;
                                $data[$code] = [
                                    'label' => $option['label'],
                                    'value' => $option['value'],
                                    'code' => $code,
                                ];
                            }
                        }
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * Retrieve product options
     *
     * @param  object $item
     * @return array|null
     */
    protected function getOptions($product)
    {
        if (! $product) {
            return;
        }
        return $this->returnruleHelper->getProductOptions($product);
    }
}
