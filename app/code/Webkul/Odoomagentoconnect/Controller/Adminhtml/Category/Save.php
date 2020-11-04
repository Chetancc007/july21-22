<?php
/**
 * Webkul Odoomagentoconnect Category Save Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Category;

use Magento\Framework\Exception\AuthenticationException;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class Save extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Category
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
            $this->messageManager->addSuccess(__('You saved the category.'));
            
            $categoryId = (int)$this->getRequest()->getParam('entity_id');
            $categorymodel = $this->_categoryMapping;
            if ($categoryId) {
                $categorymodel->load($categoryId);
                $categorymodel->setId($categorymodel);
                $data['id']=$categoryId;
            }
            if ($categoryId && $categorymodel->isObjectNew()) {
                $this->messageManager->addError(__('This category no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
            $data['name'] = 'Manual Mapping';
            $data['created_by'] = 'Manual Mapping';
            $categorymodel->setData($data);
            $categorymodel->save();
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
            $instanceId = $context['instance_id'];
            $mapArray = [
                                'cat_name'=>new xmlrpcval($odooId, "int"),
                                'oe_category_id'=>new xmlrpcval($odooId, "int"),
                                'mag_category_id'=>new xmlrpcval($magentoId, "int"),
                                'instance_id'=>$instanceId,
                                'created_by'=>new xmlrpcval('Manual Mapping', "string"),
                            ];
            $catgMap = new xmlrpcmsg('execute');
            $catgMap->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $catgMap->addParam(new xmlrpcval($userId, "int"));
            $catgMap->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $catgMap->addParam(new xmlrpcval("magento.category", "string"));
            $catgMap->addParam(new xmlrpcval("create", "string"));
            $catgMap->addParam(new xmlrpcval($mapArray, "struct"));
            $catgMap->addParam(new xmlrpcval($context, "struct"));
            $catgMapResp = $client->send($catgMap);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::category_save');
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
