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

class NewAction extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->messageManager->addSuccess(
            __('To create a new return, please click on "return" link next to your order below')
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('sales/order/history');
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // This action works only for customers
        return (bool)$this->getCustomer();
    }
}
