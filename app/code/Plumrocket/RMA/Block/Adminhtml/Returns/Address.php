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

use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

/**
 * Edit returns address form container block
 */
class Address extends \Magento\Backend\Block\Widget\Form\Container
{
    use TemplateTrait;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->templateTraitInit();
        $this->_controller = 'adminhtml_returns';
        $this->_mode = 'address';
        $this->_blockGroup = 'Plumrocket_RMA';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Return Address'));
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Edit Return Address');
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl()
    {
        $request = $this->getRequest();

        $params = [];
        if ($parentId = $request->getParam('parent_id')) {
            $params['id'] = $parentId;
        } elseif ($orderId = $request->getParam('order_id')) {
            $params['order_id'] = $orderId;
        }

        return $this->getUrl(Data::SECTION_ID . '/*/edit', $params);
    }
}
