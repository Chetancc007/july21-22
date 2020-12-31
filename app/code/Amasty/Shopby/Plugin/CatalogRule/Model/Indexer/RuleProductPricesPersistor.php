<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Indexer\IndexerRegistry;

class RuleProductPricesPersistor
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        IndexerRegistry $indexerRegistry,
        \Amasty\Base\Model\MagentoVersion $magentoVersion
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->magentoVersion = $magentoVersion;
    }

    public function isEnabled(): bool
    {
        return version_compare($this->magentoVersion->get(), '2.3', '<');
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute($subject, $result)
    {
        if ($result && $this->isEnabled()) {
            $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();
        }

        return $result;
    }
}
