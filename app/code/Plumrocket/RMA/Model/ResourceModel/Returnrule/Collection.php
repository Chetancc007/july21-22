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


namespace Plumrocket\RMA\Model\ResourceModel\Returnrule;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plumrocket\RMA\Model\Config\Source\Status;

/**
 * Return rule collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\Returnrule', 'Plumrocket\RMA\Model\ResourceModel\Returnrule');
    }

    /**
     * Add filter for only enabled
     *
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this->addFieldToFilter('status', Status::STATUS_ENABLED);
    }

    /**
     * Add website filter
     *
     * @param int|array $websiteId
     * @return $this
     */
    public function addWebsiteFilter($websiteId)
    {
        if ($websiteId) {
            if (! is_array($websiteId)) {
                $websiteId = [$websiteId];
            }

            $this->addFieldToFilter('website_id', ['finset' => $websiteId]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this->_items as $item) {
            $resolution = json_decode($item->getResolution());
            $_res = [];
            foreach ($resolution as $rid => $value) {
                $_res[$rid] = $value;
            }

            $item->setResolution($_res);

            $websites = $item->getWebsiteId();
            $item->setWebsiteId(explode(',', $websites));
        }

        return $this;
    }
}
