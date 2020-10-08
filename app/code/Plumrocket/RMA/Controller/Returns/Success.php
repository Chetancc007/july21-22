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

namespace Plumrocket\RMA\Controller\Returns;

use Plumrocket\RMA\Controller\AbstractReturns;

class Success extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->getModel();
        $this->registry->register('current_model', $model);

        // Prepare page.
        $resultPage = $this->resultPageFactory->create();
        $this->preparePage($resultPage, [
            'title' => __('Return Has Been Created Successfully')
        ]);

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client cannot have separate order on this page
        return false;
    }
}
