<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.19
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Model\Config\Source\Rule as RuleSource;
use Mirasvit\Feed\Model\Config\Source\Template as TemplateSource;
use Mirasvit\Feed\Model\RuleFactory;
use Mirasvit\Feed\Model\TemplateFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var TemplateSource
     */
    private $templateSource;

    /**
     * @var RuleSource
     */
    private $ruleSource;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * InstallData constructor.
     * @param TemplateSource $templateSource
     * @param RuleSource $ruleSource
     * @param TemplateFactory $templateFactory
     * @param RuleFactory $ruleFactory
     * @param Config $config
     */
    public function __construct(
        TemplateSource $templateSource,
        RuleSource $ruleSource,
        TemplateFactory $templateFactory,
        RuleFactory $ruleFactory,
        Config $config
    ) {
        $this->templateSource  = $templateSource;
        $this->ruleSource      = $ruleSource;
        $this->templateFactory = $templateFactory;
        $this->ruleFactory     = $ruleFactory;
        $this->config          = $config;
    }

    /**
     * {@inheritdoc}
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $templatesPath = dirname(__FILE__) . '/data/template/';
        foreach (scandir($templatesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }


            $this->templateFactory->create()->import('Setup/data/template/' . $file);
        }

        $rulesPath = dirname(__FILE__) . '/data/rule/';
        foreach (scandir($rulesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }

            $this->ruleFactory->create()->import('Setup/data/rule/' . $file);
        }
    }
}
