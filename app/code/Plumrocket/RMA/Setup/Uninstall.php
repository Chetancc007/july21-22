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

namespace Plumrocket\RMA\Setup;

class Uninstall extends \Plumrocket\Base\Setup\AbstractUninstall
{
    /**
     * Config section id
     *
     * @var string
     */
    protected $_configSectionId = 'prrma';

    /**
     * Pathes to files
     *
     * @var Array
     */
    protected $_pathes = ['/app/code/Plumrocket/RMA'];

    /**
     * Attributes
     *
     * @var Array
     */
    protected $_attributes = [];

    /**
     * Tables
     *
     * @var Array
     */
    protected $_tables = [
        'plumrocket_rma_condition',
        'plumrocket_rma_reason',
        'plumrocket_rma_resolution',
        'plumrocket_rma_response_template',
        'plumrocket_rma_return_rule',
        'plumrocket_rma_returns',
        'plumrocket_rma_returns_address',
        'plumrocket_rma_returns_item',
        'plumrocket_rma_returns_message',
        'plumrocket_rma_returns_track',
        'plumrocket_rma_store',
        'plumrocket_rma_text',
    ];
}
