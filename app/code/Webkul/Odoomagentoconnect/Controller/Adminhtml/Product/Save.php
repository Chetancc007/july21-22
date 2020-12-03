<?php
/**
 * Webkul Odoomagentoconnect Product Save Controller
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Product;

use Magento\Framework\Exception\AuthenticationException;
use Webkul\Odoomagentoconnect\Helper\Connection;
use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class Save extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Product
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
            $this->messageManager->addSuccess(__('You saved the product.'));
            
            $productId = (int)$this->getRequest()->getParam('entity_id');
            $productmodel = $this->_productMapping;
            if ($productId) {
                $productmodel->load($productId);
                $productmodel->setId($productmodel);
                $data['id']=$productId;
            }
            if ($productId && $productmodel->isObjectNew()) {
                $this->messageManager->addError(__('This product no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
            $data['created_by'] = 'Manual Mapping';
            $productmodel->setData($data);
            $productmodel->save();

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
        if ($userId > 0) {
            $context = $helper->getOdooContext();
            $client = $helper->getClientConnect();
            $instanceId = $context['instance_id'];
            $product = $this->_catalogModel->load($magentoId);
            $stockId = $product->getExtensionAttributes()->getStockItem()->getItemId();
            $prodMapArray = [
                                'name'=>new xmlrpcval($odooId, "int"),
                                'odoo_id'=>new xmlrpcval($odooId, "int"),
                                'ecomm_id'=>new xmlrpcval($magentoId, "int"),
                                'instance_id'=>$instanceId,
                                'magento_stock_id'=>new xmlrpcval($stockId, "int"),
                                'created_by'=>new xmlrpcval('Manual Mapping', "string"),
                            ];
            $context = ['context' => new xmlrpcval($context, "struct")];
            $prodMapArray = [new xmlrpcval($prodMapArray, "struct")];
            $catgMap = new xmlrpcmsg('execute_kw');
            $catgMap->addParam(new xmlrpcval($helper::$odooDb, "string"));
            $catgMap->addParam(new xmlrpcval($userId, "int"));
            $catgMap->addParam(new xmlrpcval($helper::$odooPwd, "string"));
            $catgMap->addParam(new xmlrpcval("connector.product.mapping", "string"));
            $catgMap->addParam(new xmlrpcval("create", "string"));
            $catgMap->addParam(new xmlrpcval($prodMapArray, "array"));
            $catgMap->addParam(new xmlrpcval($context, "struct"));
            $catgMapResp = $client->send($catgMap);
            if ($catgMapResp->errno != 0) {
                return false;
            } else {
                $templateMapArray = [];
                $templateMapArray['ecomm_id'] = new xmlrpcval($magentoId, "int");
                $templateMapArray['odoo_id'] = new xmlrpcval($odooId, "int");
                $templateMapArray['instance_id'] = $instanceId;

                $context = ['context' => new xmlrpcval($context, "struct")];
                $templateMapArray = [new xmlrpcval($templateMapArray, "struct")];
                $msg = new xmlrpcmsg('execute_kw');
                $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
                $msg->addParam(new xmlrpcval($userId, "int"));
                $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
                $msg->addParam(new xmlrpcval("connector.template.mapping", "string"));
                $msg->addParam(new xmlrpcval("create_template_mapping", "string"));
                $msg->addParam(new xmlrpcval($templateMapArray, "array"));
                $msg->addParam(new xmlrpcval($context, "struct"));
                $respMsg = $client->send($msg);
                if ($respMsg->errno != 0) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::product_save');
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
