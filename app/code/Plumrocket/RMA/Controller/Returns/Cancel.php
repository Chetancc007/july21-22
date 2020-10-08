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
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\Message;

class Cancel extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $model = $this->getModel();
        if ($model->isClosed()) {
            return $resultRedirect->setUrl(
                $this->_redirect->getRefererUrl()
            );
        }

        try {
            $model
                ->setIsClosed(true)
                ->setStatus(ReturnsStatus::STATUS_CLOSED)
                ->save();

            // Add system message.
            $systemMessage = $model->addMessage(
                Message::FROM_CUSTOMER,
                __('Return request has been canceled by customer'),
                null,
                true
            );

            // Send email.
            $email = $this->emailFactory->create()
                ->setReturns($model)
                ->setMessage($systemMessage)
                ->notifyManagerAboutUpdate();

            $this->messageManager->addSuccess(__('Return has been canceled'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unknown Error'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client cannot have separate order on this page
        return false;
    }
}
