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

namespace Plumrocket\RMA\Block\Adminhtml\System\Config\Form;

class Version extends \Plumrocket\Base\Block\Adminhtml\System\Config\Form\Version
{
    /**
     * Wiki link
     * @var string
     */
    protected $_wikiLink = 'http://wiki.plumrocket.com/wiki/Magento_Returns_and_Exchanges_(RMA)_Extension_v2.x';

    /**
     * Full module name
     * @var string
     */
    protected $_moduleName = 'RMA';
}
