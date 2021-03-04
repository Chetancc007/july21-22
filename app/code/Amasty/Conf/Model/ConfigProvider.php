<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Model;

use Magento\Framework\Data\CollectionDataSourceInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract implements CollectionDataSourceInterface
{
    const OUT_OF_STOCK = 'general/show_out_of_stock';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_conf/';

    public function showOutOfStockConfigurableAttributes(): bool
    {
        return (bool) $this->isSetFlag(self::OUT_OF_STOCK);
    }
}
