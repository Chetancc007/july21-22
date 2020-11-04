<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.19
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model\Rule\Condition;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class QueryBuilder
{
    static $idx = 0;

    private $resource;

    private $connection;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource   = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param  Select     $select
     * @param  Attribute  $attribute
     *
     * @return string
     */
    public function joinAttribute($select, $attribute)
    {
        self::$idx++;
        $salt = '_' . self::$idx;
        $code = $attribute->getAttributeCode();

        // valid attributes for filtering in fast mode
        $attributes = ['entity_id', 'name', 'sku', 'price', 'category_ids', 'attribute_set', 'visibility',
            'status', 'type_id', 'created_at', 'updated_at'];

        if (!in_array($code, $attributes)) {
            return false;
        }

        if ($code == 'category_ids') {
            $table      = $this->resource->getTableName('catalog_category_product');
            $tableAlias = "tbl_{$code}{$salt}";
            $field      = "{$tableAlias}.category_id";
            $select->joinLeft(
                [$tableAlias => $table],
                "e.entity_id = {$tableAlias}.product_id",
                [$code = $field]
            );

            return $field;
        } elseif ($attribute->isStatic()) {
            $field = "e.{$code}";
            $select->columns([$code => $field]);

            return $field;
        } else {
            $table = $attribute->getBackendTable();

            $tableAlias = "tbl_{$code}{$salt}";

            $field = "{$tableAlias}.value";

            if (!$this->isJoined($select, $field)) {
                $select->joinLeft(
                    [$tableAlias => $table],
                    "e.entity_id = {$tableAlias}.entity_id AND {$tableAlias}.attribute_id={$attribute->getId()}",
                    [$code => $field]
                );
            }

            return $field;
        }
    }

    /**
     * @return int
     */
    public static function getIdx()
    {
        return self::$idx;
    }

    /**
     * @param int $idx
     */
    public static function setIdx($idx)
    {
        self::$idx = $idx;
    }

    /**
     * @return ResourceConnection
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param ResourceConnection $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $field
     * @param string  $operator
     * @param string  $value
     *
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function buildCondition($field, $operator, $value)
    {
        if (in_array($operator, ['()', '!()', '{}', '!{}'])) {
            if (!is_array($value)) {
                $value = explode(',', $value);
            }
        }

        switch ($operator) {
            case '==':
                $condition = $this->conditionEQ($field, $value);
                break;

            case '!=':
                $condition = $this->conditionNEQ($field, $value);
                break;

            case '()':
                $condition = $this->conditionIsOneOf($field, $value);
                break;

            case '!()':
                $condition = $this->conditionNotIsOneOf($field, $value);
                break;

            case '<=>':
                $condition = $this->conditionIsUndefined($field);
                break;

            case '>':
                $condition = $this->conditionGt($field, $value);
                break;

            case '>=':
                $condition = $this->conditionGtEq($field, $value);
                break;

            case '<':
                $condition = $this->conditionLt($field, $value);
                break;

            case '<=':
                $condition = $this->conditionLtEq($field, $value);
                break;

            case '{}':
                $condition = $this->conditionContains($field, $value);

                break;
            case '!{}':
                $condition = $this->conditionDoesNotContain($field, $value);
                break;

            default:
                throw new \Exception("Undefined operator: {$operator}");
        }

        return $condition;
    }

    /**
     * @param  string $field
     * @param  string  $value
     *
     * @return string
     */
    private function conditionEQ($field, $value)
    {
        return $this->connection->quoteInto("${field} = ?", $value);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionNEQ($field, $value)
    {
        return $this->connection->quoteInto("${field} NOT IN (?)", $value);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionGt($field, $value)
    {
        return $this->connection->quoteInto("${field} > ?", $value);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionGtEq($field, $value)
    {
        return $this->connection->quoteInto("${field} >= ?", $value);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionLt($field, $value)
    {
        return $this->connection->quoteInto("${field} < ?", $value);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionLtEq($field, $value)
    {
        return $this->connection->quoteInto("${field} <= ?", $value);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionIsOneOf($field, $value)
    {
        $value = array_filter($value);

        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("FIND_IN_SET(?, {$field})", $v);
        }

        return implode(' OR ', $parts);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionContains($field, $value)
    {
        $value = array_filter($value);
        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("{$field} LIKE ?", '%'.$v.'%');
        }

        return implode(' OR ', $parts);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionDoesNotContain($field, $value)
    {
        $value = array_filter($value);
        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("{$field} NOT LIKE ?", '%'.$v.'%');
        }

        return implode(' AND ', $parts);
    }

    /**
     * @param  string $field
     * @param  string $value
     *
     * @return string
     */
    private function conditionNotIsOneOf($field, $value)
    {
        $value = array_filter($value);

        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("FIND_IN_SET(?, {$field}) = 0", $v);
        }

        return implode(' AND ', $parts);
    }

    /**
     * @param  string $field
     *
     * @return string
     */
    private function conditionIsUndefined($field)
    {
        $parts = [
            "{$field} IS NULL",
            "{$field} = ''",
        ];

        return implode(' OR ', $parts);
    }

    /**
     * @param Select $select
     * @param string $field
     *
     * @return string
     */
    private function isJoined($select, $field)
    {
        return strpos($select, $field) !== false;
    }
}
