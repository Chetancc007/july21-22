<?php
/**
 * Webkul Odoomagentoconnect Payment IsActive Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\Payment\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Odoomagentoconnect\Model\IsActive
     */
    protected $_payment;

    /**
     * Constructor
     *
     * @param \Webkul\Odoomagentoconnect\Model\Payment $payment
     */
    public function __construct(\Webkul\Odoomagentoconnect\Model\Payment $payment)
    {
        $this->_payment = $payment;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_payment->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
