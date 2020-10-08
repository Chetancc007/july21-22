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

namespace Plumrocket\RMA\Block\Returns;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Buttons extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * @var ReturnsStatus
     */
    protected $status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ReturnsStatus                           $status
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ReturnsStatus $status,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->status = $status;
    }

    /**
     * Check if can cancel return
     *
     * @return bool
     */
    public function canCancel()
    {
        return ! $this->getEntity()->isClosed();
    }

    /**
     * Get cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->returnsHelper->getCancelUrl($this->getEntity());
    }

    /**
     * Check if has packing slip
     *
     * @return bool
     */
    public function hasPackingSlip()
    {
        $entity = $this->getEntity();
        return ! $this->getEntity()->isVirtual()
            && ! in_array($entity->getStatus(), array_keys($this->status->getFinalStatuses()))
            && $this->returnsHelper->hasAuthorized($entity);
    }

    /**
     * Get print page url
     *
     * @return string
     */
    public function getPackingSlipUrl()
    {
        return $this->returnsHelper->getPrintUrl($this->getEntity());
    }

    /**
     * Check if has shipping label
     *
     * @return bool
     */
    public function hasShippingLabel()
    {
        $entity = $this->getEntity();
        return (bool)$entity->getShippingLabel()
            && ! $entity->isVirtual()
            && ! in_array($entity->getStatus(), array_keys($this->status->getFinalStatuses()))
            && $this->returnsHelper->hasAuthorized($this->getEntity());
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
            $this->getEntity()->getShippingLabel()
        );
    }
}
