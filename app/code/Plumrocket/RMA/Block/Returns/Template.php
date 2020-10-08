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

namespace Plumrocket\RMA\Block\Returns;

use Plumrocket\RMA\Block\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Template extends \Magento\Framework\View\Element\Template
{
    use TemplateTrait;

    /**
     * @return string
     */
    public function getActionUrl()
    {
        if ($this->isNewEntity()) {
            return $this->getUrl('*/*/createPost');
        } else {
            return $this->getUrl('*/*/save');
        }
    }

    /**
     * @return string
     */
    public function getRememberFormUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/rememberForm');
    }

    /**
     * Get cms static block html
     *
     * @param  int $id
     * @return string
     */
    public function getCmsBlockHtml($id)
    {
        return $this->getLayout()
            ->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($id)
            ->toHtml();
    }
}
