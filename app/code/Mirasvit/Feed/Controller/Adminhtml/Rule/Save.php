<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.19
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Controller\Adminhtml\Rule;

use Mirasvit\Feed\Controller\Adminhtml\Rule;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\RuleFactory;
use Mirasvit\Feed\Helper\Data as Helper;

class Save extends Rule
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param RuleFactory  $ruleFactory
     * @param Helper       $helper
     * @param Registry     $registry
     * @param Context      $context
     */
    public function __construct(
        RuleFactory      $ruleFactory,
        Helper           $helper,
        Registry         $registry,
        Context          $context,
        ForwardFactory   $resultForwardFactory
    ) {
        $this->helper = $helper;

        parent::__construct($ruleFactory, $registry, $context, $resultForwardFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getParams();

        if ($data) {
            $model = $this->initModel();
            $modelData = $this->helper->removeJS($data['data']);

            $model->addData($modelData);

            if (isset($data['rule'])) {
                $model->loadPost($data['rule']);
            }

            try {
                $model->save();

                $this->messageManager->addSuccess(__('Filter was successfully saved'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        } else {
            $this->messageManager->addError(__('Unable to find item to save'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}
