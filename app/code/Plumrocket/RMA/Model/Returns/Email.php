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

namespace Plumrocket\RMA\Model\Returns;

use Plumrocket\RMA\Model\Returns;
use Plumrocket\RMA\Model\Returns\Message;

class Email
{
    /**
     * @var \Plumrocket\RMA\Helper\Config
     */
    protected $configHelper;

    /**
     * @var Returns
     */
    protected $returns = null;

    /**
     * @var Message
     */
    protected $message = null;

    /**
     * @param \Plumrocket\RMA\Helper\Config $configHelper
     */
    public function __construct(
        \Plumrocket\RMA\Helper\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Set returns entity
     *
     * @param Returns $returns
     * @return $this
     */
    public function setReturns(Returns $returns)
    {
        $this->returns = $returns;
        return $this;
    }

    /**
     * Get returns entity
     *
     * @return Returns
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * Set message entity
     *
     * @param Message|null $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message entity
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Send email to manager
     * - if customer created rma
     * - if different manager created rma and assign it to manager
     *
     * Uses "manager_new" template
     *
     * @param  \Magento\User\Model\User|null $fromAdmin
     * @return $this
     */
    public function notifyManagerAboutCreate($fromAdmin = null)
    {
        $template = $this->configHelper->getManagerNewTemplate($this->getStoreId());
        $additional = $this->configHelper->getManagerNewEmails($this->getStoreId());

        $data = [];
        $comment = $this->getMessage() ? $this->getMessage()->getText() : '';
        if ($fromAdmin && $fromAdmin->getName()) {
            $data['sender_name'] = $fromAdmin->getName();
            $data['admin_comment'] = $comment;
        } else {
            $data['comment'] = $comment;
        }

        $emails = array_merge(
            [$this->returns->getManagerEmail()],
            $additional
        );

        $emails = array_unique($emails);
        foreach ($emails as $email) {
            $this->returns->sendEmail($template, $email, $data);
        }

        return $this;
    }

    /**
     * Send email to manager
     * - if customer send message
     * - if customer canceled rma
     * - if different manager updated rma
     *
     * Uses "manager_update" template
     *
     * @param  \Magento\User\Model\User|null $fromAdmin
     * @return $this
     */
    public function notifyManagerAboutUpdate($fromAdmin = null)
    {
        $template = $this->configHelper->getManagerUpdateTemplate($this->getStoreId());
        $additional = $this->configHelper->getManagerUpdateEmails($this->getStoreId());

        $data = [];
        $comment = $this->getMessage() ? $this->getMessage()->getText() : '';
        if ($fromAdmin && $fromAdmin->getName()) {
            $data['sender_name'] = $fromAdmin->getName();
            $data['admin_comment'] = $comment;
        } else {
            $data['comment'] = $comment;
        }

        $emails = array_merge(
            [$this->returns->getManagerEmail()],
            $additional
        );

        $emails = array_unique($emails);
        foreach ($emails as $email) {
            $this->returns->sendEmail($template, $email, $data);
        }

        return $this;
    }

    /**
     * Send email to customer
     * - if manager created rma with checkbox "Notify Customer"
     * - if customer created rma
     *
     * Uses "customer_new" template
     *
     * @return $this
     */
    public function notifyCustomerAboutCreate()
    {
        $template = $this->configHelper->getCustomerNewTemplate($this->getStoreId());
        $additional = $this->configHelper->getCustomerNewEmails($this->getStoreId());

        $comment = $this->getMessage() ? $this->getMessage()->getText() : '';

        $emails = array_merge(
            [$this->returns->getOrder()->getCustomerEmail()],
            $additional
        );

        $emails = array_unique($emails);
        foreach ($emails as $email) {
            $this->returns->sendEmail($template, $email, [
                'comment' => $comment
            ]);
        }

        return $this;
    }

    /**
     * Send email to customer
     * - if manager updated rma with checkbox "Notify Customer"
     * - if system message was added
     *
     * Uses "customer_update" template
     *
     * @return $this
     */
    public function notifyCustomerAboutUpdate()
    {
        $template = $this->configHelper->getCustomerUpdateTemplate($this->getStoreId());
        $additional = $this->configHelper->getCustomerUpdateEmails($this->getStoreId());

        $comment = $this->getMessage() ? $this->getMessage()->getText() : '';

        $emails = array_merge(
            [$this->returns->getOrder()->getCustomerEmail()],
            $additional
        );

        $emails = array_unique($emails);
        foreach ($emails as $email) {
            $this->returns->sendEmail($template, $email, [
                'comment' => $comment
            ]);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->returns->getOrder()->getStoreId();
    }
}
