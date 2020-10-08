<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Magento\Framework\File\Mime;

class File extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->getModel();

        $storage = $this->getRequest()->getParam('storage');
        if (! $model->getId() || ! is_string($storage) || ! trim($storage)) {
            $this->_forward('noroute');
            return;
        }

        // Need to use basename, because path can contain ".." to navigate to any site files
        $name = $this->fileHelper->basename($storage);

        $fileFullPath = $this->configHelper->getBaseMediaPath() . DIRECTORY_SEPARATOR
            . $model->getId() . DIRECTORY_SEPARATOR
            . $name;
        $resultRaw = $this->resultRawFactory->create();

        try {
            $contentType = (new Mime())->getMimeType($fileFullPath);
            $resultRaw->setHeader('Content-Disposition', 'inline; filename="' . $name . '"', true)
                ->setHeader('Content-Type', $contentType);
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }

        return $resultRaw->setContents($this->fileDriver->fileGetContents($fileFullPath));
    }

    /**
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        if ($this->specialAccess()) {
            return true;
        }

        return parent::canViewReturn();
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client cannot have separate order on this page
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function specialAccess()
    {
        // Access by code for admin.
        $model = $this->getModel();
        $request = $this->getRequest();
        $code = $this->returnsHelper->getCode($model, ReturnsHelper::CODE_SALT_FILE);
        if ($request->getParam('code')
            && $request->getParam('code') === $code
        ) {
            return true;
        }

        return false;
    }
}
