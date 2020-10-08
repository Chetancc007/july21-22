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

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Progress extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * @var ReturnsStatus
     */
    protected $status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ReturnsStatus                           $status
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ReturnsStatus $status,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->status = $status;
    }

    /**
     * Get steps (progress points)
     *
     * @return array
     */
    public function getSteps()
    {
        $steps = [];
        $currentStatus = $this->getEntity()->getStatus();
        $items = $this->getEntity()->getItems();

        for ($n = 4; $n >= 1; $n--) {
            $class = '';
            switch ($n) {
                case 4:
                    $status = ReturnsStatus::STATUS_PROCESSED_CLOSED;

                    if (in_array(
                        $currentStatus,
                        array_keys($this->status->getFinalStatuses())
                    )) {
                        $status = $currentStatus;
                    } elseif (ReturnsStatus::STATUS_APPROVED_PART == $currentStatus) {
                        $status = $currentStatus;
                    }

                    if ($this->isStepCompleted($n)) {
                        $class = 'completed';
                    } elseif ($this->isStepActive($n)) {
                        $class = 'active';
                    }
                    break;

                case 3:
                    $status = ReturnsStatus::STATUS_RECEIVED;

                    $hasReceived = false;
                    $allReceived = true;
                    foreach ($items as $item) {
                        if (null !== $item->getQtyReceived()) {
                            $hasReceived = true;
                        }

                        if (null === $item->getQtyReceived()
                            || $item->getQtyReceived() < $item->getQtyAuthorized()
                        ) {
                            $allReceived = false;
                        }

                        if ($hasReceived && ! $allReceived) {
                            $status = ReturnsStatus::STATUS_RECEIVED_PART;
                            break;
                        }
                    }

                    if ($this->isStepMissed($n)) {
                        $status = ReturnsStatus::STATUS_RECEIVED;
                        $class = 'missed';
                    } elseif ($this->isStepCompleted($n)) {
                        $class = 'completed';
                    } elseif ($this->isStepActive($n)) {
                        $class = 'active';
                    }
                    break;

                case 2:
                    $status = ReturnsStatus::STATUS_AUTHORIZED;

                    if (ReturnsStatus::STATUS_REJECTED_PART == $currentStatus) {
                        $status = $currentStatus;
                    } else {
                        $hasAuthorized = false;
                        $allAuthorized = true;
                        foreach ($items as $item) {
                            if (null !== $item->getQtyAuthorized()) {
                                $hasAuthorized = true;
                            }

                            if (null === $item->getQtyAuthorized()
                                || $item->getQtyAuthorized() < $item->getQtyRequested()
                            ) {
                                $allAuthorized = false;
                            }

                            if ($hasAuthorized && ! $allAuthorized) {
                                $status = ReturnsStatus::STATUS_AUTHORIZED_PART;
                                break;
                            }
                        }
                    }

                    if ($this->isStepMissed($n)) {
                        $status = ReturnsStatus::STATUS_AUTHORIZED;
                        $class = 'missed';
                    } elseif ($this->isStepCompleted($n)) {
                        $class = 'completed';
                    } elseif ($this->isStepActive($n)) {
                        $class = 'active';
                    }
                    break;

                case 1:
                    $status = ReturnsStatus::STATUS_NEW;
                    if ($this->isStepCompleted($n)) {
                        $class = 'completed';
                    } elseif ($this->isStepActive($n)) {
                        $class = 'active';
                    }
                    break;
            }

            $steps[$n] = [
                'n'         => $n,
                'name'      => $this->status->getByKey($status),
                'class'     => $class,
            ];
        }

        return array_reverse($steps, true);
    }

    /**
     * Check if step is missed
     *
     * @param  int $step
     * @return boolean
     */
    public function isStepMissed($step)
    {
        $isMissed = true;
        $items = $this->getEntity()->getItems();
        foreach ($items as $item) {
            switch ($step) {
                case 1:
                    $isMissed = false;
                    break(2);

                case 2:
                    if ($this->isStepCompleted(4) && ! $this->isStepCompleted(2)) {
                        break(2);
                    }

                    if (null === $item->getQtyAuthorized() || $item->getQtyAuthorized() > 0) {
                        $isMissed = false;
                        break(2);
                    }
                    break;

                case 3:
                    if ($this->isStepCompleted(4) && ! $this->isStepCompleted(3)) {
                        break(2);
                    }

                    if (null === $item->getQtyReceived() || $item->getQtyReceived() > 0) {
                        $isMissed = false;
                        break(2);
                    }
                    break;

                case 4:
                    $isMissed = false;
                    break(2);
            }
        }

        return $isMissed;
    }

    /**
     * Check if step is active
     *
     * @param  int $step
     * @return boolean
     */
    public function isStepActive($step)
    {
        $isActive = false;
        $currentStatus = $this->getEntity()->getStatus();
        $items = $this->getEntity()->getItems();
        foreach ($items as $item) {
            switch ($step) {
                case 1:
                    $isActive = ReturnsStatus::STATUS_NEW == $currentStatus;
                    break(2);

                case 2:
                    $isActive = ReturnsStatus::STATUS_NEW != $currentStatus;
                    break(2);

                case 3:
                    if ($item->getQtyAuthorized() > 0 || null !== $item->getQtyReceived()) {
                        $isActive = true;
                        break(2);
                    }
                    break;

                case 4:
                    if ($item->getQtyReceived() > 0 || null !== $item->getQtyApproved()) {
                        $isActive = true;
                        break(2);
                    }
                    break;
            }
        }

        return $isActive;
    }

    /**
     * Check if step is completed
     *
     * @param  int $step
     * @return boolean
     */
    public function isStepCompleted($step)
    {
        $isCompleted = false;
        $currentStatus = $this->getEntity()->getStatus();

        switch ($step) {
            case 1:
                $isCompleted = ReturnsStatus::STATUS_NEW != $currentStatus;
                break;

            case 2:
                // If all items have values in authorized field and at least one is authorized.
                $isCompleted = $this->returnsHelper->hasAuthorized($this->getEntity());
                break;

            case 3:
                $items = $this->getEntity()->getItems();
                // Is completed if item authorized but not received
                $isCompleted = true;
                foreach ($items as $item) {
                    if (null === $item->getQtyAuthorized()
                        || ($item->getQtyAuthorized() > 0 && null === $item->getQtyReceived())
                    ) {
                        $isCompleted = false;
                        break;
                    }
                }
                break;

            case 4:
                if (in_array(
                    $currentStatus,
                    array_keys($this->status->getFinalStatuses())
                )) {
                    $isCompleted = true;
                }
                break;
        }

        return $isCompleted;
    }

    /**
     * Get current step number
     *
     * @return int
     */
    public function getCurrentStep()
    {
        $current = 1;
        foreach ($this->getSteps() as $step) {
            if ('active' == $step['class'] || 'completed' == $step['class']) {
                $current = $step['n'];
            }
        }

        return $current;
    }

    /**
     * Get status step
     *
     * @return string
     */
    public function getStatusStep()
    {
        switch ($this->getEntity()->getStatus()) {
            case ReturnsStatus::STATUS_REJECTED: // no break
            case ReturnsStatus::STATUS_PROCESSED_CLOSED: // no break
            case ReturnsStatus::STATUS_CLOSED: // no break
            case ReturnsStatus::STATUS_APPROVED_PART:
                return 4;

            case ReturnsStatus::STATUS_RECEIVED: // no break
            case ReturnsStatus::STATUS_RECEIVED_PART:
                return 3;

            case ReturnsStatus::STATUS_AUTHORIZED: // no break
            case ReturnsStatus::STATUS_AUTHORIZED_PART: // no break
            case ReturnsStatus::STATUS_REJECTED_PART:
                return 2;

            case ReturnsStatus::STATUS_NEW:
                return 1;
        }
    }
}
