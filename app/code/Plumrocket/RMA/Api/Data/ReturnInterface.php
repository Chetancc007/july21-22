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
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Api\Data;

/**
 * @since 2.2.0
 */
interface ReturnInterface
{
    const INCREMENT_ID = 'increment_id';
    const IDENTIFIER = 'entity_id';
    const ORDER_ID = 'order_id';
    const MANAGER_ID = 'manager_id';
    const IS_CLOSED = 'is_closed';
    const STATUS = 'status';
    const SHIPPING_LABEL = 'shipping_label';
    const NOTE = 'note';
    const CODE = 'code';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getIncrementId(): int;

    /**
     * @return int
     */
    public function getIdentifier(): int;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @return int
     */
    public function getManagerId(): int;

    /**
     * @return bool
     */
    public function getIsClosed(): bool;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return string
     */
    public function getShippingLabel(): string;

    /**
     * @return string
     */
    public function getNote(): string;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getCeatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @return \Plumrocket\RMA\Model\Returns\Item[]
     */
    public function getItems();

    /**
     * @param int $incrementId
     * @return ReturnInterface
     */
    public function setIncrementId(int $incrementId): ReturnInterface;

    /**
     * @param int $id
     * @return ReturnInterface
     */
    public function setIdentifier(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return ReturnInterface
     */
    public function setOrderId(int $id): ReturnInterface;

    /**
     * @param int $id
     * @return ReturnInterface
     */
    public function setManagerId(int $id): ReturnInterface;

    /**
     * @param bool $flag
     * @return ReturnInterface
     */
    public function setIsClosed(bool $flag): ReturnInterface;

    /**
     * @param string $status
     * @return ReturnInterface
     */
    public function setStatus(string $status): ReturnInterface;

    /**
     * @param string $label
     * @return ReturnInterface
     */
    public function setShippingLabel(string $label): ReturnInterface;

    /**
     * @param string $note
     * @return ReturnInterface
     */
    public function setNote(string $note): ReturnInterface;

    /**
     * @param string $code
     * @return ReturnInterface
     */
    public function setCode(string $code): ReturnInterface;

    /**
     * @param string $date
     * @return ReturnInterface
     */
    public function setCeatedAt(string $date): ReturnInterface;

    /**
     * @param string $date
     * @return ReturnInterface
     */
    public function setUpdatedAt(string $date): ReturnInterface;

    /**
     * @param \Plumrocket\RMA\Model\Returns\Item[] $items
     * @return ReturnInterface
     */
    public function setItems($items): ReturnInterface;
}
