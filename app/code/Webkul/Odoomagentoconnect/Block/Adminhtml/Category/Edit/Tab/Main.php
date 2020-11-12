<?php
/**
 * Webkul Odoomagentoconnect Category Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Category\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Category;
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
        $Mage_Category = $this->getMageCategoryArray();
        $Erp_Category = $this->getErpCategoryArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $categorymodel = $this->_coreRegistry->registry('odoomagentoconnect_category');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Category Mapping'), 'class' => 'fieldset-wide']);

        if($categorymodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Category'),
                    'title' => __('Magento Category'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Category
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Category'),
                    'title' => __('Odoo Category'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Category
                ]
            );

        $data= $categorymodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getMageCategoryArray(){

        $Product = array();
        $Product[''] ='--Select Magento Category--';
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\Category')->getCollection()->getAllIds();
        
        foreach ($collection as $value) {
            $productObj = $this->_objectManager->create('\Magento\Catalog\Model\Category')->load($value);
            $productId = $productObj->getId();
            $productName = $productObj->getName();
            $Product[$productId] = $productName;
        }
        
        return $Product;
    }

    public function getErpCategoryArray() {
        $category = array();
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();

        if ($userId > 0){
            $category[''] ='--Select Odoo Category--';           
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = array();
            $categorySearch = new xmlrpcmsg('execute');
            $categorySearch->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $categorySearch->addParam(new xmlrpcval($userId, "int"));
            $categorySearch->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $categorySearch->addParam(new xmlrpcval("product.category", "string"));
            $categorySearch->addParam(new xmlrpcval("search", "string"));
            $categorySearch->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($categorySearch);           
            if ($resp0->faultCode()) {
                array_push($category, array('label' => $helper->__('Not Available(Error in Fetching)'), 'value' => ''));
                return $category;
            }
            else 
            {
                $val = $resp0->value()->me['array'];
                $categoryGet = new xmlrpcmsg('execute');
                $categoryGet->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $categoryGet->addParam(new xmlrpcval($userId, "int"));
                $categoryGet->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $categoryGet->addParam(new xmlrpcval("product.category", "string"));
                $categoryGet->addParam(new xmlrpcval("name_get", "string"));
                $categoryGet->addParam(new xmlrpcval($val, "array"));
                $categoryGet->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($categoryGet);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($category, array('label' => $msg, 'value' => ''));
                    return $category;
                } 
                else 
                {   $value_array=$resp1->value()->scalarval();
                    $count = count($value_array);
                    for($x=0;$x<$count;$x++)
                    {
                        $id = $value_array[$x]->me['array'][0]->me['int'];
                        $name = $value_array[$x]->me['array'][1]->me['string'];
                        $category[$id] = $name;
                    }
                }   
            }
            return $category;
        }else{
            $category['error'] = $errorMessage;
            return $category;
        }
    }


}
