<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


declare(strict_types=1);

namespace Amasty\ShopbyBase\Api\Data;

use Magento\Framework\Exception\NoSuchEntityException;

interface OptionSettingRepositoryInterface
{
    /**
     * @return OptionSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($value, $field = null);

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     */
    public function getByParams($filterCode, $optionId, $storeId);

    /**
     * @param OptionSettingInterface $optionSetting
     * @return OptionSettingRepositoryInterface
     */
    public function save(OptionSettingInterface $optionSetting);

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId);
}
