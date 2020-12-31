<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\Layer\Filter\Traits;

use Magento\Framework\Api\Search\SearchResultInterface;

trait FilterTrait
{
    private $attributeCode;

    /**
     * @var $currentValue
     */
    protected $currentValue;

    /**
     * @param $currentValue
     */
    protected function setCurrentValue($currentValue)
    {
        $this->currentValue = $currentValue;
    }

    /**
     * @return \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection
     */
    protected function getProductCollection()
    {
        return $this->getLayer()->getProductCollection();
    }

    /**
     * @return bool
     */
    public function isVisibleWhenSelected()
    {
        // Could be overwritten in plugins.
        $keepSingleChoice = $this->settingHelper->isSetConfig('amshopby/general/keep_single_choice_visible');

        $hideByDefaultMagentoBehavior = !$keepSingleChoice
            && (!$this->isMultiSelectAllowed() || $this->getPriceType());

        return !$hideByDefaultMagentoBehavior;
    }

    /**
     * @return bool is filter applied
     */
    protected function hasCurrentValue()
    {
        return $this->currentValue != null;
    }

    /**
     * @return bool
     */
    public function isApplied()
    {
        foreach ($this->getLayer()->getState()->getFilters() as $filter) {
            if ($filter->getFilter()->getRequestVar() == $this->getRequestVar()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Must not remove filter with one option if it is applied.
     *
     * @param array $itemsData
     * @return array
     */
    protected function getReducedItemsData(array $itemsData)
    {
        $isFilterActive = false;
        foreach ($this->getLayer()->getState()->getFilters() as $filter) {
            if ($filter->getFilter()->getRequestVar() == $this->getRequestVar()) {
                $isFilterActive = true;
                break;
            }
        }

        return $isFilterActive ? $itemsData : [];
    }

    /**
     * @return bool
     */
    private function isHide()
    {
        return (bool)$this->shopbyRequest->getFilterParam($this) && !$this->isVisibleWhenSelected();
    }

    private function getSearchResult(): ?SearchResultInterface
    {
        $alteredQueryResponse = null;
        if ($this->hasCurrentValue()) {
            $searchCriteria = $this->getProductCollection()->getSearchCriteria([$this->getAttributeCode()]);
            $alteredQueryResponse = $this->search->search($searchCriteria);
        }

        return $alteredQueryResponse;
    }

    private function getAttributeCode(): ?string
    {
        return $this->getAttributeModel()->getAttributeCode();
    }
}
