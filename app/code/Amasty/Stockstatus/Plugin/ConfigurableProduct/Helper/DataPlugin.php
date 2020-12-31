<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\ConfigurableProduct\Helper;

use Amasty\Stockstatus\Helper\Data as Helper;
use Amasty\Stockstatus\Model\Source\Outofstock;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;

class DataPlugin
{
    /**
     * @var Helper
     */
    private $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Data $subject
     * @param array $options
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function afterGetOptions(Data $subject, $options, $currentProduct, $allowedProducts)
    {
        if ($this->helper->getOutofstockVisibility() !== Outofstock::MAGENTO_LOGIC) {
            $allowAttributes = $subject->getAllowAttributes($currentProduct);

            foreach ($allowedProducts as $allowedProduct) {
                $productId = $allowedProduct->getId();
                foreach ($allowAttributes as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();
                    $productAttributeId = $productAttribute->getId();
                    $attributeValue = $allowedProduct->getData($productAttribute->getAttributeCode());
                    if (!isset($options[$productAttributeId][$attributeValue])
                        || !in_array($productId, $options[$productAttributeId][$attributeValue])
                    ) {
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
            }
        }

        return $options;
    }
}
