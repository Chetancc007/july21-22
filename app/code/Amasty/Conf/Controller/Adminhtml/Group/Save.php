<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml\Group;

use Amasty\Conf\Model\GroupAttr;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Conf\Controller\Adminhtml\Group
{
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = (int)$this->getRequest()->getParam('group_id');
            if ($id) {
                $model = $this->groupAttrRepository->get($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This group no longer exists.'));
                    $this->sessionFactory->create()->setFormData($data);

                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $model = $this->groupAttrFactory->create();
            }

            try {
                $data = $this->validateData($model, $data);
                $model->setData($data);
                $this->groupAttrRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You have saved the group.'));
                $this->sessionFactory->create()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['group_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->sessionFactory->create()->setFormData($data);

                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param  \Amasty\Conf\Api\Data\GroupAttrInterface $model
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     */
    protected function validateData($model, array $data)
    {
        if (!$model->getId() || ($model->getGroupCode() != $data['group_code'])) {
            $code = $data['group_code'];
            if ($this->groupAttrFactory->create()->getCollection()
                ->addFieldToFilter(GroupAttr::GROUP_CODE, $code)->getSize()
            ) {
                throw new LocalizedException(__('This group code already exists.'));
            }
        }

        return $data;
    }
}
