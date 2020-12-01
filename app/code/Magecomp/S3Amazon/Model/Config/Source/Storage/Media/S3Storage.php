<?php
namespace Magecomp\S3Amazon\Model\Config\Source\Storage\Media;
use Magento\MediaStorage\Model\Config\Source\Storage\Media\Storage;
class S3Storage
{
    public function afterToOptionArray(Storage $subject, $result)
    {
        $result[] = [
            'value' => \Magecomp\S3Amazon\Model\Core\File\Storage::STORAGE_MEDIA_S3,
            'label' => __('Magecomp S3Amazon'),
        ];
        return $result;
    }
}