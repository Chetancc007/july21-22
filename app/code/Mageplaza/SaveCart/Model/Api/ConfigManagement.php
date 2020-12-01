<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Model\Api;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SaveCart\Api\ConfigManagementInterface;
use Mageplaza\SaveCart\Api\Data\ConfigInterface;
use Mageplaza\SaveCart\Helper\Data;

/**
 * Class ConfigManagement
 * @package Mageplaza\SaveCart\Model\Api
 */
class ConfigManagement implements ConfigManagementInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * ConfigManagement constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request    = $request;
        $this->helperData = $helperData;
    }

    /**
     * @return DataObject|ConfigInterface
     * @throws NoSuchEntityException
     */
    public function get()
    {
        $configKeys = ['enabled', 'button_tittle', 'show_button_guest', 'page_link_area', 'allow_share', 'icon'];
        $configs    = [];
        $storeId    = $this->request->getParam('storeId');
        foreach ($configKeys as $configKey) {
            $configs[$configKey] = $this->helperData->getConfigGeneral($configKey, $storeId);
        }

        $configs['icon_url'] = $this->helperData->getIconUrl($storeId);

        return new DataObject($configs);
    }
}
