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


namespace Plumrocket\RMA\Model\ResourceModel\Returns;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Address extends \Magento\Sales\Model\ResourceModel\Order\Address
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'prrma_returns_address_resource';

    /**
     * Construct with new validator.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context                          $context
     * @param Snapshot                                                                   $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Sales\Model\ResourceModel\Attribute                               $attribute
     * @param \Magento\SalesSequence\Model\Manager                                       $sequenceManager
     * @param \Plumrocket\RMA\Model\Returns\Address\Validator                            $validator
     * @param \Magento\Sales\Model\ResourceModel\GridPool                                $gridPool
     * @param string                                                                     $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Plumrocket\RMA\Model\Returns\Address\Validator $validator,
        \Magento\Sales\Model\ResourceModel\GridPool $gridPool,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $validator,
            $gridPool,
            $connectionName
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumrocket_rma_returns_address', 'entity_id');
    }
}
