<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Controller\Adminhtml\Import;
use Amasty\ProductAttachment\Model\Import\Import as ImportModel;
use Amasty\ProductAttachment\Model\Import\ImportFactory;
use Amasty\ProductAttachment\Model\Import\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends Import
{
    /**
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        ImportFactory $importFactory,
        Repository $repository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->importFactory = $importFactory;
        $this->repository = $repository;
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                if (isset($data['filesData'])) {
                    $data = json_decode($data['filesData'], true);
                }
                if (isset($data['step'])) {
                    switch ($data['step']) {
                        case '1':
                            return $this->processFilesStep($data);
                        case '2':
                            return $this->processStoresStep($data);
                    }
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->_redirect('*/*/');
            }
        }
        return $this->_redirect('*/*/');
    }

    public function processFilesStep($filesData)
    {
        /** @var \Amasty\ProductAttachment\Model\Import\Import $model */
        $model = $this->importFactory->create();
        if ($importId = (int)$this->getRequest()->getParam(ImportModel::IMPORT_ID)) {
            $model = $this->repository->getById($importId);
            if ($importId != $model->getImportId()) {
                throw new LocalizedException(__('The wrong item is specified.'));
            }
        }

        $model->addData($filesData);
        $this->repository->save($model);

        if (!empty($filesData['attachments'])) {
            $this->repository->saveImportFiles(
                $model->getImportId(),
                $filesData['attachments']['files']
            );

        }

        return $this->_redirect('*/*/store', [ImportModel::IMPORT_ID => $model->getId()]);
    }

    public function processStoresStep($data)
    {
        if ($importId = (int)$this->getRequest()->getParam(ImportModel::IMPORT_ID)) {
            $model = $this->repository->getById($importId);
            if ($importId != $model->getImportId()) {
                throw new LocalizedException(__('The wrong item is specified.'));
            }
        } else {
            throw new LocalizedException(__('The wrong item is specified.'));
        }

        $model->addData($data);
        $this->repository->save($model);

        return $this->_redirect('*/*/fileimport', [ImportModel::IMPORT_ID => $model->getId()]);
    }
}
