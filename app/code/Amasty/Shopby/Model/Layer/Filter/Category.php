<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyFulltextCollection;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Amasty\Shopby\Model\Source\RenderCategoriesLevel;
use Amasty\Shopby\Helper\Category as CategoryHelper;
use Amasty\Shopby\Model\Layer\Filter\Traits\FilterTrait;
use Amasty\Shopby\Model\Source\CategoryTreeDisplayMode;
use Magento\Framework\App\ProductMetadata;

class Category extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    use FilterTrait;

    const MIN_CATEGORY_DEPTH = 1;

    const DENY_PERMISSION = '-2';

    const FILTER_FIELD = 'category';

    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $settingHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    private $dataProvider;

    /**
     * @var \Amasty\ShopbyBase\Model\Category\Manager
     */
    private $categoryManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Item\CategoryExtendedDataBuilder
     */
    private $categoryExtendedDataBuilder;

    /**
     * @var CategoryItemsFactory
     */
    private $categoryItemsFactory;

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\Search\Api\SearchInterface
     */
    private $search;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        \Amasty\ShopbyBase\Model\Category\Manager $categoryManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Amasty\Shopby\Model\Layer\Filter\Item\CategoryExtendedDataBuilder $categoryExtendedDataBuilder,
        \Amasty\Shopby\Model\Layer\Filter\CategoryItemsFactory $categoryItemsFactory,
        \Amasty\Shopby\Helper\Data $helper,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        CategoryHelper $categoryHelper,
        \Magento\Search\Api\SearchInterface $search,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->helper = $helper;
        $this->escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->settingHelper = $settingHelper;
        $this->categoryManager = $categoryManager;
        $this->categoryRepository = $categoryRepository;
        $this->categoryExtendedDataBuilder = $categoryExtendedDataBuilder;
        $this->categoryItemsFactory = $categoryItemsFactory;
        $this->shopbyRequest = $shopbyRequest;
        $this->categoryHelper = $categoryHelper;
        $this->categoryFactory = $categoryFactory;
        $this->search = $search;
        $this->messageManager = $messageManager;
        $this->productMetadata = $productMetadata;
        $this->logger = $logger;
    }

    /**
     * Apply category filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->isApplied()) {
            return $this;
        }

        $categoryId = $this->shopbyRequest->getFilterParam($this) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }

        $categoryIds = explode(',', $categoryId);
        $categoryIds = array_unique($categoryIds);
        /** @var ShopbyFulltextCollection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $category = $this->dataProvider->getCategory();
        if ($this->isMultiselect() && $request->getParam('id') != $categoryId) {
            $this->setCurrentValue($categoryIds);
            $child = $category->getCollection()
                ->addFieldToFilter($category->getIdFieldName(), ['in' => $categoryIds])
                ->addAttributeToSelect('name');
            $categoriesInState = [];
            foreach ($categoryIds as $categoryId) {
                if ($currentCategory = $child->getItemById($categoryId)) {
                    $categoriesInState[$currentCategory->getId()] = $currentCategory->getName();
                }
            }
            foreach ($categoriesInState as $key => $category) {
                $state = $this->_createItem($category, $key);
                $this->getLayer()->getState()->addFilter($state);
            }
        } else {
            $this->setCurrentValue($categoryIds);
            $this->dataProvider->setCategoryId($categoryId);
            if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
                $this->getLayer()->getState()->addFilter(
                    $this->_createItem(
                        $this->dataProvider->getCategory()->getName(),
                        $categoryId
                    )
                );
            }
        }
        $productCollection->addFieldToFilter(CategoryHelper::ATTRIBUTE_CODE, $categoryIds);

        return $this;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Category');
    }

    /**
     * Get fiter items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        if (!$this->categoryHelper->isCategoryFilterExtended()) {
            return count($this->getItems()->getItems(null));
        }

        return $this->getItems()->getCount();
    }

    /**
     * @return $this|\Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function _initItems()
    {
        /** @var CategoryItems $itemsCollection */
        $itemsCollection = $this->categoryItemsFactory->create();
        $data = $this->getExtendedCategoryData();
        if ($data && ($data['count'] > 1 || !$this->isMultiselect())) {
            $itemsCollection->setStartPath($data['startPath']);
            $itemsCollection->setCount($data['count']);
            foreach ($data['items'] as $path => $items) {
                foreach ($items as $itemData) {
                    $itemsCollection->addItem(
                        $path,
                        $this->_createItem($itemData['label'], $itemData['value'], $itemData['count'])
                    );
                }
            }
        }

        $this->_items = $itemsCollection;

        return $this;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $optionsFacetedData = $this->getFacetedData();
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();

        if ($categories instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {
            $categories->addAttributeToSelect('thumbnail');
        }

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if ($category->getIsActive()
                    && $category->getIsAnchor()
                    && isset($optionsFacetedData[$category->getId()])
                ) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $optionsFacetedData[$category->getId()]['count']
                    );
                }
            }
        }

        $itemsData = $this->itemDataBuilder->build();
        if (count($itemsData) == 1
            && !$this->isOptionReducesResults(
                $itemsData[0]['count'],
                $this->getLayer()->getProductCollection()->getSize()
            )
        ) {
            $itemsData = $this->getReducedItemsData($itemsData);
        }

        if ($this->getSetting()->getSortOptionsBy() == \Amasty\Shopby\Model\Source\SortOptionsBy::NAME) {
            usort($itemsData, [$this, 'sortOption']);
        }

        return $itemsData;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public function sortOption($a, $b)
    {
        return strcmp($a['label'], $b['label']);
    }

    /**
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getExtendedCategoryData()
    {
        try {
            $optionsFacetedData = $this->getFacetedData();
        } catch (StateException $e) {
            return [];
        }
        $startCategory = $this->getStartCategory();
        $startPath = $startCategory->getPath();

        $collection = $this->getExtendedCategoryCollection($startCategory);
        $currentCategoryParents = $this->getLayer()->getCurrentCategory()->getParentIds();
        foreach ($collection as $category) {
            $isAllowed = $this->isAllowedOnEnterprise($category);
            if (!isset($optionsFacetedData[$category->getId()])
                || !$isAllowed
                || (!$this->isRenderAllTree()
                    && !in_array($category->getParentId(), $currentCategoryParents)
                    && $this->getCategoriesTreeDept() != self::MIN_CATEGORY_DEPTH
                    && strpos($category->getPath(), $startPath) !== 0
                )
            ) {
                continue;
            }

            $this->categoryExtendedDataBuilder->addItemData(
                $category->getParentPath(),
                $this->escaper->escapeHtml($category->getName()),
                $category->getId(),
                $optionsFacetedData[$category->getId()]['count']
            );
        }
        $itemsData = [];
        $itemsData['count'] = $this->categoryExtendedDataBuilder->getItemsCount();
        $itemsData['startPath'] = $startPath;
        $itemsData['items'] = $this->categoryExtendedDataBuilder->build();

        if ($this->getSetting()->getSortOptionsBy() == \Amasty\Shopby\Model\Source\SortOptionsBy::NAME) {
            foreach ($itemsData['items'] as $path => &$items) {
                usort($items, [$this, 'sortOption']);
            }
        }

        return $itemsData;
    }

    /**
     * @param $category
     * @return bool
     */
    private function isAllowedOnEnterprise($category)
    {
        $isAllowed = true;
        if ($this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME) {
            $permissions = $category->getPermissions();
            if (isset($permissions['grant_catalog_category_view'])) {
                $isAllowed = $permissions['grant_catalog_category_view'] !== self::DENY_PERMISSION;
            }
        }

        return $isAllowed;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Category $startCategory
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function getExtendedCategoryCollection(\Magento\Catalog\Model\Category $startCategory)
    {
        $minLevel = $startCategory->getLevel();
        $maxLevel = $minLevel + $this->getCategoriesTreeDept();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $startCategory->getCollection();
        $isFlat = $collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Flat\Collection;
        $mainTablePrefix = $isFlat ? 'main_table.' : '';
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter($mainTablePrefix . 'is_active', 1)
            ->addFieldToFilter($mainTablePrefix . 'path', ['like' => $startCategory->getPath() . '%'])
            ->addFieldToFilter($mainTablePrefix . 'level', ['gt' => $minLevel])
            ->setOrder(
                $mainTablePrefix . 'position',
                \Magento\Framework\DB\Select::SQL_ASC
            );
        if (!$this->isRenderAllTree()) {
            $collection->addFieldToFilter($mainTablePrefix . 'level', ['lteq' => $maxLevel]);
        }

        $mainTablePrefix = $isFlat ? 'main_table.' : 'e.';
        $collection->getSelect()->joinLeft(
            ['parent' => $collection->getMainTable()],
            $mainTablePrefix . 'parent_id = parent.entity_id',
            ['parent_path' => 'parent.path']
        );

        return $collection;
    }

    /**
     * @return array
     */
    protected function getFacetedData()
    {
        $optionsFacetedData = [];

        $productCollection = $this->getProductCollection();
        if ($productCollection instanceof ShopbyFulltextCollection) {
            $result = $this->getSearchResult();
            try {
                $optionsFacetedData = $productCollection->getFacetedData(
                    self::FILTER_FIELD,
                    $result
                );
            } catch (StateException $e) {
                $this->catchBucketException();
            }
        }

        return $optionsFacetedData;
    }

    /**
     * @return $this
     */
    private function catchBucketException()
    {
        if (is_array($this->currentValue)) {
            $categoryId = current($this->currentValue);
            try {
                $category = $this->categoryRepository->get(
                    $categoryId,
                    $this->categoryManager->getCurrentStoreId()
                );
            } catch (NoSuchEntityException $e) {
                $category = $this->getRootCategory();
            }
        } else {
            $category = $this->getRootCategory();
        }

        $this->messageManager->addErrorMessage(
            __(
                'Make sure that "%1"(id:%2) category for current store is anchored',
                $category->getName(),
                $category->getId()
            )
        );

        return $this;
    }

    /**
     * Retrieve start category for bucket prepare
     * @return \Magento\Catalog\Model\Category
     */
    private function getStartCategory()
    {
        if ($this->getCategoriesTreeDept() == self::MIN_CATEGORY_DEPTH
            && !$this->getLayer()->getCurrentCategory()->getChildrenCount()
            && !$this->isRenderAllTree()
        ) {
            return $this->getLayer()->getCurrentCategory()->getParentCategory();
        }

        return $this->categoryHelper->getStartCategory();
    }

    /**
     * Retrieve root category for current store
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    private function getRootCategory()
    {
        if (!$this->getData('root_category')) {
            $category = $this->categoryRepository->get(
                $this->categoryManager->getRootCategoryId(),
                $this->categoryManager->getCurrentStoreId()
            );
            $this->setData('root_category', $category);
        }

        return $this->getData('root_category');
    }

    private function getSearchResult(): ?SearchResultInterface
    {
        $searchResult = null;
        $isCurrentLevel = $this->getRenderCategoriesLevel() == RenderCategoriesLevel::CURRENT_CATEGORY_LEVEL;
        $isRootLevel = $this->getRenderCategoriesLevel() == RenderCategoriesLevel::ROOT_CATEGORY;
        $excludeCurrentLevel = $isCurrentLevel || $isRootLevel || $this->isRenderAllTree();

        if ($this->hasCurrentValue() || ($excludeCurrentLevel && $this->isMultiselect())) {
            //TODO: need rewrite
            $categoryId = (int)$this->getCategoryIdByLevel($isCurrentLevel);
            $searchResult = $this->search->search($this->buildSearchCriteria($categoryId));
        }

        return $searchResult;
    }

    /**
     * @param bool $isCurrentLevel
     *
     * @return int
     */
    protected function getCategoryIdByLevel($isCurrentLevel)
    {
        $isCurrentLevelMultiselect = $isCurrentLevel && $this->isMultiselect();
        $isCurrentCategory = !$this->isRenderAllTree()
            && ($isCurrentLevelMultiselect || $this->getCategoriesTreeDept() == self::MIN_CATEGORY_DEPTH);

        return $isCurrentCategory
            ? $this->getLayer()->getCurrentCategory()->getId()
            : $this->getRootCategory()->getId();
    }

    protected function buildSearchCriteria(int $categoryId): SearchCriteria
    {
        $filter[CategoryHelper::ATTRIBUTE_CODE] = $categoryId;

        return $this->getProductCollection()->getMemSearchCriteria($filter);
    }

    /**
     * @param $optionsFacetedData
     * @return mixed
     */
    protected function addChildrenCategoriesWithNull($optionsFacetedData)
    {
        $categories = $this->categoryFactory->create()->addAttributeToSelect('*');

        foreach ($categories as $key => $category) {
            if (!isset($optionsFacetedData[$key])) {
                $optionsFacetedData[$key] = ['value' => $key, 'count' => 0];
            }
        }

        return $optionsFacetedData;
    }

    /**
     *
     * @return int
     */
    protected function getRenderCategoriesLevel()
    {
        return $this->getSetting()->getRenderCategoriesLevel();
    }

    /**
     *
     * @return int
     */
    protected function getCategoriesTreeDept()
    {
        return $this->getSetting()->getCategoryTreeDepth();
    }

    /**
     * @return bool
     */
    protected function isRenderAllTree()
    {
        return !!$this->getSetting()->getRenderAllCategoriesTree();
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        return $this->getSetting()->isMultiselect();
    }

    /**
     * @return bool
     */
    public function useLabelsOnly()
    {
        return $this->getImageDisplayMode() == CategoryTreeDisplayMode::SHOW_LABELS_ONLY;
    }

    /**
     * @return bool
     */
    public function useLabelsAndImages()
    {
        return $this->getImageDisplayMode() == CategoryTreeDisplayMode::SHOW_LABELS_IMAGES;
    }

    /**
     * @return bool
     */
    public function useImagesOnly()
    {
        return $this->getImageDisplayMode() == CategoryTreeDisplayMode::SHOW_IMAGES_ONLY;
    }

    /**
     * @return int
     */
    public function getImageDisplayMode()
    {
        return $this->getSetting()->getCategoryTreeDisplayMode();
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSetting()
    {
        if ($this->filterSetting === null) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }

        return $this->filterSetting;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->helper->getCategoryPosition();
    }

    /**
     * @return array
     */
    public function getAmpItems()
    {
        $data = parent::_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            $items[] = parent::_createItem($itemData['label'], $itemData['value'], $itemData['count']);
        }
        $this->_items = $items;

        return $items;
    }
}
