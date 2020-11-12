<?php
/**
 * Webkul Odoomagentoconnect Template IsActive Model
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Model\Template\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Odoomagentoconnect\Model\IsActive
     */
    protected $_template;

    /**
     * Constructor
     *
     * @param \Webkul\Odoomagentoconnect\Model\Template $template
     */
    public function __construct(\Webkul\Odoomagentoconnect\Model\Template $template)
    {
        $this->_template = $template;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_template->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
