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

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

use Magento\Backend\Block\Template as BackendTemplate;
use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Template extends BackendTemplate
{
    use TemplateTrait;

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/save');
    }

    /**
     * @return string
     */
    public function getRememberFormUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/rememberForm');
    }
}
