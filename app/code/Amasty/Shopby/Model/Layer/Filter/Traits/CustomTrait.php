<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\Layer\Filter\Traits;

trait CustomTrait
{
    use FilterTrait;

    /**
     * @return array
     */
    private function getFacetedData()
    {
        $collection = $this->getProductCollection();

        return $collection->getFacetedData($this->getAttributeCode(), $this->getSearchResult());
    }

    /**
     * @return bool
     */
    private function isMultiSelectAllowed()
    {
        return false;
    }
}
