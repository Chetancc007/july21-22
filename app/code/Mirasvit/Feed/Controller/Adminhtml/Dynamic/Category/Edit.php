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
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

class Edit extends Category
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initPage($resultPage);

        $id = $this->getRequest()->getParam('id');
        $model = $this->initModel();

        if ($id && !$model->getId()) {
            $this->messageManager->addError(__('This item not exists.'));
            return  $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Mapping'));

        return $resultPage;
    }
}
