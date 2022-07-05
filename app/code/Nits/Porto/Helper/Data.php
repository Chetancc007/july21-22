<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nits\Porto\Helper;

use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends \Smartwave\Porto\Helper\Data
{
    
    protected $_objectManager;
    private $_registry;
    protected $_filterProvider;
    private $_checkedPurchaseCode;
    private $_messageManager;
    protected $_configFactory;
    private $productRepository;
    protected $timezoneInterface;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory,
        Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        TimezoneInterface $timezoneInterface

    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_filterProvider = $filterProvider;
        $this->_registry = $registry;
        $this->_messageManager = $messageManager;
        $this->_configFactory = $configFactory;
        $this->_productRepository = $productRepository;
        $this->timezoneInterface = $timezoneInterface;
        parent::__construct($context,$objectManager,$storeManager,$filterProvider,$messageManager,$configFactory,$registry);
    }
    
    public function getPrepurchaseData($product){
        $current_product = $product->getId();
        $prePurchaseAttributeList=['prepurchasestartdate','prepurchaseenddate','is_pre_purchase','is_pre_purchase_product','release_date','quantity_and_stock_status'];
        $product = $this->_productRepository->getById($current_product);
        $attributes = $product->getAttributes();
        $prePurchaseData=[];
        $prePurchaseProductData=[];   
        $prePurchaseData['stock_status']='';
        $prePurchaseData['quantity']='';
        foreach($attributes as $a)
        {
            if(in_array($a->getName(),$prePurchaseAttributeList)){
                if (($a->getFrontendInput() != 'boolean')) {
                    if(! is_array($product->getData($a->getName()))){
                        $prePurchaseData[$a->getName()]=$product->getData($a->getName());
                    }
                    else{
                        $arrVal=$product->getData($a->getName());
                        $prePurchaseData['stock_status']=$arrVal[array_key_first($arrVal)];
                        $prePurchaseData['quantity']=$arrVal[array_key_last($arrVal)];
                    }
                }
                else{	
                    $prePurchaseData[$a->getName()]=$product->getData($a->getName());
                }
            }
        }
        
        $prePurchaseButton="";
        $product_label = "";  
        $button_title='Add to Cart';
        if(!empty($prePurchaseData)){                       
            if(isset($prePurchaseData['is_pre_purchase']) && $prePurchaseData['is_pre_purchase']==1) {
                $now = $this->timezoneInterface->date()->format('Y-m-d');
                $prePurchaseFrom= substr($prePurchaseData['prepurchasestartdate'],0,10);
                $prePurchaseTo=  substr($prePurchaseData['release_date'],0,10);
                if($prePurchaseData['stock_status']==1 && ($prePurchaseData['quantity']=="" || $prePurchaseData['quantity']<=0)){    
                    if ($prePurchaseTo != '' || $prePurchaseFrom != ''){
                        if (($prePurchaseTo != '' && $prePurchaseFrom != '' && $now>=$prePurchaseFrom && $now<=$prePurchaseTo) || ($prePurchaseTo == '' && $now >=$prePurchaseFrom) || ($prePurchaseFrom == '' && $now<=$prePurchaseTo)) {
                            $prePurchaseButton="Yes";
                            $product_label = "pre_purchase";
                            $button_title ='Pre-Purchase';
                        }
                        if (($prePurchaseTo != '' && $prePurchaseFrom != '' && $now>$prePurchaseFrom && $now>$prePurchaseTo) || ($prePurchaseTo == '' && $now >$prePurchaseFrom) || ($prePurchaseFrom == '' && $now>$prePurchaseTo)) {
                            $prePurchaseButton="Special";
                            $button_title = 'Special Order';
                        }
                    }
                }
                if($prePurchaseData['stock_status']!=1 && ($prePurchaseData['quantity']=="" || $prePurchaseData['quantity']<=0)){
                    $prePurchaseButton="No";
                    $button_title = '';
                }
            }
        }
        $prePurchaseProductData['prePurchaseButton']=$prePurchaseButton;
        $prePurchaseProductData['product_label']=$product_label;
        $prePurchaseProductData['button_title']=$button_title;
        return  $prePurchaseProductData;   

    }
}
