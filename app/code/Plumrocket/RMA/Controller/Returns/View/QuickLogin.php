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

namespace Plumrocket\RMA\Controller\Returns\View;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;

class QuickLogin extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $request = $this->getRequest();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $session = $this->getSession();
        $model = $this->getModel();

        if (! $model || ! $model->getId()) {
            if ($session->isLoggedIn()) {
                // Redirect to returns list
                $resultRedirect->setUrl(
                    $this->returnsHelper->getHistoryUrl()
                );
            } else {
                // Redirect to guest form
                $resultRedirect->setPath('sales/guest/form');
            }

            return $resultRedirect;
        }

        // If customer is logged in and customer is creator of return, redirect to view page
        $order = $model->getOrder();
        if ($session->isLoggedIn()) {
            if ($order->getCustomerId()
                && $order->getCustomerId() === $session->getCustomer()->getId()
            ) {
                // Redirect to view page
                return $resultRedirect->setUrl(
                    $this->returnsHelper->getViewUrl($model)
                );
            } else {
                // Logout and continue to execute
                $session->logout();
            }
        }

        // Check code and show return as for guest
        $code = $this->returnsHelper->getCode($model, ReturnsHelper::CODE_SALT_GUEST);
        if ($model->getCode()
            && $code
            && $code === $request->getParam('code')
        ) {
            // Take order number, lastname, email and enter guest
            $request->setPostValue('oar_type', 'email');
            $request->setPostValue('oar_order_id', $order->getRealOrderId());
            if (! $lastname = $order->getCustomerLastname()) {
                $lastname = $order->getBillingAddress()->getLastname();
            }
            $request->setPostValue('oar_billing_lastname', $lastname);
            $request->setPostValue('oar_email', $order->getCustomerEmail());
            $request->setPostValue('oar_zip', '');
            if (true === $this->guestHelper->loadValidOrder($request)) {
                // Redirect to view page
                return $resultRedirect->setUrl(
                    $this->returnsHelper->getViewUrl($model)
                );
            }
        }

        // Redirect to guest form
        return $resultRedirect->setPath('sales/guest/form');
    }

    /**
     * - need return id
     * - return belongs to current customer
     * - return belongs to entered guest
     *
     * {@inheritdoc}
     */
    public function checkAccess()
    {
        // This action has own access logic.
        return null;
    }
}
