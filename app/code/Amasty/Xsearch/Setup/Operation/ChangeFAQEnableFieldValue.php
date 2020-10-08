<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


declare(strict_types=1);

namespace Amasty\Xsearch\Setup\Operation;

use Amasty\Xsearch\Model\System\Config\Source\Faq;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

class ChangeFAQEnableFieldValue
{
    const CONFIG_PATH = 'amasty_xsearch/faq/enabled';

    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        TypeListInterface $cacheTypeList
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        try {
            $faqEnableConfig = $this->loadConfig($setup);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        if (!empty($faqEnableConfig)) {
            foreach ($faqEnableConfig as $config) {
                if ($config['value'] === '2') {
                    $this->configWriter->save(
                        self::CONFIG_PATH,
                        Faq::DISABLED,
                        $config['scope'],
                        $config['scope_id']
                    );
                }
            }
        }

        $this->cacheTypeList->cleanType(ConfigCache::TYPE_IDENTIFIER);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return array
     */
    private function loadConfig(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select = $connection->select();
        $select->from($setup->getTable('core_config_data'));
        $select->where('path = ?', self::CONFIG_PATH);
        $select->columns(['scope_id', 'scope', 'path', 'value']);

        return $connection->fetchAll($select);
    }
}
