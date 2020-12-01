<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Setup;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InstallData
 * @package Mageplaza\SaveCart\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Reader
     */
    protected $moduleReader;

    /**
     * InstallData constructor.
     *
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param Reader $moduleReader
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
        Reader $moduleReader
    ) {
        $this->filesystem   = $filesystem;
        $this->logger       = $logger;
        $this->moduleReader = $moduleReader;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->copyIconDefault();
    }

    /**
     * Copy icon default to media path
     */
    protected function copyIconDefault()
    {
        try {
            /** @var Filesystem\Directory\WriteInterface $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

            $mediaDirectory->create('mageplaza/savecart/default');
            $targetPath = $mediaDirectory->getAbsolutePath('mageplaza/savecart/default/icon.png');
            $viewDir    = $this->moduleReader->getModuleDir(
                Dir::MODULE_VIEW_DIR,
                'Mageplaza_SaveCart'
            );

            $oriPath = $viewDir . '/frontend/web/images/default/icon.png';
            $mediaDirectory->getDriver()->copy($oriPath, $targetPath);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
