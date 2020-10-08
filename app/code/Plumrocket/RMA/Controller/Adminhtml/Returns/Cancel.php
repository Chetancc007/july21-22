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

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\Message;

class Cancel extends \Plumrocket\RMA\Controller\Adminhtml\Returns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->_getModel();
        if ($model->isClosed()) {
            $this->_redirect('*/*');
            return;
        }

        try {
            $model
                ->setIsClosed(true)
                ->setStatus(ReturnsStatus::STATUS_CLOSED)
                ->save();

            // Add system message.
            $systemMessage = $model->addMessage(
                Message::FROM_MANAGER,
                __('Return request has been canceled by store manager'),
                null,
                true
            );

            // Send email.
            $email = $this->emailFactory->create()
                ->setReturns($model)
                ->setMessage($systemMessage)
                ->notifyCustomerAboutUpdate();

            if ($model->getManagerId() != $this->_auth->getUser()->getId()) {
                $email->notifyManagerAboutUpdate(
                    $this->_auth->getUser()
                );
            }

            $this->_redirect('*/*');
        } catch (\Exception $e) {
            $this->_redirect('*/*/edit', ['id' => $model->getId()]);
        }
    }
}
