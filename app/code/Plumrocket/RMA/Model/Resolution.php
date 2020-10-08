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

namespace Plumrocket\RMA\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\RMA\Helper\Data;

class Resolution extends AbstractModel
{
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param Context               $context
     * @param Registry              $registry
     * @param Data                  $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface     $localeDate
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->localeDate = $localeDate;
        parent::__construct(
            $context,
            $registry,
            $dataHelper,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Resolution');
    }

    /**
     * Get days left
     *
     * @param  string $startDate
     * @return bool|int
     */
    public function getDaysLeft($startDate)
    {
        if (null === $this->getDays()) {
            return false;
        }

        $currentDate = new \DateTime();
        $startDate = new \DateTime($startDate);
        $diff = $currentDate->diff($startDate, true)->format('%a');
        return max(0, $this->getDays() - $diff);
    }

    /**
     * Get expire date
     *
     * @param  string $startDate
     * @param  null|string $pattern
     * @return bool|string
     */
    public function getExpireDate($startDate, $pattern = null)
    {
        if (null === $this->getDays()) {
            return false;
        }

        $time = strtotime("+{$this->getDays()} days", strtotime($startDate));
        $date = $this->localeDate->date($time);

        return $this->localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            $pattern
        );
    }
}
