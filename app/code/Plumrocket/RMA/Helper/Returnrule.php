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
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\RMA\Model\Resolution;
use Plumrocket\RMA\Model\ResourceModel\Returnrule\CollectionFactory;
use Plumrocket\RMA\Model\ReturnruleFactory;
use Plumrocket\RMA\Model\Returnrule\SpaceFactory;
use Magento\Catalog\Model\ProductFactory;

class Returnrule extends Main
{
    /**
     * Option code for additional options
     */
    const OPTION_CODE = 'additional_options';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ReturnruleFactory
     */
    protected $returnruleFactory;

    /**
     * @var SpaceFactory
     */
    protected $spaceFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Resolution
     */
    protected $resolution;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $configurationHelper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ReturnruleFactory $returnruleFactory
     * @param SpaceFactory $spaceFactory
     * @param CollectionFactory $collectionFactory
     * @param Resolution $resolution
     * @param Session $session
     * @param \Magento\Catalog\Helper\Product\Configuration $configurationHelper
     * @param ProductFactory $productFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        StoreManagerInterface $storeManager,
        ReturnruleFactory $returnruleFactory,
        SpaceFactory $spaceFactory,
        CollectionFactory $collectionFactory,
        Resolution $resolution,
        Session $session,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper,
        ProductFactory $productFactory,
        SerializerInterface $serializer
    ) {
        $this->storeManager = $storeManager;
        $this->returnruleFactory = $returnruleFactory;
        $this->spaceFactory = $spaceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resolution = $resolution;
        $this->session = $session;
        $this->configurationHelper = $configurationHelper;
        $this->productFactory = $productFactory;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = Data::SECTION_ID;
        $this->serializer = $serializer;
    }

    /**
     * Retrieve resolutions list
     *
     * @return Resolution[]
     */
    public function getResolutions()
    {
        $resolutions = $this->resolution
            ->getCollection()
            ->addActiveFilter()
            ->setOrder('position', 'ASC');

        return $resolutions;
    }

    /**
     * Find rule for product
     *
     * @param  Product $product
     * @return Returnrule|null
     */
    public function getByProduct($product)
    {
        if ($product) {
            $groupId = 0;
            if ($this->session->isLoggedIn()) {
                $groupId = $this->session->getCustomerGroupId();
            }

            $returnrules = $this->collectionFactory
                ->create()
                ->addActiveFilter()
                // ->addFieldToFilter('conditions_serialized', ['neq' => ''])
                //->addWebsiteFilter($this->storeManager->getWebsite()->getId())
                ->addFieldToFilter('customer_group_id', ['finset' => $groupId])
                ->setOrder('priority', 'ASC');

            $space = $this->spaceFactory->create()->getSpace($product);
            foreach ($returnrules as $returnrule) {
                $_returnrule = $this->returnruleFactory
                    ->create()
                    ->load($returnrule->getId());

                if ($_returnrule->validate($space)) {
                    return $_returnrule;
                }
            }
        }

        return null;
    }

    /**
     * Retrieve resolutions options by return
     * rule
     * @param  Returnrule $returnrule
     * @return array
     */
    public function getResolutionsByRule($returnrule)
    {
        $resolutions = [];

        $data = $returnrule->getResolution();
        foreach ($this->getResolutions() as $resolution) {
                $_resolution = clone $resolution;
                $days = isset($data[$resolution->getId()]) ?
                    max(0, (int) $data[$resolution->getId()]) :
                    0;
                $_resolution->setDays($days);
                $resolutions[$_resolution->getId()] = $_resolution;
        }

        return $resolutions;
    }

    /**
     * Retrieve resolutions options
     *
     * @param  Product $product
     * @return array
     */
    public function getResolutionsByProduct($product)
    {
        $resolutions = [];
        if (! $returnrule = $this->getByProduct($product)) {
            return $resolutions;
        }

        foreach ($this->getResolutionsByRule($returnrule) as $resolution) {
            if ($resolution->getDays() <= 0) {
                continue;
            }

            $resolutions[] = $resolution;
        }

        return $resolutions;
    }

    /**
     * Retrieve resolutions options for select
     *
     * @param  Product $product
     * @param  array   $args Additional arguments
     * @return array
     */
    public function getSelectOptions($product, array $args = [])
    {
        $options = [];
        if (! $resolutions = $this->getResolutionsByProduct($product)) {
            return $options;
        }

        if (empty($args['date_format'])) {
            $args['date_format'] = 'dd MMM, yyyy';
        }

        foreach ($resolutions as $resolution) {
            $label = $resolution->getLabel() ?: $resolution->getTitle();
            $expired = false;
            if (! empty($args['date']) && ! $resolution->getDaysLeft($args['date'])) {
                if (! empty($args['exclude_expired'])) {
                    continue;
                }

                $label .= ' (' . __(
                    'Expired %1',
                    $resolution->getExpireDate($args['date'], $args['date_format'])
                ) . ')';
                $expired = true;
            }
            $options[$resolution->getId()] = [
                'id' => $resolution->getId(),
                'priority' => $resolution->getPriority(),
                'label' => $label,
                'expired' => $expired
            ];
        }

        if (! empty($args['sort'])) {
            uasort($options, function ($a, $b) {
                if ($a['expired'] && ! $b['expired']) {
                    return 1;
                }

                return $a['priority'] < $b['priority']? 1 : 0;
            });
        }

        if (empty($args['full_data'])) {
            foreach ($options as &$value) {
                $value = $value['label'];
            }
        }

        return $options;
    }

    /**
     * Retrieve resolutions product options
     *
     * @param  Product $product
     * @return array
     */
    public function getProductOptions($product)
    {
        $options = [];
        if (! $resolutions = $this->getResolutionsByProduct($product)) {
            return $options;
        }

        foreach ($resolutions as $resolution) {
            $options[] = [
                'label' => (string) __('%1 period', $resolution->getLabel() ?: $resolution->getTitle()),
                'value' => (string) __('%1 days', $resolution->getDays()),
                'custom_view' => true,
            ];
        }

        return $options;
    }

    /**
     * Check if need to show position
     *
     * @param  string $position
     * @return bool
     */
    public function showPosition($position)
    {
        $positions = explode(',', $this->getConfig(
            $this->_configSectionId . '/general/return_placement'
        ));

        if (in_array($position, $positions)) {
            return true;
        }
    }

    /**
     * Set set additional option to items
     *
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return void
     */
    public function setAdditionalOption($items)
    {
        $jsonSerialize = false;
        foreach ($items as $item) {
            if ($options = $this->getOptions($item)) {
                if (! $item->getPrEdOption()) {
                    $item->setPrEdOption(1);
                    if ($jsonSerialize) {
                        $item->addOption([
                            'code' => self::OPTION_CODE,
                            'value' => json_encode($options)
                        ]);
                    } else {
                        $item->addOption([
                            'code' => self::OPTION_CODE,
                            'value' => $this->serializer->serialize($options)
                        ]);
                        try {
                            $this->configurationHelper->getCustomOptions($item);
                        } catch (\Exception $e) {
                            $jsonSerialize = true;
                            $option = $item->getOptionByCode(self::OPTION_CODE);
                            $option->setValue(json_encode($options));
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve product options
     *
     * @param  object $item
     * @param  bool $forOrder Currently unused param
     * @return array|null
     */
    public function getOptions($item, $forOrder = null)
    {
        if ($item instanceof \Magento\Catalog\Model\Product) {
            return $this->getProductOptions($item);
        }

        if (! $product = $item->getProduct()) {
            $product = $this->productFactory->create()->load($item->getProductId());
        }

        return $this->getProductOptions($product);
    }
}
