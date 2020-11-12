<?php
/**
 * Webkul Odoomagentoconnect Carrier IsActive Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\Carrier\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Odoomagentoconnect\Model\IsActive
     */
    protected $_carrier;

    /**
     * Constructor
     *
     * @param \Webkul\Odoomagentoconnect\Model\Carrier $carrier
     */
    public function __construct(\Webkul\Odoomagentoconnect\Model\Carrier $carrier)
    {
        $this->_carrier = $carrier;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_carrier->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
