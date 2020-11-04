<?php
/**
 * Webkul Odoomagentoconnect Payment Edit Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Payment;

class ExportAllIds extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $_resultForwardFactory;
    protected $_scopeConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Payment $paymentMapping,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_paymentConfig = $paymentConfig;
        $this->_paymentMapping = $paymentMapping;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::payment_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $exportIds = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $collection = $this->_paymentConfig->getActiveMethods();
            foreach ($collection as $paymentCode => $paymentModel) {
                $paymentMethod = $this->_scopeConfig->getValue('payment/'.$paymentCode.'/title');
                $mapping = $this->_paymentMapping->getCollection()
                                             ->addFieldToFilter('magento_id', ['eq'=>$paymentMethod]);
                if ($mapping->getSize() == 0) {
                    array_push($exportIds, $paymentMethod);
                }
            }
            
            if (count($collection) == 0) {
                $this->messageManager->addSuccess(__("No Payment Methods are exist at Magento."));
            } else {
                if (count($exportIds) == 0) {
                    $this->messageManager->addSuccess(__("All Magento Payment Methods are already exported at Odoo."));
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Payment Method(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($exportIds));
    }
}
