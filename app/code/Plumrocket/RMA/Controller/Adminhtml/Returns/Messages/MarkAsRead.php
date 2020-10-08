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

namespace Plumrocket\RMA\Controller\Adminhtml\Returns\Messages;

use Plumrocket\RMA\Controller\Adminhtml\Returns;

class MarkAsRead extends Returns
{
    /**
     * Mark returns as readed
     *
     * @return json
     */
    public function execute()
    {
        $data = [];
        $id = $this->getRequest()->getParam('id');
        $time = $this->getRequest()->getParam('time');
        if (is_numeric($id) && is_numeric($time)) {
            $this->_getModel()
                ->setReadMarkAt($time)
                ->save();

            $data['success'] = true;
        }
        $this->getResponse()->setBody(json_encode($data));
    }
}
