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

use Plumrocket\RMA\Model\Returns\Message;

class Messages extends Template
{
    /**
     * Retrieve messages list
     *
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->getEntity()
            ->getMessagesCollection()
            ->addFieldToFilter('is_internal', false)
            ->getItems();
    }

    /**
     * Check if current customer is the sender
     *
     * @param  Message $message
     * @return bool
     */
    public function isFromYou(Message $message)
    {
        $order = $this->getOrder();
        return Message::FROM_CUSTOMER === $message->getType()
            && $order
            && $order->getId()
            && $message->getFromId() === $order->getCustomerId();
    }

    /**
     * Prepare message text
     *
     * @param  Message $text
     * @return string
     */
    public function getMessageText(Message $message)
    {
        return nl2br((string)$message->getText());
    }

    /**
     * Get editor element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getEditor()
    {
        return $this->createElement('returns_comment', 'textarea', [
            'name'      => 'comment',
            'label'     => __('Comment (optional)'),
            'rows'      => 5,
            'value'     => $this->dataHelper->getFormData('comment'),
        ]);
    }

    /**
     * Get file url
     *
     * @param string $filename
     * @return string
     */
    public function getFileUrl($filename)
    {
        return $this->returnsHelper->getFileUrl($this->getEntity(), $filename);
    }

    /**
     * Check if customer can add message
     *
     * @return bool
     */
    public function canSubmit()
    {
        return ! $this->getEntity()->isClosed();
    }
}
