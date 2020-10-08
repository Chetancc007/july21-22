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

namespace Plumrocket\RMA\Model\Config\Source;

use Magento\Cms\Model\BlockFactory;
use Plumrocket\RMA\Helper\Data;
use Magento\Cms\Api\Data\BlockInterface;

class StaticBlock extends AbstractSource
{
    /**
     * Block factory
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @param Data         $dataHelper
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        Data $dataHelper,
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
        parent::__construct($dataHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        $blocks = $this->blockFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(BlockInterface::IS_ACTIVE, true);

        $options = [];
        foreach ($blocks as $block) {
            $options[$block->getIdentifier()] = $block->getTitle();
        }

        return $options;
    }
}
