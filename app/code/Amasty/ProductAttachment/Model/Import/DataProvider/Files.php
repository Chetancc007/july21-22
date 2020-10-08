<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Model\Import\DataProvider;

use Amasty\ProductAttachment\Model\Filesystem\ImportFilesScanner;
use Amasty\ProductAttachment\Model\Icon\GetIconForFile;
use Amasty\ProductAttachment\Model\Icon\ResourceModel\Icon;
use Amasty\ProductAttachment\Model\Import\Import;
use Amasty\ProductAttachment\Model\Import\Repository;
use Amasty\ProductAttachment\Model\Import\ResourceModel\ImportCollectionFactory;
use Magento\Framework\Filesystem\Io\File;

class Files extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ImportFilesScanner
     */
    private $importFilesScanner;

    /**
     * @var Icon
     */
    private $iconResource;

    /**
     * @var GetIconForFile
     */
    private $iconForFile;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        File $file,
        ImportCollectionFactory $importCollectionFactory,
        Repository $repository,
        ImportFilesScanner $importFilesScanner,
        GetIconForFile $iconForFile,
        Icon $iconResource,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $importCollectionFactory->create();
        $this->importFilesScanner = $importFilesScanner;
        $this->iconResource = $iconResource;
        $this->iconForFile = $iconForFile;
        $this->repository = $repository;
        $this->file = $file;
    }

    public function getData()
    {
        $data = parent::getData();
        if (empty($data['items'])) {
            $data = [];
            $key = null;
            $data[$key] = [Import::IMPORT_ID => $key];
        } else {
            $key = $data['items'][0][Import::IMPORT_ID];
            $data[$key] = $data['items'][0];
        }

        if ($uploadedFiles = $this->importFilesScanner->execute()) {
            $allowedExtensions = $this->iconResource->getAllowedExtensions();
            $fileId = 100000;
            foreach ($uploadedFiles as $file) {
                list($fileName, $baseName, $extension) = $this->extractPathInfo($file);
                if (in_array($extension, $allowedExtensions)) {
                    $data[$key]['attachments']['files'][] = [
                        'show_file_id' => 'New File',
                        'file_id' => $fileId++,
                        'icon' => $this->iconForFile->byFileExtension($extension),
                        'extension' => $extension,
                        'label' => $fileName,
                        'filename' => $fileName,
                        'include_in_order' => '0',
                        'is_visible' => '1',
                        'customer_groups' => '',
                        'filepath' => $baseName
                    ];
                }
            }
        }

        if ($key) {
            $importFiles = $this->repository->getImportFilesByImportId($key);
            foreach ($importFiles as $importFile) {
                list(, , $extension) = $this->extractPathInfo($importFile->getFilePath());

                $data[$key]['attachments']['files'][] = [
                    'show_file_id' => $importFile->getImportFileId(),
                    'file_id' => $importFile->getImportFileId(),
                    'icon' => $this->iconForFile->byFileExtension($extension),
                    'extension' => $extension,
                    'label' => $importFile->getLabel(),
                    'filename' => $importFile->getFileName(),
                    'include_in_order' => $importFile->isIncludeInOrder() ? '1' : '0',
                    'is_visible' => $importFile->isVisible() ? '1' : '0',
                    'customer_groups' => $importFile->getCustomerGroups()
                ];
            }
        }

        return $data;
    }

    private function extractPathInfo($filePath)
    {
        $pathInfo = $this->file->getPathInfo($filePath);

        return [
            !empty($pathInfo['filename']) ? $pathInfo['filename'] : '',
            !empty($pathInfo['basename']) ? $pathInfo['basename'] : '',
            !empty($pathInfo['extension']) ? $pathInfo['extension'] : '',
        ];
    }
}
