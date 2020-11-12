<?php
/**
 * Webkul Odoomagentoconnect Logs Downloadfile Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Logs;

class Downloadfile extends \Magento\Backend\App\Action
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
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->directory_list = $directory_list;
        $this->jsonHelper = $jsonHelper;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::synchronization_logs');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $dir = $this->directory_list->getPath('log');
        $files = scandir($dir);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logfile = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue('odoomagentoconnect/additional/view_log');
        if (!$logfile) {
            $logfile = "odoo_connector.log";
        }
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            if ($file == $logfile) {
                $filepath = $dir.'/'.$file ;
                if (file_exists($filepath)) {
                    $response = $this->getResponse();
                    $response->setHeader('Content-Description: File Transfer', true);
                    $response->setHeader('Content-Type: application/octet-stream', true);
                    $response->setHeader('Content-Disposition: attachment; filename="'.basename($filepath).'"', true);
                    $response->setHeader('Expires: 0', true);
                    $response->setHeader('Cache-Control: must-revalidate', true);
                    $response->setHeader('Pragma: public', true);
                    $response->setHeader('Content-Length: ' . filesize($filepath), true);
                    readfile($filepath);
                    $this->getResponse()->setBody($this->jsonHelper->jsonEncode(array()));
                    $response->sendResponse();
                }
            }
        }
    }
}