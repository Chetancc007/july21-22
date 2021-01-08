<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Block\Adminhtml\Rates\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_objectManager;

    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\StorePickup\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('amstorepick_rate_form');
        $this->setTitle(__('Store Information'));
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('amasty_storepick_rate');

        /**
         * @var \Magento\Framework\Data\Form $form
         */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('amstorepick/rates/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $fieldsetDestination = $form->addFieldset('destination', ['legend' => __('Store Displaying Conditions')]);

        $fieldsetDestination->addType('notice', \Amasty\StorePickup\Block\Adminhtml\Form\Element\Notice::class);
        $fieldsetDestination->addField(
            'notice',
            'notice',
            [
                'notice_text' => __(
                    'Make sure that the display conditions will not overlap conditions in previously created stores.'
                )
            ]
        );

        if ($model->getId()) {
            $fieldsetDestination->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldsetDestination->addField(
            'method_id',
            'hidden',
            [
                'name' => 'method_id'
            ]
        );

        $fieldsetDestination->addField(
            'country',
            'select',
            [
                'name' => 'country',
                'label' => __('Country'),
                'options' => $this->_helper->getCountries(true),

            ]
        );

        $fieldsetDestination->addField(
            'state',
            'select',
            [
                'name' => 'state',
                'label' => __('State'),
                'options' => $this->_helper->getStates(true),

            ]
        );

        $fieldsetDestination->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
            ]
        );

        $fieldsetDestination->addField(
            'zip_from',
            'text',
            [
                'label' => __('Zip From'),
                'name' => 'zip_from'
            ]
        );

        $fieldsetDestination->addField(
            'zip_to',
            'text',
            [
                'label' => __('Zip To'),
                'name' => 'zip_to'
            ]
        );

        $fieldsetConditions = $form->addFieldset('conditions', ['legend' => __('Store Information')]);

        $fieldsetConditions->addField(
            'time_delivery',
            'text',
            [
                'label' => __('Store Name'),
                'name' => 'time_delivery',
                'note' => __('Here you can set Store name ' .
                    'that will be used for the {store} variable in the Store Pickup method name')
            ]
        );

        $fieldsetRate = $form->addFieldset('rate', ['legend' => __('Store Pickup Amount')]);

        $fieldsetRate->addField(
            'cost_base',
            'text',
            [
                'label' => __('Base Rate for the Order'),
                'name' => 'cost_base',
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
