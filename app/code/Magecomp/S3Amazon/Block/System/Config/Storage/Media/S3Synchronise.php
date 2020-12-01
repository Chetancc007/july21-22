<?php
namespace Magecomp\S3Amazon\Block\System\Config\Storage\Media;
class S3Synchronise
{
    public function aroundGetTemplate()
    {
        return 'Magecomp_S3Amazon::system/config/storage/media/synchronise.phtml';
    }
}