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

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;

class Create extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->getModel();
        if (! $model->hasOrderId()) {
            $orderId = $this->getRequest()->getParam('order_id');
            $model->setOrderId($orderId);
        }
        $this->registry->register('current_model', $model);

        // Load form data in local storage and clear form data from session.
        $this->dataHelper->getFormData();
        $this->dataHelper->setFormData(false);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $title = __('New Return');
        if ($order = $model->getOrder()) {
            $title = __('New Return for Order #%1', $order->getRealOrderId());
        }

        $this->preparePage($resultPage, [
            'title' => $title
        ]);

        return $resultPage;
    }

    /**
     * - need order id
     * - can create return for this order
     * - order belongs to customer
     * - order belongs to guest
     *
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // Client cannot have return on this page
        return false;
    }
}
