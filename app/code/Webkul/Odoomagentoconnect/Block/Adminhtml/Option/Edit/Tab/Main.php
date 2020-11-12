<?php
/**
 * Webkul Odoomagentoconnect Attribute Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Option\Edit\Tab;
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
    const CURRENT_OPTION_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;
    protected $_objectManager;



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
        $this->_LocaleLists = $localeLists;
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
        $Mage_Option = $this->getMageOptionArray();
        $Erp_Option = $this->getErpOptionArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $optionmodel = $this->_coreRegistry->registry('odoomagentoconnect_option');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Option Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Option Mapping'), 'class' => 'fieldset-wide']);

        if($optionmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Option'),
                    'title' => __('Magento Option'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Option
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Option'),
                    'title' => __('Odoo Option'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Option
                ]
            );

        //$data = $model->getData();
        $data= $optionmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getMageOptionArray(){
        $attributeOption = array();
        $attributeOption[''] ='--Select Magento Product Attribute Option--';
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->addVisibleFilter();
        $collection->getSelect()->where('is_user_defined = 1');
        // $collection->getSelect()->where('is_configurable = 1');
        $collection->getSelect()->where('is_global = 1');
        $collection->getSelect()->where("frontend_input = 'select'");
        foreach ($collection as $value) {
            $attributeName = $value->getAttributeCode();
            $options = $value->getSource()->getAllOptions(false);
            foreach ($options as $key) {
                $mageOptionId = $key['value'];
                $mageOptionLabel = $key['label'];
                $attributeOption[$mageOptionId] = "$attributeName: $mageOptionLabel";
            }
        }
        return $attributeOption;
    }

    public function getErpOptionArray() {
        $attributeOption = array();
        $helper = $this->_objectManager->create('\Webkul\Odoomagentoconnect\Helper\Connection');
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();

        if ($userId > 0){
            $attributeOption[''] ='--Select Odoo Product Attribute Option--';
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $key = array();
            $msgSer = new xmlrpcmsg('execute');
            $msgSer->addParam(new xmlrpcval(Connection::$odooDb, "string"));
            $msgSer->addParam(new xmlrpcval($userId, "int"));
            $msgSer->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
            $msgSer->addParam(new xmlrpcval("product.attribute.value", "string"));
            $msgSer->addParam(new xmlrpcval("search", "string"));
            $msgSer->addParam(new xmlrpcval($key, "array"));
            $resp0 = $client->send($msgSer);
            if ($resp0->faultCode()) {
                array_push($attributeOption, array('label' => $helper->__('Not Available(Error in Fetching)'), 'value' => ''));
                return $attributeOption;
            }
            else
            {
                $val = $resp0->value()->me['array'];
                $msgSer1 = new xmlrpcmsg('execute');
                $msgSer1->addParam(new xmlrpcval(Connection::$odooDb, "string"));
                $msgSer1->addParam(new xmlrpcval($userId, "int"));
                $msgSer1->addParam(new xmlrpcval(Connection::$odooPwd, "string"));
                $msgSer1->addParam(new xmlrpcval("product.attribute.value", "string"));
                $msgSer1->addParam(new xmlrpcval("name_get", "string"));
                $msgSer1->addParam(new xmlrpcval($val, "array"));
                $msgSer1->addParam(new xmlrpcval($context, "struct"));
                $resp1 = $client->send($msgSer1);

                if ($resp1->faultCode()) {
                    $msg = $helper->__('Not Available- Error: ').$resp1->faultString();
                    array_push($attributeOption, array('label' => $msg, 'value' => ''));
                    return $attributeOption;
                }
                else
                {   $value_array=$resp1->value()->scalarval();
                    $count = count($value_array);
                    for($x=0;$x<$count;$x++)
                    {
                        $id = $value_array[$x]->me['array'][0]->me['int'];
                        $name = $value_array[$x]->me['array'][1]->me['string'];
                        $attributeOption[$id] = $name;
                    }
                }
            }
            return $attributeOption;
        }else{
            $attributeOption['error'] = $errorMessage;
            return $attributeOption;
        }
    }



}
