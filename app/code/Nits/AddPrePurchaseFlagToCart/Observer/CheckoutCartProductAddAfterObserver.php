<?php 
namespace Nits\AddPrePurchaseFlagToCart\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Nits\Porto\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckoutCartProductAddAfterObserver implements ObserverInterface {

    protected $_request;
    protected $productRepository;
    protected $timezoneInterface;

    public function __construct(RequestInterface $request, Data $helper, ProductRepositoryInterface $productRepository, TimezoneInterface $timezoneInterface) {
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_productRepository =  $productRepository;
        $this->timezoneInterface = $timezoneInterface;
    }

    public function execute(EventObserver $observer) {
        $item = $observer->getQuoteItem();
        $additionalOptions = array();
        $prePurchaseAttributeList=['prepurchasestartdate','prepurchaseenddate','is_pre_purchase','is_pre_purchase_product','release_date','quantity_and_stock_status'];
        $current_product=$item->getProductId(); 
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
        $product_label = "";  
        if(!empty($prePurchaseData)){                       
            if(isset($prePurchaseData['is_pre_purchase']) && $prePurchaseData['is_pre_purchase']==1) {
                $now = $this->timezoneInterface->date()->format('Y-m-d');
                $prePurchaseFrom= substr($prePurchaseData['prepurchasestartdate'],0,10);
                $prePurchaseTo=  substr($prePurchaseData['release_date'],0,10);   
                if($prePurchaseData['stock_status']==1 && ($prePurchaseData['quantity']=="" || $prePurchaseData['quantity']<=0)){    
                    if ($prePurchaseTo != '' || $prePurchaseFrom != ''){
                        if (($prePurchaseTo != '' && $prePurchaseFrom != '' && $now>=$prePurchaseFrom && $now<=$prePurchaseTo) || ($prePurchaseTo == '' && $now >=$prePurchaseFrom) || ($prePurchaseFrom == '' && $now<=$prePurchaseTo)) {
                            $product_label = "pre_purchase";
                        }
                    }
                }
            }
        }
        if ($additionalOption = $item->getOptionByCode('additional_options')) {
            $additionalOptions = (array) unserialize($additionalOption->getValue());
        }
        $post = array('pre-purchase' => $product_label);
        if (is_array($post)) {
            foreach ($post as $key => $value) {
                if ($key == '' || $value == '') {
                    continue;
                }

                $additionalOptions[] = array(
                    'label' => $key,
                    'value' => $value
                );
            }
        }

        if (count($additionalOptions) > 0) {
            $item->addOption(array(
                'product_id' => $item->getProductId(),
                'code' => 'additional_options',
                'value' => json_encode($additionalOptions)
            ));
        }

    }

}
?>