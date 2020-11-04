<?php
/**
 * Webkul Odoomagentoconnect Currency Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Currency\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Option;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;        
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $Mage_Currency = $this->getMageCurrencyArray();
        $Erp_Currency = $this->getErpCurrencyArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $currencymodel = $this->_coreRegistry->registry('odoomagentoconnect_currency');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Currency Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Currency Mapping'), 'class' => 'fieldset-wide']);

        if($currencymodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Currency'),
                    'title' => __('Magento Currency'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Currency
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Currency'),
                    'title' => __('Odoo Currency'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Currency
                ]
            );
        $data= $currencymodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getMageCurrencyArray(){
        $Currency = array();
        $Currency[''] ='--Select Magento Currency--';
        $collection = $this->_objectManager->create('\Magento\Directory\Model\Currency')->getConfigAllowCurrencies();
        foreach ($collection as $currency) {
            $Currency[$currency] = $currency;
        }
        
        return $Currency;
    }

    public function getErpCurrencyArray() {
        $Currency = array();
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();
        if ($userId > 0){
            $Currency[''] ='--Select Odoo Currency--';          
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = array();
            $msgSer = new xmlrpcmsg('execute');
            $msgSer->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msgSer->addParam(new xmlrpcval($userId, "int"));
            $msgSer->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msgSer->addParam(new xmlrpcval("product.pricelist", "string"));
            $msgSer->addParam(new xmlrpcval("search", "string"));
            $msgSer->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($msgSer);           
            if ($resp0->faultCode()) {
                array_push($Currency, array('label' => $helper->__('Not Available(Error in Fetching)'), 'value' => ''));
                return $Currency;
            }
            else 
            {
                $val = $resp0->value()->me['array'];
                $key1 = array(new xmlrpcval('id','string') , new xmlrpcval('name', 'string'),new xmlrpcval('currency_id', 'string'));
                $msgSer1 = new xmlrpcmsg('execute');
                $msgSer1->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $msgSer1->addParam(new xmlrpcval($userId, "int"));
                $msgSer1->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $msgSer1->addParam(new xmlrpcval("product.pricelist", "string"));
                $msgSer1->addParam(new xmlrpcval("read", "string"));
                $msgSer1->addParam(new xmlrpcval($val, "array"));
                $msgSer1->addParam(new xmlrpcval($key1, "array"));
                $resp1 = $client->send($msgSer1);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($Currency, array('label' => $msg, 'value' => ''));
                    return $Currency;
                } 
                else 
                {   $value_array=$resp1->value()->scalarval();
                    $count = count($value_array);
                    for($x=0;$x<$count;$x++)
                    {
                        $id = $value_array[$x]->me['struct']['id']->me['int'];
                        $name = $value_array[$x]->me['struct']['name']->me['string'];
                        $code = $value_array[$x]->me['struct']['currency_id']->me['array'][1]->me['string'];
                        $Currency[$id] = $name.' - '.$code;
                    }
                }   
            }
            return $Currency;
        }else{
            $Currency['error'] = $errorMessage;

            // array_push($Currency, array('label' => $errorMessage, 'value' => ''));
            return $Currency;
        }
    }



}
