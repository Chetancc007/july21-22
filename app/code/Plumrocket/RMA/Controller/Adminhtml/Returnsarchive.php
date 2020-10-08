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

namespace Plumrocket\RMA\Controller\Adminhtml;

class Returnsarchive extends \Plumrocket\Base\Controller\Adminhtml\Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::returnsarchive';

    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_returnsarchive_form_data';

    /**
     * Model of main class
     *
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Returns';

    /**
     * Actibe menu
     *
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::returnsarchive';

    /**
     * Object Title
     *
     * @var string
     */
    protected $_objectTitle     = 'Return';

    /**
     * Object titles
     *
     * @var string
     */
    protected $_objectTitles    = 'Returns Archive';

    /**
     * Status field
     *
     * @var string
     */
    protected $_statusField     = 'status';
}
