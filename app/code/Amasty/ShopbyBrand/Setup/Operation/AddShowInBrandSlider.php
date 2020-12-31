<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


declare(strict_types=1);

namespace Amasty\ShopbyBrand\Setup\Operation;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddShowInBrandSlider
{
    public function execute(SchemaSetupInterface $setup): void
    {
        $tableName = $setup->getTable('amasty_amshopby_option_setting');
        $setup->getConnection()->addColumn(
            $tableName,
            OptionSettingInterface::IS_SHOW_IN_SLIDER,
            [
                'type'     => Table::TYPE_SMALLINT,
                'default'  => 0,
                'nullable' => false,
                'comment'  => 'Is show in slider'
            ]
        );
    }
}
