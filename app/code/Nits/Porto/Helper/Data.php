<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nits\Porto\Helper;

use Magento\Framework\Registry;

class Data extends \Smartwave\Porto\Helper\Data
{

    protected $_objectManager;
    private $_registry;
    protected $_filterProvider;
    private $_checkedPurchaseCode;
    private $_messageManager;
    protected $_configFactory;
    protected $_productCollectionFactory;
    private $productRepository;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory,
        Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository

    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_filterProvider = $filterProvider;
        $this->_registry = $registry;
        $this->_messageManager = $messageManager;
        $this->_configFactory = $configFactory;
        $this->_productRepository = $productRepository;
        parent::__construct($context,$objectManager,$storeManager,$filterProvider,$messageManager,$configFactory,$registry);
    }
    
    public function getPrepurchaseData($product){
        $current_product = $product->getId();
        $prePurchaseAttributeList=['prepurchasestartdate','prepurchaseenddate','is_pre_purchase','is_pre_purchase_product','release_date'];
        $product = $this->_productRepository->getById($current_product);
        $attributes = $product->getAttributes();
        $prePurchaseData=[];
        foreach($attributes as $a)
        {
            if(in_array($a->getName(),$prePurchaseAttributeList)){
                if (($a->getFrontendInput() != 'boolean')) {
                     $prePurchaseData[$a->getName()]=$product->getData($a->getName());
                }
                else{	
                    $prePurchaseData[$a->getName()]=$product->getData($a->getName());
                }
            }
        }
        return  $prePurchaseData;   

    }
}
