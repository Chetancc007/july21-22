<?php
/**
 * Webkul Odoomagentoconnect Tax IsActive Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\Tax\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Odoomagentoconnect\Model\IsActive
     */
    protected $_tax;

    /**
     * Constructor
     *
     * @param \Webkul\Odoomagentoconnect\Model\Tax $tax
     */
    public function __construct(\Webkul\Odoomagentoconnect\Model\Tax $tax)
    {
        $this->_tax = $tax;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_tax->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
