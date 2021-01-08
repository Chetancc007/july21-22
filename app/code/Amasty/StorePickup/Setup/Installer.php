<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Amasty\StorePickup\Model\Sample
     */
    private $sample;

    public function __construct(\Amasty\StorePickup\Model\Sample $sample)
    {
        $this->sample = $sample;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->sample->install();
    }
}
