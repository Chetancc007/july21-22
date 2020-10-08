<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Model\Rule\Condition;

use Amasty\Xlanding\Model\Rule\Condition;

class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Amasty\Xlanding\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);
        $this->storeManager = $storeManager;
        $this->setType(Condition\Combine::class);
    }

    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Xlanding\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => Condition\Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                [
                    'label' => __('Custom Fields'),
                    'value' => [
                        [
                            'label' => __('Is New (by a period)'),
                            'value' => Condition\IsNewByPeriod::class,
                        ],
                        [
                            'label' => __('Is New (by \'is_new\' attribute)'),
                            'value' => Condition\IsNew::class,
                        ],
                        [
                            'label' => __('Created (in days)'),
                            'value' => Condition\Created::class,
                        ],
                        [
                            'label' => __('In Stock'),
                            'value' => Condition\InStock::class,
                        ],
                        [
                            'label' => __('Is on Sale'),
                            'value' => Condition\Price\Sale::class,
                        ],
                        [
                            'label' => __('Qty'),
                            'value' => Condition\Qty::class,
                        ],
                        [
                            'label' => __('Min Price'),
                            'value' => Condition\Price\Min::class,
                        ],
                        [
                            'label' => __('Max Price'),
                            'value' => Condition\Price\Max::class,
                        ],
                        [
                            'label' => __('Final Price'),
                            'value' => Condition\Price\FinalPrice::class,
                        ],
                        [
                            'label' => __('Rating'),
                            'value' => Condition\Rating::class,
                        ],
                        [
                            'label' => __('Type of Product'),
                            'value' => Condition\ProductType::class,
                        ],
                    ]
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }

    public function collectConditionSql()
    {
        $wheres = [];
        foreach ($this->getConditions() as $condition) {
            $where = $condition->collectConditionSql();
            if ($where) {
                $wheres[] = $where;
            }

        }

        if (empty($wheres)) {
            return '';
        }

        $delimiter = $this->getAggregator() == "all" ? ' AND ' : ' OR ';
        return '(' . implode($delimiter, $wheres) . ')';
    }

    /**
     * @param object $condition
     * @return $this
     */
    public function addCondition($condition)
    {
        $condition->setData('store_manager', $this->storeManager);
        return parent::addCondition($condition);
    }
}
