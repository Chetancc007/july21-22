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

namespace Plumrocket\RMA\Block\Adminhtml\Response\Edit;

class Form extends \Plumrocket\RMA\Block\Adminhtml\AbstractRmaForm
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Plumrocket\RMA\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\RMA\Model\Config\Source\Status
     */
    protected $statusSource;

    /**
     * Wsiwyg config
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;


    /**
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Framework\Data\FormFactory        $formFactory
     * @param \Magento\Store\Model\System\Store          $systemStore
     * @param \Plumrocket\RMA\Helper\Data                $dataHelper
     * @param \Plumrocket\RMA\Model\Config\Source\Status $statusSource
     * @param \Magento\Cms\Model\Wysiwyg\Config          $wysiwygConfig
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Plumrocket\RMA\Helper\Data $dataHelper,
        \Plumrocket\RMA\Model\Config\Source\Status $statusSource,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->dataHelper = $dataHelper;
        $this->statusSource = $statusSource;
        $this->wysiwygConfig = $wysiwygConfig;
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
        $form->setHtmlIdPrefix('rma_response_');

        $legend = ($model->getId())  ? __('Quick Response Template') : __('Add New Quick Response Template');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => $legend]);

        if ($this->_authorization->isAllowed('Plumrocket_RMA::response')) {
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
                'label' => __('Title'),
                'title' => __('Title'),
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
                'values'    => $this->statusSource->toOptionHash(),
                'value'     => $model->getData('status')
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name'  => 'store_id[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required'  => true,
                    'values'    => $this->systemStore->getStoreValuesForForm(false, true),
                    'disabled'  => $isElementDisabled,
                    'value'     => (null !== $model->getStoreId() ? $model->getStoreId() : 0)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField('message', 'editor', [
            'name'      => 'message',
            'label'     => __('Message'),
            'title'     => __('Message'),
            'config'    => $this->wysiwygConfig->getConfig(['height' => '150px']),
            'value'     => $model->getData('message')
        ]);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
