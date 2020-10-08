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
use Plumrocket\RMA\Block\Returns\Messages\Uploader;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Model\Returns\Message;

class Save extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $request = $this->getRequest();
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            if (! $request->isPost()) {
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            $model = $this->getModel();

            if ($model->isClosed()) {
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            // Validate data.
            $validator = $this->validatorFactory->create()
                ->validateMessage(
                    $request->getParam('comment'),
                    $request->getParam(Uploader::FILE_FIELD_NAME)
                );

            if (! $validator->isValid()) {
                foreach ($validator->getMessages() as $message) {
                    $this->messageManager->addError($message);
                }
                $this->dataHelper->setFormData();
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            // Add message.
            $message = $model->addMessage(
                Message::FROM_CUSTOMER,
                $request->getParam('comment'),
                $request->getParam(Uploader::FILE_FIELD_NAME)
            );

            // Send email.
            if ($message && $message->getId()) {
                $model->setUpdatedAt($this->dateTime->gmtDate())->save();

                $this->emailFactory->create()
                    ->setReturns($model)
                    ->setMessage($message)
                    ->notifyManagerAboutUpdate();
            }

            // Clear form data.
            $this->dataHelper->setFormData(false);

            $this->messageManager->addSuccess(__('Message has been sent'));
        } catch (\Exception $e) {
            $this->messageManager->addError('Unknown Error');
            $this->dataHelper->setFormData();
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client canot have separate order on this page
        return false;
    }
}
