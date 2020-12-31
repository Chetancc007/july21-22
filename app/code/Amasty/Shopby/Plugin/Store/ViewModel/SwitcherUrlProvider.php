<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Store\ViewModel;

use \Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;
use Amasty\ShopbyBase\Helper\Data;

class SwitcherUrlProvider
{
    const STORE_PARAM_NAME = '___store';
    const FROM_STORE_PARAM_NAME = '___from_store';

    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $encoder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    public function __construct(
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $emulation,
        DataPersistorInterface $dataPersistor
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStoreRedirectUrl($subject, callable $proceed, Store $store)
    {
        $this->emulation->startEnvironmentEmulation(
            $store->getStoreId(),
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_scope'] = $store;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null, 'amshopby' => null];
        $this->dataPersistor->set(Data::SHOPBY_SWITCHER_STORE_ID, $store->getId());
        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);
        $this->dataPersistor->clear(Data::SHOPBY_SWITCHER_STORE_ID);

        $this->emulation->stopEnvironmentEmulation();
        return $this->urlBuilder->getUrl(
            'stores/store/redirect',
            [
                self::STORE_PARAM_NAME => $store->getCode(),
                self::FROM_STORE_PARAM_NAME => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($currentUrl),
            ]
        );
    }
}
