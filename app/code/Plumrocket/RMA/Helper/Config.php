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

namespace Plumrocket\RMA\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;

class Config extends Main
{
    /**
     * File size service.
     *
     * @var Size
     */
    protected $fileSizeService;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Context                $context
     * @param Size                   $fileSize
     * @param Filesystem             $filesystem
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Context $context,
        Size $fileSize,
        Filesystem $filesystem
    ) {
        $this->fileSizeService = $fileSize;
        $this->filesystem = $filesystem;
        parent::__construct($objectManager, $context);

        $this->_configSectionId = Data::SECTION_ID;
    }

    /**
     * Get return information positions
     *
     * @return array
     */
    public function getReturnPositions()
    {
        return explode(',', $this->getConfig(
            $this->_configSectionId . '/general/return_placement'
        ));
    }

    /**
     * Get store address
     *
     * @return string
     */
    public function getStoreAddress()
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/general/store_address'
        ));
    }

    /**
     * Get auto clode period
     *
     * @return int
     */
    public function getAutoClose()
    {
        return (int)$this->getConfig(
            $this->_configSectionId . '/general/auto_close'
        );
    }

    /**
     * Retrieve if tracking number is enabled for customer
     *
     * @return bool
     */
    public function enabledTrackingNumber()
    {
        return (bool)$this->getConfig(
            $this->_configSectionId . '/general/tracking_number'
        );
    }

    /**
     * Get shipping carriers list
     *
     * @return array
     */
    public function getShippingCarriers()
    {
        $list = explode(',', $this->getConfig(
            $this->_configSectionId . '/general/shipping_carriers'
        ));

        array_walk($list, function (&$value) {
            $value = trim($value);
        });

        return array_combine($list, $list);
    }

    /**
     * Get count of shipping label files
     *
     * @return int
     */
    public function getShippingLabelCount()
    {
        return 1;
    }

    /**
     * Check possibility to creating return on frontend
     *
     * @return bool
     */
    public function allowCreateOnFrontend()
    {
        return (bool)$this->getConfig(
            $this->_configSectionId . '/newrma/allow'
        );
    }

    /**
     * Get default manager id for new return
     *
     * @return int
     */
    public function getDefaultManagerId()
    {
        return (int)$this->getConfig(
            $this->_configSectionId . '/newrma/default_manager'
        );
    }

    /**
     * Check is required return policy
     *
     * @return bool
     */
    public function enabledReturnPolicy()
    {
        return (bool)$this->getConfig(
            $this->_configSectionId . '/newrma/return_policy'
        );
    }

    /**
     * Get policy block id
     *
     * @return string
     */
    public function getReturnPolicyBlock()
    {
        return $this->getConfig(
            $this->_configSectionId . '/newrma/return_policy_block'
        );
    }

    /**
     * Check if can to authorize items
     *
     * @return bool
     */
    public function canAutoAuthorize()
    {
        return (bool)$this->getConfig(
            $this->_configSectionId . '/newrma/auto_authorize'
        );
    }

    /**
     * Get return success message block id
     *
     * @return string
     */
    public function getReturnSuccessBlock()
    {
        return $this->getConfig(
            $this->_configSectionId . '/newrma/return_success'
        );
    }

    /**
     * Get return instruction message block id
     *
     * @return string
     */
    public function getReturnInstructionsBlock()
    {
        return $this->getConfig(
            $this->_configSectionId . '/newrma/return_instructions'
        );
    }

    /**
     * Get sender name
     *
     * @return string
     */
    public function getSenderName($store = null)
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/email/sender_name',
            $store
        ));
    }

    /**
     * Get sender email
     *
     * @return string
     */
    public function getSenderEmail($store = null)
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/email/sender_email',
            $store
        ));
    }

    /**
     * Get new email tempate to customer
     *
     * @return string
     */
    public function getCustomerNewTemplate($store = null)
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/email/email_customer/new_template',
            $store
        ));
    }

    /**
     * Get additional addresses for new email to customer
     *
     * @return array
     */
    public function getCustomerNewEmails($store = null)
    {
        $emails = explode(',', $this->getConfig(
            $this->_configSectionId . '/email/email_customer/new_copy',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get update email tempate to customer
     *
     * @return string
     */
    public function getCustomerUpdateTemplate($store = null)
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/email/email_customer/update_template',
            $store
        ));
    }

    /**
     * Get additional addresses for update email to customer
     *
     * @return array
     */
    public function getCustomerUpdateEmails($store = null)
    {
        $emails = explode(',', $this->getConfig(
            $this->_configSectionId . '/email/email_customer/update_copy',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get new email tempate to manager
     *
     * @return string
     */
    public function getManagerNewTemplate($store = null)
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/email/email_manager/new_template',
            $store
        ));
    }

    /**
     * Get additional addresses for new email to manager
     *
     * @return array
     */
    public function getManagerNewEmails($store = null)
    {
        $emails = explode(',', $this->getConfig(
            $this->_configSectionId . '/email/email_manager/new_copy',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get update email tempate to manager
     *
     * @return string
     */
    public function getManagerUpdateTemplate($store = null)
    {
        return trim($this->getConfig(
            $this->_configSectionId . '/email/email_manager/update_template',
            $store
        ));
    }

    /**
     * Get additional addresses for update email to manager
     *
     * @return array
     */
    public function getManagerUpdateEmails($store = null)
    {
        $emails = explode(',', $this->getConfig(
            $this->_configSectionId . '/email/email_manager/update_copy',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get allowed file types
     *
     * @return array
     */
    public function getFileAllowedExtensions()
    {
        $types = explode(',', $this->getConfig(
            $this->_configSectionId . '/file/type'
        ));

        array_walk($types, function (&$value) {
            $value = trim($value);
        });

        return $types;
    }

    /**
     * Get max size of file
     *
     * @return int
     */
    public function getFileMaxSize($inBytes = false)
    {
        $size = (int)$this->getConfig($this->_configSectionId . '/file/size');
        $size *= 1024 * 1024;
        $size = min($size, $this->fileSizeService->getMaxFileSize());

        if (! $inBytes) {
            // $size /= (1024 * 1024);
            $size = $this->fileSizeService->getFileSizeInMb($size);
        }

        return $size;
    }

    /**
     * Get max count of files
     *
     * @return int
     */
    public function getFileMaxCount()
    {
        $count = (int)$this->getConfig($this->_configSectionId . '/file/count');
        return max(1, $count);
    }

    /**
     * Filesystem directory path of temporary files
     *
     * @param bool $full
     * @return string
     */
    public function getBaseTmpMediaPath($full = true)
    {
        $path = 'tmp/prrma';
        if ($full) {
            $path = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath($path);
        }
        return $path;
    }

    /**
     * Filesystem directory path of stable files
     *
     * @param bool $full
     * @return string
     */
    public function getBaseMediaPath($full = true)
    {
        $path = 'prrma';
        if ($full) {
            $path = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath($path);
        }
        return $path;
    }
}
