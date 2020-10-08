<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returnrule\Edit;

class Form extends \Plumrocket\RMA\Block\Adminhtml\AbstractRmaForm
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $groupCollection;

    /**
     * Resolution Factory
     * @var \Plumrocket\RMA\Model\ResolutionFactory
     */
    protected $resolutionFactory;

    /**
     * Renderer fieldset
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * Conditions
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * Statuses
     * @var \Plumrocket\RMA\Model\Config\Source\Status
     */
    protected $statusSource;

    /**
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Framework\Registry                            $registry
     * @param \Magento\Rule\Block\Conditions                         $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset   $rendererFieldset
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection
     * @param \Plumrocket\RMA\Model\ResolutionFactory                $resolutionFactory
     * @param \Magento\Framework\Data\FormFactory                    $formFactory
     * @param \Magento\Store\Model\System\Store                      $systemStore
     * @param \Plumrocket\RMA\Model\Config\Source\Status             $statusSource
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection,
        \Plumrocket\RMA\Model\ResolutionFactory $resolutionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Plumrocket\RMA\Model\Config\Source\Status $statusSource,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->rendererFieldset = $rendererFieldset;
        $this->resolutionFactory = $resolutionFactory;
        $this->groupCollection = $groupCollection;
        $this->conditions = $conditions;
        $this->statusSource = $statusSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => [
                'id'        => 'edit_form',
                'action'    => $this->getData('action'),
                'method'    => 'post',
                'enctype'   => 'multipart/form-data'
            ]]
        );
        $form->setUseContainer(true);

        $model = $this->_coreRegistry->registry('current_model');
        $form->setHtmlIdPrefix('rma_returnrule_');

        $legend = ($model->getId())  ? __('Return Rule') : __('Add New Return Rule');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => $legend]);

        if ($this->_authorization->isAllowed('Plumrocket_RMA::returnrule')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                [
                    'name'  => 'id',
                    'value' => $model->getData('id'),
                ]
            );
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Rule Name'),
                'title' => __('Rule Name'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'value' => $model->getData('title'),
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'      => 'status',
                'label'     => __('Status'),
                'title'     => __('Status'),
                'required'  => true,
                'value'     => $model->getData('status'),
                'values'    => $this->statusSource->toOptionHash(),
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'website_id',
                'multiselect',
                [
                    'name'  => 'website_id[]',
                    'label' => __('Websites'),
                    'title' => __('Websites'),
                    'required'  => true,
                    'values'    => $this->systemStore->getWebsiteValuesForForm(false, false),
                    'disabled'  => $isElementDisabled,
                    'value'     => $model->getWebsiteId()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'website_id',
                'hidden',
                ['name' => 'website_id[]', 'value' => $this->_storeManager->getWebsite(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'customer_group_id',
            'multiselect',
            [
                'name'  => 'customer_group_id',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required'  => true,
                'values'    => $this->groupCollection->load()->toOptionArray(),
                'disabled'  => $isElementDisabled,
                'value' => $model->getCustomerGroupId()
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'name'  => 'priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'disabled' => $isElementDisabled,
                'value' => $model->getData('priority'),
            ]
        );

        $resolutions = $this->getResolutions();
        foreach ($resolutions as $resolution) {
            $index = 'res_' . $resolution->getId();
            $value = $model->getData('resolution');

            $_value = (isset($value[$resolution->getId()])) ? $value[$resolution->getId()] : '';
            $fieldset->addField(
                $index,
                'text',
                [
                    'name'  => 'resolution[' . $resolution->getId() . ']',
                    'label' => $resolution->getTitle() . ' ' . __('Period') . ' ' . __('(days)'),
                    'title' => $resolution->getTitle() . ' ' . __('Period') . ' ' . __('(days)'),
                    'disabled' => $isElementDisabled,
                    'value' => $_value,
                    'note'  => __('Enter "0" to disable this type of resolution.')
                ]
            );
        }

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/newConditionHtml/form/rma_returnrule_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Conditions (do not add conditions, if the rule is applied to all products)')]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve resolutions
     * @return Plumrocket\RMA\Model\ResolutionFactory
     */
    protected function getResolutions()
    {
        $resolutions = $this->resolutionFactory->create()
            ->getCollection()
            ->addActiveFilter();

        return $resolutions;
    }
}
