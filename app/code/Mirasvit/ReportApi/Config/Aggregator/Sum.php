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
 * @package   mirasvit/module-report-api
 * @version   1.0.39
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Aggregator;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;

class Sum implements AggregatorInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_SUM;
    }

    /**
     * @return array|string
     */
    public function getExpression()
    {
        return 'IFNULL(SUM(%1), 0)';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Sum';
    }
}
