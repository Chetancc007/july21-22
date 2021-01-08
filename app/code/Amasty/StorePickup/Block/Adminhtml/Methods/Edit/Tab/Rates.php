<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Block\Adminhtml\Methods\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;


class Rates extends \Magento\Backend\Block\Widget\Grid\Container implements TabInterface
{
    /**
     * @var \Amasty\StorePickup\Model\Method $_model
     */
    protected $_model;

    public function getTabLabel()
    {
        return __('Pickup Stores');
    }

    public function getTabTitle()
    {
        return __('Pickup Stores');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data)
    {
        $this->_model = $registry->registry('current_amasty_storepick_method');
        parent::__construct($context, $data);
    }


    protected function _construct()
    {
        $this->_controller = 'adminhtmlMethods';
        $this->_headerText = __('Stores');

        if ($this->_model->getId()) {
            $this->_addButtonLabel = __('Add New Store');
            $this->addButton(
                'add_new',
                [
                    'label' => $this->getAddButtonLabel(),
                    'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
                    'class' => 'add primary'
                ],
                0,
                0,
                $this->getNameInLayout()
            );
        }

        $this->removeButton('add');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/rates/newAction', ['method_id' => $this->_model->getId()]);
    }
}
