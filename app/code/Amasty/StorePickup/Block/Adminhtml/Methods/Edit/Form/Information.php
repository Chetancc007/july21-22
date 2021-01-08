<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Block\Adminhtml\Methods\Edit\Form;

use Amasty\StorePickup\Block\Adminhtml\System\Config\Form\Information as ConfigInformation;
use Magento\Backend\Block\Template;

class Information extends Template
{
    /**
     * @var ConfigInformation
     */
    private $information;

    public function __construct(
        ConfigInformation $information,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->information = $information;
    }

    public function _toHtml()
    {
        return '<div><span class="message message-info info">' . $this->getNoticeText() . '</span></br></div>';
    }

    private function getNoticeText()
    {
        return __(
            'Need help with the settings?'
            . '  Please  consult the <a target="_blank" href="%1">user guide</a>'
            . ' to configure the extension properly.',
            $this->information->getUserGuideLink()
        );
    }
}
