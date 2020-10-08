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

namespace Plumrocket\RMA\Block\Returns\Messages;

use Plumrocket\RMA\Block\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Uploader extends \Plumrocket\RMA\Block\File\Uploader
{
    use TemplateTrait;

    /**
     * Name of form element
     */
    const FILE_FIELD_NAME = 'comment_file';

    /**
     * {@inheritdoc}
     */
    public function getSubmitUrl()
    {
        $params = [];
        if ($this->isNewEntity()) {
            $params['order_id'] = $this->getOrder()->getId();
        } else {
            $params['id'] = $this->getEntity()->getId();
        }

        return $this->_urlBuilder->getUrl(
            Data::SECTION_ID . '/returns/messages_upload',
            $params
        );
    }
}
