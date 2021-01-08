<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Block\Adminhtml\Methods\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\StorePickup\Model\Rate;

class Main extends Generic implements TabInterface
{
    /**
     * @var \Amasty\StorePickup\Model\Config\Source\Statuses
     */
    protected $_statuses;

    /**
     * @var \Amasty\StorePickup\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\StorePickup\Model\Config\Source\Statuses $statuses,
        \Amasty\StorePickup\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_statuses = $statuses;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('General');
    }

    public function getTabTitle()
    {
        return __('General');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_storepick_method');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amstorepick_');
        $fieldsetGeneral = $form->addFieldset('general_fieldset', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldsetGeneral->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldsetGeneral->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        $fieldsetGeneral->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'note' => $this->escapeHtml(
                        __(
                            'HTML tags <b>, <u>, <i>, <s> are supported.
                    For example: This is a <b>Bold text</b>. To learn more, refer to this page: '
                        )
                    )
                    . '<a href="' . $this->escapeUrl('https://www.w3schools.com/html/html_css.asp') . '" title="' . __(
                        'HTML Styles - CSS'
                    )
                    . '" target="_blank">' . $this->escapeUrl('https://www.w3schools.com/html/html_css.asp') . '</a>'
            ]
        );

        $fieldsetGeneral->addField(
            'comment_img',
            'image',
            [
                'name' => 'comment_img',
                'label' => __('Image'),
                'title' => __('Image'),
                'note' => __('Please use {IMG} in Comment field to insert an image')
            ]
        );

        $fieldsetGeneral->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => $this->_statuses->toOptionArray(),
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
