<?php
/**
 * Webkul Odoomagentoconnect Option Save Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Option;

use Magento\Framework\Exception\AuthenticationException;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class Save extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Option
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('odoomagentoconnect/*/');
            return;
        }

        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        try {
            $this->messageManager->addSuccess(__('You saved the option.'));
            
            $optionId = (int)$this->getRequest()->getParam('entity_id');
            $optionmodel = $this->_optionMapping;
            if ($optionId) {
                $optionmodel->load($optionId);
                $optionmodel->setId($optionmodel);
                $data['id']=$optionId;
            }
            if ($optionId && $optionmodel->isObjectNew()) {
                $this->messageManager->addError(__('This option no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
            $optionValue = $this->_attrOptionCollectionFactory->create()
                ->setPositionOrder('asc')
                ->setIdFilter($data['magento_id'])
                ->setStoreFilter()
                ->load()
                ->getFirstItem()
                ->getValue();
            $data['name'] = $optionValue;
            $data['created_by'] = 'Manual Mapping';
            $optionmodel->setData($data);
            $optionmodel->save();
            $this->_mapOnErp($data['magento_id'], $data['odoo_id']);
            
            $this->_getSession()->setUserData(false);
            $this->_redirect('odoomagentoconnect/*/');
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($data);
        }
    }

    protected function _mapOnErp($magentoId, $odooId)
    {
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $errorMessage = $helper->getSession()->getErrorMessage();
        if ($userId > 0) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $mapArray = [
                                'name'=>new xmlrpcval($odooId, "int"),
                                'odoo_id'=>new xmlrpcval($odooId, "int"),
                                'ecomm_id'=>new xmlrpcval($magentoId, "int"),
                                'created_by'=>new xmlrpcval('Manual Mapping', "string"),
                            ];
            $context = ['context' => new xmlrpcval($context, "struct")];
            $mapArray = [new xmlrpcval($mapArray, "struct")];
            $catgMap = new xmlrpcmsg('execute_kw');
            $catgMap->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $catgMap->addParam(new xmlrpcval($userId, "int"));
            $catgMap->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $catgMap->addParam(new xmlrpcval("connector.option.mapping", "string"));
            $catgMap->addParam(new xmlrpcval("create", "string"));
            $catgMap->addParam(new xmlrpcval($mapArray, "array"));
            $catgMap->addParam(new xmlrpcval($context, "struct"));
            $catgMapResp = $client->send($catgMap);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::option_save');
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(array $data)
    {
        $this->_getSession()->setUserData($data);
        $data['entity_id']=isset($data['entity_id'])?$data['entity_id']:0;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        if ($data['entity_id']) {
            $this->_redirect('odoomagentoconnect/*/edit', $arguments);
        } else {
            $this->_redirect('odoomagentoconnect/*/index', $arguments);
        }
    }
}
