<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Model\Elasticsearch\Adapter\IndexedDataMapper;

class Wished extends IndexedDataMapper
{
    const FIELD_NAME = 'wished';

    /**
     * @inheritdoc
     */
    public function getIndexerCode()
    {
        return 'amasty_sorting_wished';
    }
}
