<?php
namespace Magecomp\S3Amazon\Helper\Core\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\Database as StorageDatabase;
use Magento\MediaStorage\Model\File\Storage\DatabaseFactory;
use Magecomp\S3Amazon\Helper\Data as DataHelper;
use Magecomp\S3Amazon\Model\Core\File\Storage\S3Factory;
class S3Database
{
    protected $helper;
    protected $s3StorageFactory;
    protected $dbStorageFactory;
    protected $storageModel;

    public function __construct(
        DataHelper $helper,
        S3Factory $s3StorageFactory,
        DatabaseFactory $dbStorageFactory
    ) {
        $this->helper = $helper;
        $this->s3StorageFactory = $s3StorageFactory;
        $this->dbStorageFactory = $dbStorageFactory;
    }

    public function afterCheckDbUsage(Database $subject, $result)
    {
        if (!$result) {
            $result = $this->helper->checkS3Usage();
        }
        return $result;
    }
    public function aroundGetStorageDatabaseModel(Database $subject, $proceed)
    {
        if (null === $this->storageModel) {
            if ($subject->checkDbUsage() && $this->helper->checkS3Usage()) {
                $this->storageModel = $this->s3StorageFactory->create();
            } else {
                $this->storageModel = $this->dbStorageFactory->create();
            }
        }
        return $this->storageModel;
    }
    public function aroundSaveFileToFilesystem(Database $subject, $proceed, $filename)
    {
        if ($subject->checkDbUsage() && $this->helper->checkS3Usage()) {
            $file = $subject->getStorageDatabaseModel()->loadByFilename($subject->getMediaRelativePath($filename));
            if (!$file->getId()) {
                return false;
            }
            return $subject->getStorageFileModel()->saveFile($file->getData(), true);
        }
        return $proceed($filename);
    }
    public function afterGetMediaRelativePath(Database $subject, $result)
    {
        $newMediaRelativePath = $result;
        if ($this->helper->checkS3Usage()) {
            $prefixToRemove = 'pub/media/';
            if (substr($result, 0, strlen($prefixToRemove)) == $prefixToRemove) {
                $newMediaRelativePath = substr($result, strlen($prefixToRemove));
            }
        }
        return $newMediaRelativePath;
    }
    public function aroundDeleteFolder(Database $subject, $proceed, $folderName)
    {
        if ($this->helper->checkS3Usage()) {
            $storageModel = $subject->getStorageDatabaseModel();
            $storageModel->deleteDirectory($folderName);
        } else {
            $proceed($folderName);
        }
    }
    public function afterSaveUploadedFile(Database $subject, $result)
    {
        return ltrim($result, '/');
    }
}
