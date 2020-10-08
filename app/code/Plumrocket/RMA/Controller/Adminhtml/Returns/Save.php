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

use Plumrocket\RMA\Block\Adminhtml\Returns\Messages\Uploader as MessageUploader;
use Plumrocket\RMA\Block\Adminhtml\Returns\ShippingLabel\Uploader;
use Plumrocket\RMA\Controller\Adminhtml\Returns;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\Message;
use Plumrocket\RMA\Model\Returns\Track;

class Save extends Returns
{
    protected function _beforeSave($model, $request)
    {
        $request = $this->getRequest();
        $this->_getRegistry()->register('current_model', $model);

        if ($model->isObjectNew() &&
            ! $this->returnsHelper->canReturnAdmin($model->getOrder())
        ) {
            $this->_redirect('*/*');
            return false;
        }

        // Validate data.
        $validator = $this->validatorFactory->create()
            ->setReturns($model)
            ->validateMessage(
                $request->getParam('comment'),
                $request->getParam(MessageUploader::FILE_FIELD_NAME),
                false
            );

        foreach ((array)$request->getParam('track_add') as $track) {
            $validator->validateTrack(
                isset($track['carrier_code']) ? $track['carrier_code'] : null,
                isset($track['track_number']) ? $track['track_number'] : null
            );
        }

        if (! $model->isClosed()) {
            $validator->validateItemsAdmin($request->getParam('items'));
        }

        if (! $validator->isValid()) {
            foreach ($validator->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
            $this->dataHelper->setFormData();
            return false;
        }

        $model->setValidItems($validator->getValidItems());

        // Remove shipping label.
        if ($request->getParam('shipping_label_delete')) {
            $model->setData('shipping_label', null);
        }
    }

    protected function _afterSave($model, $request)
    {
        if (! $model->isVirtual()) {
            // Add tracks.
            foreach ((array)$request->getParam('track_add') as $track) {
                $model->addTrack(
                    Track::FROM_MANAGER,
                    $track['carrier_code'],
                    $track['track_number']
                );
            }

            // Remove tracks.
            foreach ((array)$request->getParam('track_remove') as $trackId => $remove) {
                if ($remove) {
                    $track = $model->getTrackById($trackId);
                    if ($track && $track->getId()) {
                        $track->delete();
                    }
                }
            }

            // Take shipping label file.
            if ($filesTmp = $request->getParam(Uploader::FILE_FIELD_NAME)) {
                $shippingLabelFile = $this->fileHelper
                    ->setAdditionalPath($model->getId())
                    ->takeShippingLabel($filesTmp);

                if ($shippingLabelFile) {
                    $model
                        ->setData('shipping_label', $shippingLabelFile)
                        ->save();
                }
            }
        }

        // Save items.
        $validItems = $model->getValidItems();
        if (is_array($validItems)) {
            $hasItemChanges = false;
            foreach ($validItems as $data) {
                $item = $this->itemFactory->create();
                if ('' === $data[ItemHelper::ENTITY_ID]) {
                    $orderItem = $this->orderItemFactory->create()
                        ->load($data[ItemHelper::ORDER_ITEM_ID]);

                    $item->setReturns($model)
                        ->setQtyPurchased(
                            $this->itemHelper->getQtyToReturn($orderItem, $model->getId())
                        );

                    $data[ItemHelper::QTY_AUTHORIZED] = $data[ItemHelper::QTY_REQUESTED];
                    $hasItemChanges = true;
                } else {
                    $item->load($data[ItemHelper::ENTITY_ID]);
                    if (! $item->getId()
                        || $item->getOrderItemId() != $data[ItemHelper::ORDER_ITEM_ID]
                        || $item->getParentId() != $model->getId()
                    ) {
                        continue;
                    }
                }

                // Prepare data before save.
                unset($data[ItemHelper::ENTITY_ID]);

                $cols = [
                    ItemHelper::QTY_AUTHORIZED,
                    ItemHelper::QTY_RECEIVED,
                    ItemHelper::QTY_APPROVED,
                ];

                foreach ($cols as $col) {
                    if (isset($data[$col]) && '' === $data[$col]) {
                        $data[$col] = null;
                    }
                }

                $item->addData($data)->save();
                $hasItemChanges = true;
            }

            if ($hasItemChanges) {
                // If items was created then reset items in model.
                $model->setItems(null);
            }
        }

        // Calculate and save new status.
        $statusChanged = false;
        $status = $this->returnsHelper->getStatus($model);
        if ($status && $status != $model->getStatus() && ! $model->isClosed()) {
            // If it is one of final statuses then close return.
            if (in_array(
                $status,
                array_keys($this->returnsStatusSource->getFinalStatuses())
            )) {
                $model->setIsClosed(true);
            }

            $model->setStatus($status)->save();
            $statusChanged = true;
        }

        // Add message.
        $message = $model->addMessage(
            Message::FROM_MANAGER,
            $request->getParam('comment'),
            $request->getParam(MessageUploader::FILE_FIELD_NAME),
            false,
            $request->getParam('comment_is_internal')
        );

        // Send email.
        $email = $this->emailFactory->create()
            ->setReturns($model)
            ->setMessage($message);

        // New object after save.
        if ($model->isObjectNew()) {
            // Assign address.
            $address = $model->getAddress();
            if (! $address || ! $address->getId()) {
                $unassignedAddress = $this->addressFactory->create()
                    ->getUnassigned($model->getOrder()->getId());

                if ($unassignedAddress) {
                    $unassignedAddress->setParentId($model->getId())
                        ->save();
                }
            }

            // Send emails
            if ($model->getManagerId() != $this->_auth->getUser()->getId()) {
                $email->notifyManagerAboutCreate(
                    $this->_auth->getUser()
                );
            }

            if ($request->getParam('comment_send_email')
                && ! $request->getParam('comment_is_internal')) {
                $email->notifyCustomerAboutCreate();
            }
        } else {
            // Add system message if status is changed.
            $systemMessage = null;
            if ($statusChanged) {
                $systemMessage = $model->addMessage(
                    Message::FROM_MANAGER,
                    __('Status of return request has been updated to: %1', $model->getStatusLabel()),
                    null,
                    true,
                    // If manager has added previous message as internal then status message make internal too
                    $request->getParam('comment_is_internal')
                );
            }

            // If return is updated, send emails only if message exists
            if ($message || $systemMessage) {
                // If message is empty then use system message. Othervise use message as the primary
                if (! $message) {
                    $email->setMessage($systemMessage);
                }

                if ($model->getManagerId() != $this->_auth->getUser()->getId()) {
                    $email->notifyManagerAboutUpdate(
                        $this->_auth->getUser()
                    );
                }

                if ($request->getParam('comment_send_email')
                    && ! $request->getParam('comment_is_internal')
                ) {
                    $email->notifyCustomerAboutUpdate();
                }
            }
        }
    }

    public function _saveAction()
    {
        $request = $this->getRequest();
        $model = $this->_getModel();

        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
        }

        try {
            $date = $this->dateTime->gmtDate();

            $model->addData($request->getParams())
                ->setUpdatedAt($date);

            if (!$model->getId()) {
                $model->setCreatedAt($date)
                    ->setReadMarkAt($date);
            }

            if (false === $this->_beforeSave($model, $request)) {
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }

            $model->save();

            $this->_afterSave($model, $request);

            // Check which controller use
            if ($model->isClosed()) {
                $controller = 'returnsarchive';
            } else {
                $controller = 'returns';
            }

            $this->messageManager->addSuccess(__($this->_objectTitle.' has been saved.'));
            $this->_setFormData(false);

            if ($request->getParam('back')) {
                $this->_redirect("*/{$controller}/edit", [$this->_idKey => $model->getId()]);
            } else {
                $this->_redirect("*/{$controller}");
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_setFormData();
        }

        // $this->_forward('new');
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
