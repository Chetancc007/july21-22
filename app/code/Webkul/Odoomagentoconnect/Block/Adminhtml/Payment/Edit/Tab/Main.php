<?php
/**
 * Webkul Odoomagentoconnect Payment Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Payment\Edit\Tab;
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Payment $payment
    ) {
        $this->_payment = $payment;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $data = array();
        /** @var $model \Magento\User\Model\User */
        $Mage_Payment =$this->_payment->getMagePaymentArray();
        $Erp_Payment = $this->_payment->getErpPaymentArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $paymentmodel = $this->_coreRegistry->registry('odoomagentoconnect_payment');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Payment Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Payment Mapping'), 'class' => 'fieldset-wide']);

        if($paymentmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Payment'),
                    'title' => __('Magento Payment'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $Mage_Payment
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Payment'),
                    'title' => __('Odoo Payment'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $Erp_Payment
                ]
            );

        //$data = $model->getData();
        $data= $paymentmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
