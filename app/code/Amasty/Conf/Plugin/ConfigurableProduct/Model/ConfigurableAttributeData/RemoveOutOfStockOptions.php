<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


declare(strict_types=1);

namespace Amasty\Conf\Plugin\ConfigurableProduct\Model\ConfigurableAttributeData;

use Amasty\Conf\Model\ConfigProvider;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;

class RemoveOutOfStockOptions
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function afterGetAttributesData(ConfigurableAttributeData $subject, array $result): array
    {
        if (!$this->configProvider->showOutOfStockConfigurableAttributes()) {
            foreach ($result['attributes'] as $key => $attribute) {
                foreach ($attribute['options'] as $optionId => $option) {
                    if (!$option['products']) {
                        unset($result['attributes'][$key]['options'][$optionId]);
                    }
                }
            }
        }

        return $result;
    }
}
