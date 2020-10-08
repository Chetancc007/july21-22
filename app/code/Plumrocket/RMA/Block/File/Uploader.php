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

namespace Plumrocket\RMA\Block\File;

class Uploader extends \Magento\Framework\View\Element\Template
{
    /**
     * Name of form element
     */
    const FILE_FIELD_NAME = 'file';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_config;

    /**
     * Prepare layout
     *
     * @return \Magento\Backend\Block\Media\Uploader
     */
    protected function _prepareLayout()
    {
        $this->getConfig()->setUrl($this->getSubmitUrl());
        $this->getConfig()->setFileField(static::FILE_FIELD_NAME);

        return parent::_prepareLayout();
    }

    /**
     * Get submit url
     *
     * @return string|true
     */
    public function getSubmitUrl()
    {
        return $this->_urlBuilder->getUrl('*/*/upload');
    }

    /**
     * Get max file size
     *
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->getConfigHelper()->getFileMaxSize(true);
    }

    /**
     * Get max files count
     *
     * @return int
     */
    public function getMaxFilesCount()
    {
        return $this->getConfigHelper()->getFileMaxCount();
    }

    /**
     * File list for form autofill
     *
     * @return array
     */
    public function getFileList()
    {
        $files = $this->getDataHelper()->getFormData(static::FILE_FIELD_NAME);
        if (! is_array($files)) {
            $files = [];
        }

        return $files;
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $this->_config = new \Magento\Framework\DataObject();
        }
        return $this->_config;
    }
}
