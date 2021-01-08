<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Model\Cart;

use Amasty\StorePickup\Helper\Data;
use Amasty\StorePickup\Model\MethodFactory;
use Amasty\StorePickup\Model\ResourceModel\Label\CollectionFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class ShippingMethodConverter
{
    /**
     * @var CollectionFactory
     */
    private $labelCollectionFactory;
    /**
     * @var MethodFactory
     */
    private $methodFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var ExtensionAttributesFactory
     */
    private $attributesFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CollectionFactory $labelCollectionFactory,
        MethodFactory $methodFactory,
        Data $helperData,
        ExtensionAttributesFactory $attributesFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->methodFactory = $methodFactory;
        $this->helperData = $helperData;
        $this->attributesFactory = $attributesFactory;
        $this->storeManager = $storeManager;
    }

    public function afterModelToDataObject(
        \Magento\Quote\Model\Cart\ShippingMethodConverter $subject,
        $result
    ) {
        if ($result->getCarrierCode() == 'amstorepick') {
            $methodId = str_replace('amstorepick', '', $result->getMethodCode());
            $storeId = $subject->storeManager->getStore()->getId();
            /** @var \Amasty\StorePickup\Model\ResourceModel\Label\Collection $label */
            $label = $this->labelCollectionFactory->create()
                ->addFiltersByMethodIdStoreId($methodId, $storeId)
                ->getLastItem();
            /** @var \Amasty\StorePickup\Model\Method $method */
            $method = $this->methodFactory->create()->load($methodId);
            $comment = $label->getComment() != "" ? $label->getComment() : $method->getComment();
            $comment = $this->helperData->escapeHtml($comment);
            if ($comment) {
                if ($img = $method->getCommentImg()) {
                    $imgUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $img;
                    $comment = str_replace('{IMG}', '<img src="' . $imgUrl . '" />', $comment);
                }

                $extAttributes = $result->getExtensionAttributes();
                if ($extAttributes === null) {
                    $extAttributes = $this->attributesFactory
                        ->create(\Magento\Quote\Api\Data\ShippingMethodInterface::class);
                }
                $extAttributes->setAmstorepickComment(__($comment));
                $result->setExtensionAttributes($extAttributes);
                $result->setComment(__($comment));
            }
        }

        return $result;
    }
}
