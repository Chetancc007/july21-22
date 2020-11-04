<?php
/**
 * Webkul Odoomagentoconnect Attribute Update Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Attribute;

class Update extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $_resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Attribute $attributeModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_attributeModel = $attributeModel;
        $this->_connection = $connection;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::attribute_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $notUpdatedAttribute = 0;
            $updatedAttribute = 0;
            $response = $this->_attributeModel
                                ->updateAttribute();
            if ($response) {
                $notUpdatedAttribute = $response[1];
                $updatedAttribute = $response[0];
            }
            if ($notUpdatedAttribute) {
                $this->messageManager->addError(
                    __(
                        '%1 attribute(s) cannot be update at Odoo.',
                        $notUpdatedAttribute
                    )
                );
            }
            if ($updatedAttribute) {
                $this->messageManager
                    ->addSuccess(
                        __(
                            'Total of %1 attribute(s) have been successfully update at Odoo.',
                            $updatedAttribute
                        )
                    );
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Attribute(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $resultForward = $this->_resultForwardFactory->create();
        return $resultForward->forward('index');
    }
}
