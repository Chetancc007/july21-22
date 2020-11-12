<?php
/**
 * Webkul Odoomagentoconnect Category IsActive Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\Category\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Odoomagentoconnect\Model\IsActive
     */
    protected $_category;

    /**
     * Constructor
     *
     * @param \Webkul\Odoomagentoconnect\Model\Category $category
     */
    public function __construct(\Webkul\Odoomagentoconnect\Model\Category $category)
    {
        $this->_category = $category;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_category->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
