<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Plugin;

use Magento\Config\Block\System\Config\Tabs;
use Magento\Config\Model\Config\Structure\Element\Section;

class RedirectToShipping
{
    public function aroundGetSectionUrl(
        Tabs $subject,
        callable $proceed,
        Section $section
    ) {
        $url = $proceed($section);
        if ($section->getId() === 'amstorepick_amasty_tab') {
            $url = $subject->getUrl('*/*/*', ['_current' => true, 'section' => 'carriers']);
        }

        return $url;
    }
}
