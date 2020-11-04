<?php

/**
 * Webkul Odoomagentoconnect Attribute Form Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Attribute\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Attribute;
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
    protected $_objectManager;

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
        $Mage_Attribute = $this->getMageAttributeArray();
        $Erp_Attribute = $this->getErpAttributeArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $attributemodel = $this->_coreRegistry->registry('odoomagentoconnect_attribute');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Attribute Mapping'), 'class' => 'fieldset-wide']);

        if($attributemodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Attribute'),
                    'title' => __('Magento Attribute'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Attribute
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Attribute'),
                    'title' => __('Odoo Attribute'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Attribute
                ]
            );

        $data= $attributemodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getMageAttributeArray(){
        $productAttribute = array();
        $productAttribute[''] ='--Select Magento Product Attribute--';
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->addVisibleFilter();
        $collection->getSelect()->where('is_user_defined = 1');
        $collection->getSelect()->where('is_global = 1');

        foreach ($collection as $value) {
            if ($value['frontend_input']=='select'){
                $mage_attribute_id = $value->getAttributeId();
                $mage_attribute_label = $value->getAttributeCode();
                $productAttribute[$mage_attribute_id] = $mage_attribute_label;
            }
        }

        return $productAttribute;
    }

    public function getErpAttributeArray() {
        $productAttribute = array();
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();

        if ($userId > 0){
            $productAttribute[''] ='--Select Odoo Product Attribute--';
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = array();
            $msgSer = new xmlrpcmsg('execute');
            $msgSer->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msgSer->addParam(new xmlrpcval($userId, "int"));
            $msgSer->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msgSer->addParam(new xmlrpcval("product.attribute", "string"));
            $msgSer->addParam(new xmlrpcval("search", "string"));
            $msgSer->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($msgSer);
            if ($resp0->faultCode()) {
                array_push($productAttribute, array('label' => $helper->__('Not Available(Error in Fetching)'), 'value' => ''));
                return $productAttribute;
            }
            else
            {
                $val = $resp0->value()->me['array'];
                $key1 = array(new xmlrpcval('id','string') , new xmlrpcval('name', 'string'));
                $msgSer1 = new xmlrpcmsg('execute');
                $msgSer1->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $msgSer1->addParam(new xmlrpcval($userId, "int"));
                $msgSer1->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $msgSer1->addParam(new xmlrpcval("product.attribute", "string"));
                $msgSer1->addParam(new xmlrpcval("read", "string"));
                $msgSer1->addParam(new xmlrpcval($val, "array"));
                $msgSer1->addParam(new xmlrpcval($key1, "array"));
                $msgSer1->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($msgSer1);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($productAttribute, array('label' => $msg, 'value' => ''));
                    return $productAttribute;
                }
                else
                {   $value_array=$resp1->value()->scalarval();
                    $count = count($value_array);
                    for($x=0;$x<$count;$x++)
                    {
                        $id = $value_array[$x]->me['struct']['id']->me['int'];
                        $name = $value_array[$x]->me['struct']['name']->me['string'];
                        $productAttribute[$id] = $name;
                    }
                }
            }
            return $productAttribute;
        }else{
            $productAttribute['error'] = $errorMessage;
            return $productAttribute;
        }
    }


}
