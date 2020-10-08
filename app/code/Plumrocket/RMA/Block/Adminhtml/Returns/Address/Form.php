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

namespace Plumrocket\RMA\Block\Adminhtml\Returns\Address;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address
{
    use TemplateTrait;

    /**
     * Address form template
     *
     * @var string
     */
    protected $_template = 'returns/address/form.phtml';

    /**
     * Order address getter
     *
     * @return \Magento\Sales\Model\Order\Address
     */
    protected function _getAddress()
    {
        return $this->registry->registry('returns_address');
    }

    /**
     * Get submit url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        $params = [];
        $request = $this->getRequest();

        if ($parentId = $request->getParam('parent_id')) {
            $params['parent_id'] = $parentId;
        } elseif ($orderId = $request->getParam('order_id')) {
            $params['order_id'] = $orderId;
        }

        return $this->getUrl(Data::SECTION_ID . '/returns/addressSave', $params);
    }

    /**
     * Define form attributes (id, method, action)
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->_form->setId('edit_form');
        $this->_form->setMethod('post');
        $this->_form->setAction($this->getSubmitUrl());
        $this->_form->setUseContainer(true);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        parent::_addAdditionalFormElementData($element);

        // Hide VAT number field.
        if ($element->getId() == 'vat_id') {
            $element->setNoDisplay(true);
        }

        return $this;
    }

    /**
     * Form header text getter
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Return Address Information');
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        $data = $this->dataHelper->getFormData('returns_address');
        if (! $data || ! is_array($data)) {
            $data = $this->_getAddress()->getData();
        }

        return $data;
    }
}
