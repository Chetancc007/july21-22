<?php
namespace Magecomp\S3Amazon\Model\Core\File\Storage;
use Magento\MediaStorage\Model\File\Storage\Database;
use Magecomp\S3Amazon\Helper\Data as S3Helper;
use Magecomp\S3Amazon\Model\Core\File\Storage\S3 as S3Storage;
class S3Database
{
    protected $helper;
    protected $storageModel;

    public function __construct(
        S3Helper $helper,
        S3Storage $storageModel
    ) {
        $this->helper = $helper;
        $this->storageModel = $storageModel;
    }

    public function aroundGetDirectoryFiles(Database $subject, $proceed, $directory)
    {
        if ($this->helper->checkS3Usage()) {
            return $this->storageModel->getDirectoryFiles($directory);
        }
        return $proceed($directory);
    }
}