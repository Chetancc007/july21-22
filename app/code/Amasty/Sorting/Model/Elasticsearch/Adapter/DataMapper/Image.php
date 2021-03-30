<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapperInterface;

class Image implements DataMapperInterface
{
    /**
     * @var Data
     */
    private $data;

    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        $value = isset($context['document']['small_image'])
            ? (int) ($context['document']['small_image'] !== 'no_selection')
            : 0;

        return ['non_images_last' => $value];
    }

    /**
     * @inheritdoc
     */
    public function isAllowed($storeId)
    {
        return $this->data->getNonImageLast($storeId);
    }
}
