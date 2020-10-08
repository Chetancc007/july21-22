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

namespace Plumrocket\RMA\Controller\Adminhtml\Returnrule;

class Save extends \Plumrocket\RMA\Controller\Adminhtml\Returnrule
{
    /**
     * {@inheritdoc}
     */
    protected function _beforeSave($model, $request)
    {
        $data = $request->getParams();
        $data = $this->_prepareData($data);
        $model->loadPost($data);
        $model->setData($data);
    }

    /**
     * Prepare data
     * @param  array $data
     * @return array
     */
    protected function _prepareData($data)
    {
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        if (isset($data['rule']['actions'])) {
            $data['actions'] = $data['rule']['actions'];
        }
        unset($data['rule']);

        if (isset($data['website_id'])) {
            $data['website_id'] = implode(',', $data['website_id']);
        }

        if (isset($data['customer_group_id'])) {
            $data['customer_group_id'] = implode(',', $data['customer_group_id']);
        }

        if (isset($data['resolution'])) {
            $data['resolution'] = json_encode($data['resolution']);
        }

        return $data;
    }
}
