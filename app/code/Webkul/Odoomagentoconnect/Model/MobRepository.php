<?php
/**
 * Webkul MobRepository Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */


namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\MobRepositoryInterface;
use Webkul\Odoomagentoconnect\Api\Data\CategoryInterface;
use Webkul\Odoomagentoconnect\Api\Data\CategoryInterfaceFactory;

/**
 * Defines the implementation class of the mob repository service contract.
 */
class MobRepository implements MobRepositoryInterface
{
    
    /**
     * @var MobFactory
     */
    protected $mobRepositoryFactory;

    /**
     * Constructor.
     *
     * @param MobFactory
     */
    public function __construct(
        MobRepositoryFactory $mobRepositoryFactory,
        \Webkul\Odoomagentoconnect\Model\Category $categorymodel,
        \Webkul\Odoomagentoconnect\Model\Product $productmodel,
        \Webkul\Odoomagentoconnect\Model\Template $templatemodel,
        \Webkul\Odoomagentoconnect\Model\Attribute $attributemodel,
        \Webkul\Odoomagentoconnect\Model\Option $optionmodel,
        \Magento\Catalog\Model\Category $catalogCategory,
        \Magento\Catalog\Model\Product $productManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_categorymodel = $categorymodel;
        $this->_templatemodel = $templatemodel;
        $this->_attributemodel = $attributemodel;
        $this->_productmodel = $productmodel;
        $this->_productManager = $productManager;
        $this->_optionmodel = $optionmodel;
        $this->_catalogCategory = $catalogCategory;
        $this->mobRepositoryFactory = $mobRepositoryFactory;
        $this->_objectManager = $objectManager;
    }

    /**
     * Create category mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\CategoryInterface $category
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function categoryMap(\Webkul\Odoomagentoconnect\Api\Data\CategoryInterface $category)
    {
        $this->_categorymodel->setData($category->getData());
        $this->_categorymodel->save();
        return $this->_categorymodel->getId();
    }

    /**
     * Update category name
     *
     * @param int $categoryId
     * @param string $name
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function categoryUpdate($categoryId, $name)
    {
        $category = $this->_catalogCategory
                        ->setStoreId(0)
                        ->load($categoryId);
        $category->setName($name);
        $category->save();
        return true;
    }

    /**
     * Create Product Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\ProductInterface $product
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function productMap(\Webkul\Odoomagentoconnect\Api\Data\ProductInterface $product)
    {
        $this->_productmodel->setData($product->getData());
        $this->_productmodel->save();
        return $this->_productmodel->getId();
    }

    /**
     * Create Template Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\TemplateInterface $template
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function templateMap(\Webkul\Odoomagentoconnect\Api\Data\TemplateInterface $template)
    {
        $this->_templatemodel->setData($template->getData());
        $this->_templatemodel->save();
        return $this->_templatemodel->getId();
    }

    /**
     * Create Attribute Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\AttributeInterface $attribute
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function attributeMap(\Webkul\Odoomagentoconnect\Api\Data\AttributeInterface $attribute)
    {
        $this->_attributemodel->setData($attribute->getData());
        $this->_attributemodel->save();
        return $this->_attributemodel->getId();
    }

    /**
     * Create Option Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function optionMap(\Webkul\Odoomagentoconnect\Api\Data\OptionInterface $option)
    {
        $this->_optionmodel->setData($option->getData());
        $this->_optionmodel->save();
        return $this->_optionmodel->getId();
    }


    /**
     * {@inheritdoc}
     */
    public function saveProduct(\Magento\Catalog\Api\Data\ProductInterface $product, $saveOptions = false)
    {
        if ($product->getId()) {
            $existingProduct = $this->_productManager->load($product->getId());
            if ($product->getSku() && ($product->getSku() != $existingProduct->getSku())) {
                $existingProduct->setSku($product->getSku());
                $existingProduct->save();
            }
            if (!$product->getSku() && $existingProduct->getSku()){
                $product->setSku($existingProduct->getSku());
            }
        }
        $data = $this->_objectManager
                    ->create('\Magento\Catalog\Model\ProductRepository')
                    ->save($product, $saveOptions);
        return $data;
    }

}
