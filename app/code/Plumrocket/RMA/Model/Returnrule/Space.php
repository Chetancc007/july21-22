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

namespace Plumrocket\RMA\Model\Returnrule;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;

class Space extends AbstractModel
{
    /**
     * Retrieve space
     *
     * @param  Product $product
     * @return $this
     */
    public function getSpace(Product $product)
    {
        // Product and categories.
        if ($product->getId()) {
            $this->setData('product', $product);
        }

        return $this;
    }
}
