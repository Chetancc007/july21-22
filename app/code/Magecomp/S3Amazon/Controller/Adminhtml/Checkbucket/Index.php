<?php
namespace Magecomp\S3Amazon\Controller\Adminhtml\Checkbucket;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Aws\S3\S3Client;

class Index extends Action
{
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }
    public function execute()
    {
        $postData=$this->getRequest()->getPost();
        try {
            $client = new S3Client(array(
                'version'     => 'latest',
                'credentials' => array(
                    'key'    => $postData['accesskey'],
                    'secret' => $postData['secretkey'],
                ),
                'region'  => $postData['region'],
            ));
            $client->listObjects(array('Bucket' => $postData['new_bucket']));
            $response = [
                'success' => true,
            ];
        } catch(\Exception $e) {
            $response = [
                'success' => false,
            ];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
    protected function _isAllowed()
    {
        return true;
    }
}