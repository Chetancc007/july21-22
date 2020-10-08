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

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Data;

class Address extends AbstractReturns
{
    /**
     * Address book form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $model = $this->getModel();
        $request = $this->getRequest();
        $address = $this->addressFactory->create();

        if ($parentId = $request->getParam('parent_id')) {
            /*// From return edit page
            $address->load($parentId, 'parent_id');

            if (! $address->getId() && $model && $model->getId() === $parentId) {
                $address = $model->getOrder()->getShippingAddress();
            }*/

            // Cannot edit address on frontend
            return $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT)
                ->setPath(Data::SECTION_ID . '/returns/view', ['id' => $parentId]);
        } elseif ($orderId = $request->getParam('order_id')) {
            // From return create page
            if ($unassignedAddress = $address->getUnassigned($orderId)) {
                $address = $unassignedAddress;
            } else {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order && $order->getId()) {
                    $address = $order->getShippingAddress();
                }
            }
        }

        $this->registry->register('current_model', $model);
        $this->registry->register('returns_address', $address);

        // Load form data in local storage and clear form data from session.
        $this->dataHelper->getFormData();
        $this->dataHelper->addFormData('returns_address', false);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->preparePage($resultPage, [
            'title' => __('Edit Address')
        ]);

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // Client cannot have return on this page
        return false;
    }
}
