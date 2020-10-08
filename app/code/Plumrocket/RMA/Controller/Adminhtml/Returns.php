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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Plumrocket\Base\Controller\Adminhtml\Actions;
use Plumrocket\RMA\Helper\Config;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\File;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Response as ResponseTemplate;
use Plumrocket\RMA\Model\Returns\AddressFactory;
use Plumrocket\RMA\Model\Returns\EmailFactory;
use Plumrocket\RMA\Model\Returns\ItemFactory;
use Plumrocket\RMA\Model\Returns\ValidatorFactory;
use Magento\User\Model\UserFactory;

class Returns extends Actions
{
    const ADMIN_RESOURCE = 'Plumrocket_RMA::returns';

    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey  = 'rma_returns_form_data';

    /**
     * Model of main class
     * @var string
     */
    protected $_modelClass      = 'Plumrocket\RMA\Model\Returns';

    /**
     * Actibe menu
     * @var string
     */
    protected $_activeMenu     = 'Plumrocket_RMA::returns';

    /**
     * Object Title
     * @var string
     */
    protected $_objectTitle     = 'Return';

    /**
     * Object titles
     * @var string
     */
    protected $_objectTitles    = 'Returns';

    /**
     * Status field
     * @var string
     */
    protected $_statusField     = 'status';

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var File
     */
    protected $fileHelper;

    /**
     * @var ReturnsHelper
     */
    protected $returnsHelper;

    /**
     * @var ItemHelper
     */
    protected $itemHelper;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var EmailFactory
     */
    protected $emailFactory;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * Quick Response Template
     *
     * @var ResponseTemplate
     */
    protected $responseTemplate;

    /**
     * @var ReturnsStatus
     */
    protected $returnsStatusSource;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @param Context          $context
     * @param Data             $dataHelper
     * @param Config           $configHelper
     * @param File             $fileHelper
     * @param ReturnsHelper    $returnsHelper
     * @param ItemHelper       $itemHelper
     * @param ItemFactory      $itemFactory
     * @param EmailFactory     $emailFactory
     * @param ValidatorFactory $validatorFactory
     * @param OrderFactory     $orderFactory
     * @param OrderItemFactory $orderItemFactory
     * @param AddressFactory   $addressFactory
     * @param ResponseTemplate $responseTemplate
     * @param ReturnsStatus    $returnsStatusSource
     * @param Registry         $coreRegistry
     * @param DateTime         $dateTime
     * @param PageFactory      $resultPageFactory
     * @param UserFactory      $userFactory
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Config $configHelper,
        File $fileHelper,
        ReturnsHelper $returnsHelper,
        ItemHelper $itemHelper,
        ItemFactory $itemFactory,
        EmailFactory $emailFactory,
        ValidatorFactory $validatorFactory,
        OrderFactory $orderFactory,
        OrderItemFactory $orderItemFactory,
        AddressFactory $addressFactory,
        ResponseTemplate $responseTemplate,
        ReturnsStatus $returnsStatusSource,
        Registry $coreRegistry,
        DateTime $dateTime,
        PageFactory $resultPageFactory,
        UserFactory $userFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->fileHelper = $fileHelper;
        $this->returnsHelper = $returnsHelper;
        $this->itemHelper = $itemHelper;
        $this->itemFactory = $itemFactory;
        $this->emailFactory = $emailFactory;
        $this->validatorFactory = $validatorFactory;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->addressFactory = $addressFactory;
        $this->responseTemplate = $responseTemplate;
        $this->returnsStatusSource = $returnsStatusSource;
        $this->coreRegistry = $coreRegistry;
        $this->dateTime = $dateTime;
        $this->resultPageFactory = $resultPageFactory;
        $this->userFactory = $userFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getModel($load = true)
    {
        parent::_getModel($load);
        if (!$this->_model->getEntityId()) {
            $id = (int)$this->getRequest()->getParam('entity_id');
            if ($id && $load) {
                $this->_model->load($id);
            }
        }

        return $this->_model;
    }
}
