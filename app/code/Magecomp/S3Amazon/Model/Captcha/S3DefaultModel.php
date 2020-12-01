<?php
namespace Magecomp\S3Amazon\Model\Captcha;

use Magento\Captcha\Model\DefaultModel;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magecomp\S3Amazon\Helper\Data;
class S3DefaultModel
{
    protected $helper;
    protected $database;

    public function __construct(
        Data $helper,
        Database $database
    ) {
        $this->helper = $helper;
        $this->database = $database;
    }
    public function afterGenerate(DefaultModel $subject, $result)
    {
        if ($this->helper->checkS3Usage()) {
            $imgFile = $subject->getImgDir() . $result . $subject->getSuffix();
            $relativeImgFile = $this->database->getMediaRelativePath($imgFile);
            $this->database->getStorageDatabaseModel()->saveFile($relativeImgFile);
        }
        return $result;
    }
}