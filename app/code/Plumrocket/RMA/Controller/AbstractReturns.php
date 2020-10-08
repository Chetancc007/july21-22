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

namespace Plumrocket\RMA\Controller;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Helper\Guest;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem\DriverInterface;
use Plumrocket\RMA\Helper\Config;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\File;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Returns\AddressFactory;
use Plumrocket\RMA\Model\Returns\EmailFactory;
use Plumrocket\RMA\Model\Returns\ItemFactory;
use Plumrocket\RMA\Model\Returns\ValidatorFactory;
use Plumrocket\RMA\Model\ReturnsFactory;

abstract class AbstractReturns extends Action
{
    /**
     * @var ReturnsFactory
     */
    protected $returnsFactory;

    /**
     * Returns model
     *
     * @var Returns
     */
    protected $returnsModel = null;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

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
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Guest
     */
    protected $guestHelper;

    /**
     * Guest order
     *
     * @var Order
     */
    protected $guestOrder = null;

    /**
     * @var OrderConfig
     */
    protected $orderFactory;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var OrderConfig
     */
    protected $orderConfig;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Enable checking of can return
     *
     * @var bool
     */
    protected $canReturnControl = true;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var DriverInterface
     */
    protected $fileDriver;

    /**
     * AbstractReturns constructor.
     *
     * @param Context $context
     * @param ReturnsFactory $returnsFactory
     * @param ValidatorFactory $validatorFactory
     * @param Data $dataHelper
     * @param Config $configHelper
     * @param File $fileHelper
     * @param ReturnsHelper $returnsHelper
     * @param ItemHelper $itemHelper
     * @param ItemFactory $itemFactory
     * @param EmailFactory $emailFactory
     * @param Registry $registry
     * @param Session $session
     * @param Guest $guestHelper
     * @param OrderFactory $orderFactory
     * @param OrderItemFactory $orderItemFactory
     * @param OrderConfig $orderConfig
     * @param AddressFactory $addressFactory
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param Url $customerUrl
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param DriverInterface $fileDriver
     */
    public function __construct(
        Context $context,
        ReturnsFactory $returnsFactory,
        ValidatorFactory $validatorFactory,
        Data $dataHelper,
        Config $configHelper,
        File $fileHelper,
        ReturnsHelper $returnsHelper,
        ItemHelper $itemHelper,
        ItemFactory $itemFactory,
        EmailFactory $emailFactory,
        Registry $registry,
        Session $session,
        Guest $guestHelper,
        OrderFactory $orderFactory,
        OrderItemFactory $orderItemFactory,
        OrderConfig $orderConfig,
        AddressFactory $addressFactory,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        Url $customerUrl,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        DriverInterface $fileDriver
    ) {
        $this->returnsFactory = $returnsFactory;
        $this->validatorFactory = $validatorFactory;
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->fileHelper = $fileHelper;
        $this->returnsHelper = $returnsHelper;
        $this->itemHelper = $itemHelper;
        $this->itemFactory = $itemFactory;
        $this->emailFactory = $emailFactory;
        $this->registry = $registry;
        $this->session = $session;
        $this->guestHelper = $guestHelper;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderConfig = $orderConfig;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->customerUrl = $customerUrl;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileDriver = $fileDriver;

        parent::__construct($context);
    }

    /**
     * Retrieve returns model instance
     *
     * @return Returns
     */
    public function getModel()
    {
        if (null === $this->returnsModel) {
            $this->returnsModel = $this->returnsFactory->create();
            if (! $id = $this->getRequest()->getParam('id')) {
                $id = $this->getRequest()->getParam('entity_id');
            }
            if (is_numeric($id) && $id > 0) {
                $this->returnsModel->load($id);
            }
        }
        return $this->returnsModel;
    }

    /**
     * Check access and display the appropriate result
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $result = $this->checkAccess();
        if (null !== $result) {
            return $result;
        }

        return parent::dispatch($request);
    }

    /**
     * Check access
     *
     * @return ResponseInterface|null
     */
    public function checkAccess()
    {
        // If customer or guest is missed, show login form.
        if (! $this->getCustomer()
            && ! $this->getGuestOrder()
            && ! $this->specialAccess()
        ) {
            if (! $this->getSession()->authenticate()) {
                $this->_actionFlag->set('', 'no-dispatch', true);
                return null;
            }
        }

        // Otherwise, if entry isn't exists or isn't allow.
        if (! $this->canViewReturn() && ! $this->canViewOrder()) {
            return $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT)
                ->setUrl($this->customerUrl->getDashboardUrl());
        }

        return null;
    }

    /**
     * Check if current return exists and is allowed for current customer/guest
     *
     * @return bool
     */
    public function canViewReturn()
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return false;
        }

        $model = $this->getModel();
        $request = $this->getRequest();
        $availableStatuses = $this->orderConfig->getVisibleOnFrontStatuses();

        if ($model->getId() > 0) {
            // Entry exists, check if customer or guest made it.
            $currentOrder = $model->getOrder();
            if ($customer = $this->getCustomer()) {
                if ($customer->getId() > 0
                    && $currentOrder
                    && $currentOrder->getCustomerId() == $customer->getId()
                    && in_array($currentOrder->getStatus(), $availableStatuses, true)
                ) {
                    return true;
                }
            } elseif ($order = $this->getGuestOrder()) {
                if ($currentOrder
                    && $currentOrder->getId() == $order->getId()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if order exists and is allowed for current customer/guest
     * Also it checks if order can be returned
     *
     * @return bool
     */
    public function canViewOrder()
    {
        if (! $this->dataHelper->moduleEnabled()) {
            return false;
        }

        $model = $this->getModel();
        $request = $this->getRequest();
        $availableStatuses = $this->orderConfig->getVisibleOnFrontStatuses();

        if (! $model->getId()) {
            // Entry will creating, check if order is allowed.
            $currentOrder = null;
            $canReturn = false;
            if ($orderId = $request->getParam('order_id')) {
                $currentOrder = $this->orderFactory->create()->load($orderId);
                if ($this->canReturnControl) {
                    $canReturn = $this->returnsHelper->canReturnCustomer($currentOrder);
                } else {
                    $canReturn = true;
                }
            }

            if ($customer = $this->getCustomer()) {
                if ($customer->getId() > 0
                    && $currentOrder
                    && $currentOrder->getCustomerId() == $customer->getId()
                    && in_array($currentOrder->getStatus(), $availableStatuses, true)
                    && $canReturn
                ) {
                    return true;
                }
            } elseif ($order = $this->getGuestOrder()) {
                if ($currentOrder
                    && $currentOrder->getId() == $order->getId()
                    && $canReturn
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * Get current customer
     *
     * @return Customer|bool
     */
    public function getCustomer()
    {
        if ($this->getSession()->isLoggedIn()) {
            return $this->getSession()->getCustomer();
        }

        return false;
    }

    /**
     * Get order of current guest
     *
     * @return Order|bool
     */
    public function getGuestOrder()
    {
        if (null === $this->guestOrder) {
            $this->guestOrder = false;

            // Remove current order if exists.
            $this->registry->unregister('current_order');
            $this->registry->unregister(Data::SECTION_ID . '_guest_mode');
            // Try to load order for guest.
            $result = $this->guestHelper->loadValidOrder($this->getRequest());
            if (true === $result) {
                // If loaded, return order.
                $order = $this->registry->registry('current_order');
                if ($order && $order->getId()) {
                    $this->guestOrder = $order;
                    $this->registry->register(Data::SECTION_ID . '_guest_mode', true);
                }
            }
        }

        return $this->guestOrder;
    }

    /**
     * Check if current action allows a special access
     *
     * @return boolean
     */
    public function specialAccess()
    {
        return false;
    }

    /**
     * Prepare returns pages
     *
     * @param  Page   $resultPage
     * @param  array  $arguments
     * @return void
     */
    public function preparePage(Page $resultPage, array $arguments = [])
    {
        // Change page for guest. It need to do before getting blocks.
        if (! $this->getCustomer()
            && $this->getGuestOrder()
        ) {
            $this->prepareGuestPage($resultPage, $arguments);
        }

        if (isset($arguments['title'])) {
            $resultPage->getConfig()->getTitle()->set($arguments['title']);
        }

        // Select menu item.
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            if (empty($arguments['active'])) {
                $arguments['active'] = 'prrma/returns';
            }
            $navigationBlock->setActive($arguments['active']);
        }

        // Back link.
        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
    }

    /**
     * Prepare page for guest
     *
     * @param  Page   $resultPage
     * @param  array  $arguments
     * @return void
     */
    public function prepareGuestPage(Page $resultPage, array $arguments = [])
    {
        $resultPage->addHandle('prrma_returns_guest');

        // Add breadcrumbs.
        if (empty($arguments['breadcrumbs'])
            || (isset($arguments['breadcrumbs']) && false !== $arguments['breadcrumbs'])
        ) {
            if (empty($arguments['breadcrumbs'])) {
                $arguments['breadcrumbs'] = [];
                if (isset($arguments['title'])) {
                    $key = str_replace(' ', '_', mb_strtolower($arguments['title']));
                    $arguments['breadcrumbs'][$key] = [
                        'label' => $arguments['title'],
                        'title' => $arguments['title'],
                    ];
                }
            }

            $this->getBreadcrumbs($resultPage, $arguments['breadcrumbs']);
        }
    }

    /**
     * Get Breadcrumbs for controller action
     *
     * @param Page $resultPage
     * @return void
     */
    public function getBreadcrumbs(Page $resultPage, $crumbs = [])
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if (! $breadcrumbs) {
            return;
        }

        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->storeManager->getStore()->getBaseUrl()
            ]
        );
        $breadcrumbs->addCrumb(
            Data::SECTION_ID,
            [
                'label' => __('RMA'),
                'title' => __('RMA'),
                'link'  => $this->getCustomer() ? $this->_url->getUrl('prrma/returns') : null
            ]
        );

        foreach ($crumbs as $key => $data) {
            $breadcrumbs->addCrumb($key, $data);
        }
    }
}
