<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model;

use Magento\Catalog\Model\ImageUploader;

class ImageUploaderPlugin
{
    public function beforeMoveFileFromTmp(ImageUploader $subject, $path, $returnRelativePath = false): array
    {
        $posLastSlash = strripos($path, '/');
        $path = $posLastSlash && strpos($path, '/category/') !== false ? substr($path, $posLastSlash + 1) : $path;

        return [$path, $returnRelativePath];
    }
}
