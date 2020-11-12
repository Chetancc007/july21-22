<?php

namespace Webkul\Odoomagentoconnect\Api;

/**
 * @api
 */
interface MobRepositoryInterface
{
    /**
     * Create category mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\CategoryInterface $category
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function categoryMap(\Webkul\Odoomagentoconnect\Api\Data\CategoryInterface $category);

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
    public function categoryUpdate($categoryId, $name);

    /**
     * Create Product Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\ProductInterface $product
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function productMap(\Webkul\Odoomagentoconnect\Api\Data\ProductInterface $product);

    /**
     * Create Template Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\TemplateInterface $template
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function templateMap(\Webkul\Odoomagentoconnect\Api\Data\TemplateInterface $template);

    /**
     * Create Attribute Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\AttributeInterface $attribute
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function attributeMap(\Webkul\Odoomagentoconnect\Api\Data\AttributeInterface $attribute);

    /**
     * Create Option Mapping
     *
     * @param \Webkul\Odoomagentoconnect\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function optionMap(\Webkul\Odoomagentoconnect\Api\Data\OptionInterface $option);

    /**
     * Save product using id
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveProduct(\Magento\Catalog\Api\Data\ProductInterface $product, $saveOptions = false);

}
