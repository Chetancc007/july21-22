<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Model\Icon;

use Amasty\ProductAttachment\Model\Filesystem\UrlResolver;
use Magento\Framework\Filesystem\Io\File;

class GetIconForFile
{
    /**
     * @var ResourceModel\Icon
     */
    private $iconResource;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        ResourceModel\Icon $iconResource,
        UrlResolver $urlResolver,
        File $file
    ) {
        $this->iconResource = $iconResource;
        $this->urlResolver = $urlResolver;
        $this->file = $file;
    }

    //TODO FileStatInterface
    public function byFileName($filename)
    {
        if (!empty($filename)) {
            $extension = $this->file->getPathInfo($filename)['extension'] ?? '';
            if (!empty($extension) && $iconImage = $this->iconResource->getExtensionIconImage($extension)) {
                return $this->urlResolver->getIconUrlByName($iconImage);
            }
        }

        return false;
    }

    public function byFileExtension($ext)
    {
        if (!empty($ext)) {
            if ($iconImage = $this->iconResource->getExtensionIconImage($ext)) {
                return $this->urlResolver->getIconUrlByName($iconImage);
            }
        }

        return false;
    }
}
