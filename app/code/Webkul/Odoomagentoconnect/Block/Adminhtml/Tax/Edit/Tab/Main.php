<?php
/**
 * Webkul Odoomagentoconnect Tax Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Tax\Edit\Tab;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    const CURRENT_USER_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;



    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_authSession = $authSession;
        $this->_objectManager = $objectManager;
        $this->_LocaleLists = $localeLists;
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
        $Mage_Tax = $this->getMageTaxArray();
        $Erp_Tax = $this->getErpTaxArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $taxmodel = $this->_coreRegistry->registry('odoomagentoconnect_tax');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Tax Mapping'), 'class' => 'fieldset-wide']);

        if($taxmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Tax'),
                    'title' => __('Magento Tax'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Tax
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Tax'),
                    'title' => __('Odoo Tax'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Tax
                ]
            );

        //$data = $model->getData();
        $data= $taxmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getMageTaxArray(){
        $Tax = array();
        $Tax[''] ='--Select Magento Tax--';
        $tax_coll = $this->_objectManager->create('\Magento\Tax\Model\Calculation\Rate')->getCollection()->getData();
        foreach ($tax_coll as $tax) {
            $mage_tax_id = $tax['tax_calculation_rate_id'];
            $mage_tax_code = $tax["code"];
            $mage_tax_rate = $tax["rate"];
            $t = $mage_tax_code.'('.$mage_tax_rate.'%)';
            $Tax[$mage_tax_id] = $t;
        }
        return $Tax;
    }

    public function getErpTaxArray() {
        $Tax = array();
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();

        if ($userId > 0){
            $Tax[''] ='--Select Odoo Tax--';            
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = array();
            $msgSer = new xmlrpcmsg('execute');
            $msgSer->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msgSer->addParam(new xmlrpcval($userId, "int"));
            $msgSer->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msgSer->addParam(new xmlrpcval("account.tax", "string"));
            $msgSer->addParam(new xmlrpcval("search", "string"));
            $msgSer->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($msgSer);
            if ($resp0->faultCode()) {
                array_push($Tax, array('label' => $helper->__('Not Available(Error in Fetching)'), 'value' => ''));
                return $Tax;
            }
            else 
            {
                $val = $resp0->value()->me['array'];
                $key1 = array(new xmlrpcval('id','string') , new xmlrpcval('name', 'string'),new xmlrpcval('amount', 'string'));
                $msgSer1 = new xmlrpcmsg('execute');
                $msgSer1->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $msgSer1->addParam(new xmlrpcval($userId, "int"));
                $msgSer1->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $msgSer1->addParam(new xmlrpcval("account.tax", "string"));
                $msgSer1->addParam(new xmlrpcval("read", "string"));
                $msgSer1->addParam(new xmlrpcval($val, "array"));
                $msgSer1->addParam(new xmlrpcval($key1, "array"));
                $msgSer1->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($msgSer1);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($Tax, array('label' => $msg, 'value' => ''));
                    return $Tax;
                } 
                else 
                {   $value_array=$resp1->value()->scalarval();
                    $count = count($value_array);
                    for($x=0;$x<$count;$x++)
                    {
                       $id = $value_array[$x]->me['struct']['id']->me['int'];
                       $name = $value_array[$x]->me['struct']['name']->me['string'].' ('.($value_array[$x]->me['struct']['amount']->me['double']).'%)';
                       $Tax[$id] = $name;
                    }
                }   
            }
            return $Tax;
        }else{
            $Tax['error'] = $errorMessage;
            return $Tax;
        }
    }
}
