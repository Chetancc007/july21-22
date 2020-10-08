<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


declare(strict_types=1);

namespace Amasty\ElasticSearch\Model\Indexer;

class ExternalIndexerProvider
{
    /**
     * @var array
     */
    private $sources;

    public function __construct(
        array $sources = []
    ) {
        $this->sources = $sources;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getDocuments(int $storeId): array
    {
        $documents = [];
        foreach ($this->sources as $indexType => $source) {
            $documents[$indexType] = $source->get($storeId);
        }

        return $documents;
    }

    /**
     * @return array
     */
    public function getIndexTypes()
    {
        return array_keys($this->sources);
    }
}
