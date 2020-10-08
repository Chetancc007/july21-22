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

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

class Index extends \Plumrocket\RMA\Controller\Adminhtml\Returns
{
    public function execute()
    {
        $currentDefaultManager = $this->configHelper->getDefaultManagerId();
        $user = $this->userFactory->create()->load($currentDefaultManager);
        if (null === $user->getId()) {
            $url = $this->_url->getUrl('adminhtml/system_config/edit', ['section' => 'prrma']);
            $url .= '#prrma_newrma_default_manager';
            $message = __('Default RMA Manager was removed from Magento store. Click <a href="%1">here</a> to set new manager for RMA extension.', $url);
            $this->messageManager->addError($message);
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = __('Manage '.$this->_objectTitles);
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }
}
