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

class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Plumrocket\RMA\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Plumrocket\RMA\Helper\Data                                  $dataHelper
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Plumrocket\RMA\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        if ($this->getEntityTypeId()) {
            $stores = $this->getStoreId();

            if (! is_array($stores)) {
                $stores = [$stores];
            }

            if (count($stores)) {
                $storeTable = $this->getStoreTable();

                if ($this->getId()) {
                    $this->getResource()
                        ->getConnection()
                        ->delete(
                            $storeTable,
                            'entity_type_id = ' . $this->getEntityTypeId() . ' AND entity_id = ' . $this->getId()
                        );
                }

                foreach ($stores as $store) {
                    $this->getResource()
                        ->getConnection()
                        ->insert($storeTable, [
                                'entity_type_id' => $this->getEntityTypeId(),
                                'entity_id' => $this->getId(),
                                'store_id' => $store
                            ]
                        );
                }
            }

            $labels = $this->getStoreLabels();
            if (is_array($labels) && count($labels)) {
                $labelTable = $this->getLabelTable();

                if ($this->getId()) {
                    $this->getResource()
                        ->getConnection()
                        ->delete(
                            $labelTable,
                            'entity_type_id = ' . $this->getEntityTypeId() . ' AND entity_id = ' . $this->getId()
                        );
                }
                foreach ($labels as $storeId => $label) {
                    $this->getResource()
                        ->getConnection()
                        ->insert($labelTable, [
                                'entity_type_id' => $this->getEntityTypeId(),
                                'entity_id' => $this->getId(),
                                'store_id' => $storeId,
                                'value' => $label,
                            ]
                        );
                }
            }
        }

        return parent::afterSave();
    }

    /**
     * Add stores and labels to entity.
     * @return  $this
     */
    protected function _afterLoad()
    {
        if ($this->getEntityTypeId()) {
            $storeTable = $this->getStoreTable();
            $storeSql = $this->getResource()
                ->getConnection()
                ->select()
                ->from($storeTable)
                ->where('entity_type_id = ?', $this->getEntityTypeId())
                ->where('entity_id = ?', $this->getId());

            $stores = $this->getResource()->getConnection()->fetchAll($storeSql);
            $_stores = [];
            foreach ($stores as $store) {
                $_stores[] = $store['store_id'];
            }
            $this->setStoreId($_stores);

            $labelTable = $this->getLabelTable();
            $labelSql = $this->getResource()
                ->getConnection()
                ->select()
                ->from($labelTable)
                ->where('entity_type_id = ?', $this->getEntityTypeId())
                ->where('entity_id = ?', $this->getId());

            if ($this->getStoreId() && !is_array($this->getStoreId())) {
                $labelSql->where('store_id = ?', $this->getStoreId());
            }

            $labels = $this->getResource()->getConnection()->fetchAll($labelSql);
            if ($this->getStoreId() && is_array($this->getStoreId())) {
                $_labels = [];
                foreach ($labels as $label) {
                    $_labels[$label['store_id']] = $label['value'];
                }
                $this->setLabels($_labels);
            } else {
                if (count($labels)) {
                    $this->setLabel($labels[0]);
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Retrieve label
     * @return string
     */
    public function getLabel()
    {
        if ($this->getData('label')) {
            return $this->getData('label');
        } elseif (! $this->dataHelper->isBackend()
            && $labels = $this->getLabels()
        ) {
            $storeId = $this->storeManager->getStore()->getId();
            if ($storeId && ! empty($labels[$storeId])) {
                return $labels[$storeId];
            }
        }

        return $this->getTitle();
    }

    /**
     * Retrieve name
     * @return string
     */
    public function getName()
    {
        if (null !== $this->getData('name')) {
            return $this->getData('name');
        }

        return $this->getLabel();
    }

    /**
     * Store table
     * @return string
     */
    protected function getStoreTable()
    {
        return $this->getResource()->getTable('plumrocket_rma_store');
    }

    /**
     * Return store label text
     * @return string
     */
    protected function getLabelTable()
    {
        return $this->getResource()->getTable('plumrocket_rma_text');
    }

    /**
     * Retriev entity type id
     * @return int
     */
    public function getEntityTypeId()
    {
        return $this->getResource()->getEntityTypeId();
    }
}
