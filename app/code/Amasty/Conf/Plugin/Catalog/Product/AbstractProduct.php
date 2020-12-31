<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Catalog\Product;

use Amasty\Conf\Model\ConfigurableConfigGetter;
use Magento\Catalog\Block\Product\AbstractProduct as AbstractProductBlock;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Swatches\Model\Plugin\ProductImage;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableBlock;
use Amasty\Conf\Helper\Data;

class AbstractProduct
{
    /**
     * @var ConfigurableBlock
     */
    private $configurableBlock;

    /**
     * @var ConfigurableConfigGetter
     */
    private $configGetter;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var array
     */
    private $affectedLocations = [
        ProductImage::CATEGORY_PAGE_GRID_LOCATION,
        ProductImage::CATEGORY_PAGE_LIST_LOCATION
    ];

    public function __construct(
        ConfigurableBlock $configurableBlock,
        ConfigurableConfigGetter $configGetter,
        Data $helper
    ) {
        $this->configurableBlock = $configurableBlock;
        $this->configGetter = $configGetter;
        $this->helper = $helper;
    }

    /**
     * @param AbstractProductBlock $subject
     * @param ProductModel $product
     * @param $location
     * @param array $attributes
     * @return array
     */
    public function beforeGetImage(
        AbstractProductBlock $subject,
        ProductModel $product,
        $location,
        array $attributes = []
    ) {
        $preselect = $this->helper->getModuleConfig('preselect/preselect');
        $categoryPreselect = $this->helper->getModuleConfig('preselect/preselect_category');
        if ($categoryPreselect
            && $product->getTypeId() == ConfigurableType::TYPE_CODE
            && in_array($location, $this->affectedLocations)
            && ($preselect || $product->getSimplePreselect())
        ) {
            $this->configurableBlock->unsAllowProducts();
            $this->configurableBlock->setProduct($product);
            $preselectedData = $this->configGetter->getPreselectData($preselect, $this->configurableBlock);
            if ($preselectedData['product']) {
                $product = $preselectedData['product']->getImage() != 'no_selection'
                    ? $preselectedData['product']
                    : $product;
            }
        }

        return [$product, $location, $attributes];
    }
}
