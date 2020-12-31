<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


namespace Amasty\Finder\Model\Finder\Session;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Filesystem;

class Config extends \Magento\Framework\Session\Config
{
    const AUTO_DETECT_ENDINGS = 'auto_detect_line_endings';

    public function __construct(
        \Magento\Framework\ValidatorFactory $validatorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        Filesystem $filesystem,
        DeploymentConfig $deploymentConfig,
        $scopeType,
        $lifetimePath = \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME
    ) {
        parent::__construct(
            $validatorFactory,
            $scopeConfig,
            $stringHelper,
            $request,
            $filesystem,
            $deploymentConfig,
            $scopeType,
            $lifetimePath
        );
        $this->options[self::AUTO_DETECT_ENDINGS] = true;
    }
}
