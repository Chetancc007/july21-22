<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\Layer\Filter;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Exception\StateException;
use Amasty\Shopby\Model\Layer\Filter\Traits\FromToDecimal;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Api\Data\FromToFilterInterface;
use Amasty\Shopby\Model\Source\PositionLabel;

class Decimal extends \Magento\CatalogSearch\Model\Layer\Filter\Decimal implements FromToFilterInterface
{
    use FromToDecimal;

    const DECIMAL_DELTA = 0.001;
    const LABEL_RANGE = 0.01;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $settingHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var float|int|null
     */
    private $extraToValue;

    /**
     * @var array|null
     */
    private $facetedData;

    /**
     * @var \Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    /**
     * @var \Magento\Search\Api\SearchInterface
     */
    private $search;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var string
     */
    private $currencySymbol;

    /**
     * @var string
     */
    private $magentoVersion;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        \Amasty\Shopby\Helper\Group $groupHelper,
        \Magento\Search\Api\SearchInterface $search,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\Base\Model\MagentoVersion $magentoVersion,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterDecimalFactory,
            $priceCurrency,
            $data
        );
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->dataProvider = $dataProviderFactory->create(['layer' => $layer]);
        $this->shopbyRequest = $shopbyRequest;
        $this->groupHelper = $groupHelper;
        $this->search = $search;
        $this->messageManager = $messageManager;
        $this->magentoVersion = $magentoVersion->get();
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->isApplied()) {
            return $this;
        }

        $filter = $this->shopbyRequest->getFilterParam($this);
        $noValidate = 0;
        if (!empty($filter) && !is_array($filter)) {
            $filter = $this->getFromToValues($filter);
            $filterParams = explode(',', $filter);
            $validateFilter = $this->dataProvider->validateFilter($filterParams[0]);

            if (!$validateFilter) {
                $noValidate = 1;
            } else {
                $this->setFromTo($validateFilter[0], $validateFilter[1]);
            }
        }

        if ($noValidate) {
            return $this;
        }

        $request->setQueryValue($this->getRequestVar(), $filter);
        $apply = parent::apply($request);
        if (!empty($filter) && !is_array($filter)) {
            $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
            if ($filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER) {
                $facets = $this->getFacetedData();
                $arrayRange = $this->getExtremeValues($filterSetting, $facets);
                $this->setFromTo($arrayRange['from'], $arrayRange['to']);
            }
        }

        return $apply;
    }

    /**
     * @param string $filter
     * @return string
     */
    private function getFromToValues($filter)
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        list($from, $to) = explode('-', $filter);
        $displayMode = $filterSetting->getDisplayMode();
        $includeBorders = $this->isSliderOrFromTo($displayMode) ? self::DECIMAL_DELTA : 0;
        $from = $from ?: 0;
        $to = floatval($to) ? (floatval($to) + $includeBorders) : $to;

        return $from . '-' . $to;
    }

    /**
     * @return array
     */
    public function getFromToConfig()
    {
        return $this->getConfig('decimal');
    }

    /**
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->isHide()) {
            return [];
        }

        $facets = $this->getFacetedData();

        $data = [];
        foreach ($facets as $key => $aggregation) {
            if ($key === 'data') {
                continue;
            }
            $count = $aggregation['count'];
            list($from, $to) = explode('_', $key);

            $from = $from == '*' ? 0 : $from;
            $to = $to == '*' ? '' : $to;

            $label = $this->renderRangeLabel(
                empty($from) ? 0 : $from,
                $to
            );

            $value = $from . '-' . $to;

            $data[] = [
                'label' => $label,
                'value' => $value,
                'count' => $count,
                'from' => $from,
                'to' => $to
            ];
        }

        return $data;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getFacetedData(): ?array
    {
        if ($this->facetedData === null) {
            $productCollection = $this->getLayer()->getProductCollection();
            try {
                $this->facetedData = $productCollection->getFacetedData(
                    $this->getAttributeModel()->getAttributeCode(),
                    $this->getSearchResult()
                );
            } catch (StateException $e) {
                if (!$this->messageManager->hasMessages()) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Make sure that "%1" attribute can be used in layered navigation',
                            $this->getAttributeModel()->getAttributeCode()
                        )
                    );
                }
                $this->facetedData = [];
            }
            $this->prepareFacetedData();
        }

        return $this->facetedData;
    }

    /**
     * Method for fix faceted data depends on current filter value. If current filter value does not exist in
     * faceted data results, we have to modify faceted data, so than we can see applied filter with current value
     * @TODO remove after refactoring
     *
     * @return void
     */
    private function prepareFacetedData(): void
    {
        if ($this->hasCurrentValue()) {
            $from = $this->isValidFrom() ? $this->getCurrentFrom() : '*';
            $to = $this->isValidTo() ? $this->getCurrentTo() : '*';
            $key = $from . '_' . $to;
            if (!isset($this->facetedData[$key])
                && isset($this->facetedData['data']['count'])
                && $this->facetedData['data']['count']
            ) {

                $this->facetedData = [
                    $key => [
                        'value' => $key,
                        'count' => $this->facetedData['data']['count']
                    ],
                    'data' => $this->facetedData['data']
                ];
            }
        }
    }

    private function isValidFrom(): bool
    {
        if (version_compare($this->magentoVersion, '2.4.0', '>')) {
            return $this->getCurrentFrom() !== null;
        }

        return (bool)$this->getCurrentFrom();
    }

    private function isValidTo(): bool
    {
        if (version_compare($this->magentoVersion, '2.4.0', '>')) {
            return $this->getCurrentTo() !== null;
        }

        return (bool)$this->getCurrentTo();
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return \Magento\Framework\Phrase
     */
    protected function renderRangeLabel($fromPrice, $toPrice)
    {
        $groupRanges = $this->getGroupRanges($fromPrice, $toPrice);
        if ($groupRanges) {
            return $groupRanges;
        }

        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        $defaultLabel = $this->getDefaultRangeLabel($fromPrice, $toPrice, $filterSetting);
        if ($defaultLabel) {
            return $defaultLabel;
        }

        $stateLabel = $this->getRangeLabel($fromPrice, $toPrice, $filterSetting);

        return $stateLabel;
    }

    /**
     * @param $fromPrice
     * @param $toPrice
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getGroupRanges($fromPrice, $toPrice)
    {
        $result = '';
        $ranges = $this->groupHelper->getGroupAttributeMinMaxRanges($this->getAttributeModel()->getAttributeId());
        if ($ranges) {
            if (isset($ranges[$fromPrice . '-' . $toPrice])) {
                $result =  __($ranges[$fromPrice . '-' . $toPrice]);
            }
        }

        return $result;
    }

    /**
     * @param $fromPrice
     * @param $toPrice
     * @param \Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting
     * @return \Magento\Framework\Phrase|string
     */
    private function getDefaultRangeLabel($fromPrice, $toPrice, $filterSetting)
    {
        $result = '';
        if ($filterSetting->getUnitsLabelUseCurrencySymbol()) {
            $displayMode = $filterSetting->getDisplayMode();
            if ($this->isSliderOrFromTo($displayMode) && $toPrice) {
                $toPrice = $toPrice + self::LABEL_RANGE;
            } elseif (!$toPrice) {
                $toPrice = null;
            }
            $toPrice = $this->isSliderOrFromTo($displayMode) && $toPrice ? $toPrice + self::LABEL_RANGE : $toPrice;
            $result = parent::renderRangeLabel($fromPrice, $toPrice);
        }

        return $result;
    }

    /**
     * @param $fromPrice
     * @param $toPrice
     * @param $filterSetting
     * @return \Magento\Framework\Phrase
     */
    private function getRangeLabel($fromPrice, $toPrice, $filterSetting)
    {
        $formattedFromPrice = $this->formatLabelForStateAndRange($fromPrice, $filterSetting);
        if ($toPrice === '') {
            $result = __('%1 and above', $formattedFromPrice);
        } else {
            if (!$this->isSliderOrFromTo($filterSetting->getDisplayMode()) && $fromPrice != $toPrice) {
                $toPrice -= self::LABEL_RANGE;
            }
            $result =  __(
                '%1 - %2',
                $formattedFromPrice,
                $this->formatLabelForStateAndRange($toPrice, $filterSetting)
            );
        }

        return $result;
    }

    /**
     * @param $value
     * @param $filterSetting
     * @return string
     */
    private function formatLabelForStateAndRange($value, $filterSetting)
    {
        $value = round(floatval($value), 2);
        if ($filterSetting->getPositionLabel() == PositionLabel::POSITION_BEFORE) {
            $formattedLabel = sprintf("%s%.2F", $filterSetting->getUnitsLabel(), $value);
        } else {
            $formattedLabel = sprintf("%.2F%s", $value, $filterSetting->getUnitsLabel());
        }

        return $formattedLabel;
    }
}
