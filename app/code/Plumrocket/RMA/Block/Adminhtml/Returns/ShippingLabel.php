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

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

class ShippingLabel extends \Plumrocket\RMA\Block\Adminhtml\Returns\Template
{
    /**
     * Check if has shipping label
     *
     * @return bool
     */
    public function hasShippingLabel()
    {
        return (bool)$this->getEntity()->getShippingLabel();
    }

    /**
     * Get shipping label url
     *
     * @return string
     */
    public function getShippingLabelUrl()
    {
        return $this->returnsHelper->getFileUrl(
            $this->getEntity(),
            $this->getEntity()->getShippingLabel(),
            true
        );
    }

    /**
     * Get checkbox element of delete field
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getCheckboxOfDelete()
    {
        return $this->createElement('shipping_label_delete', 'checkbox', [
            'name'      => 'shipping_label_delete',
            'label'     => __('Delete'),
            'value'     => '1',
            'checked'   => $this->dataHelper->getFormData('shipping_label_delete'),
            'class'     => 'admin__control-checkbox',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getEntity()->isVirtual()) {
            return '';
        }

        return parent::_toHtml();
    }
}
