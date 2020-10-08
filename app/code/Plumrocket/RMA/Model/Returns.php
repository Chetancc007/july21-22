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
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\RMA\Model;

use Magento\Backend\Model\Auth;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Plumrocket\RMA\Api\Data\ReturnInterface;
use Plumrocket\RMA\Helper\Config as ConfigHelper;
use Plumrocket\RMA\Helper\Data as DataHelper;
use Plumrocket\RMA\Helper\File as FileHelper;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\AddressFactory;
use Plumrocket\RMA\Model\Returns\ItemFactory;
use Plumrocket\RMA\Model\Returns\Message;
use Plumrocket\RMA\Model\Returns\MessageFactory;
use Plumrocket\RMA\Model\Returns\TrackFactory;

class Returns extends AbstractModel implements ReturnInterface
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Entity order
     *
     * @var Order
     */
    protected $order = null;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var FileHelper
     */
    protected $fileHelper;

    /**
     * @var ReturnsHelper
     */
    protected $returnsHelper;

    /**
     * @var ReturnsStatus
     */
    protected $returnsStatus;

    /**
     * Entity address
     *
     * @var Address
     */
    protected $address = null;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Items collection
     *
     * @var Item[]
     */
    protected $items = null;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * Tracks collection
     *
     * @var Track[]
     */
    protected $tracks = null;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * Messages collection
     *
     * @var Message[]
     */
    protected $messages = null;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * Backend auth
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Backend user factory
     *
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * Entity user
     *
     * @var \Magento\User\Model\User
     */
    protected $user = null;

    /**
     * Customer session
     *
     * @var Session
     */
    protected $session;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context               $context
     * @param Registry              $registry
     * @param DataHelper            $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param OrderFactory          $orderFactory
     * @param AddressFactory        $addressFactory
     * @param ConfigHelper          $configHelper
     * @param FileHelper            $fileHelper
     * @param ReturnsHelper         $returnsHelper
     * @param ReturnsStatus         $returnsStatus
     * @param ItemFactory           $itemFactory
     * @param TrackFactory          $trackFactory
     * @param MessageFactory        $messageFactory
     * @param ScopeConfigInterface  $scopeConfig
     * @param TransportBuilder      $transportBuilder
     * @param StateInterface        $inlineTranslation
     * @param Auth                  $auth
     * @param UserFactory           $userFactory
     * @param Session               $session
     * @param DateTime              $dateTime
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DataHelper $dataHelper,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        AddressFactory $addressFactory,
        ConfigHelper $configHelper,
        FileHelper $fileHelper,
        ReturnsHelper $returnsHelper,
        ReturnsStatus $returnsStatus,
        ItemFactory $itemFactory,
        TrackFactory $trackFactory,
        MessageFactory $messageFactory,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Auth $auth,
        UserFactory $userFactory,
        Session $session,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
        $this->configHelper = $configHelper;
        $this->fileHelper = $fileHelper;
        $this->returnsHelper = $returnsHelper;
        $this->returnsStatus = $returnsStatus;
        $this->itemFactory = $itemFactory;
        $this->trackFactory = $trackFactory;
        $this->messageFactory = $messageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->auth = $auth;
        $this->userFactory = $userFactory;
        $this->session = $session;
        $this->dateTime = $dateTime;
        parent::__construct(
            $context,
            $registry,
            $dataHelper,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Plumrocket\RMA\Model\ResourceModel\Returns');
    }

    /**
     * Retrieve status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->returnsStatus->getByKey($this->getStatus());
    }

    /**
     * @inheritDoc
     */
    public function getIncrementId(): int
    {
        return (int) $this->getData(self::INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): int
    {
        return (int) $this->getData(self::IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getManagerId(): int
    {
        return (int) $this->getData(self::MANAGER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getIsClosed(): bool
    {
        return (bool) $this->getData(self::IS_CLOSED);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return (string) $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getShippingLabel(): string
    {
        return (string) $this->getData(self::SHIPPING_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function getNote(): string
    {
        return (string) $this->getData(self::NOTE);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return (string) $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function getCeatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setIncrementId(int $incrementId): ReturnInterface
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(int $id): ReturnInterface
    {
        $this->setData(self::IDENTIFIER, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(int $id): ReturnInterface
    {
        $this->setData(self::ORDER_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setManagerId(int $id): ReturnInterface
    {
        $this->setData(self::MANAGER_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIsClosed(bool $flag): ReturnInterface
    {
        $this->setData(self::IS_CLOSED);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): ReturnInterface
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setShippingLabel(string $label): ReturnInterface
    {
        $this->setData(self::SHIPPING_LABEL, $label);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setNote(string $note): ReturnInterface
    {
        $this->setData(self::NOTE, $note);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCode(string $code): ReturnInterface
    {
        $this->setData(self::CODE, $code);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCeatedAt(string $date): ReturnInterface
    {
        $this->setData(self::CREATED_AT, $date);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $date): ReturnInterface
    {
        $this->setData(self::UPDATED_AT);
        return $this;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        if (null !== $this->getData('name')) {
            return $this->getData('name');
        }

        return __('RMA #%1 (%2)', $this->getIncrementId(), $this->getStatusLabel());
    }

    /**
     * Check if return is closed
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->getIsClosed();
    }

    /**
     * Get manager name by admin user
     *
     * @return string
     */
    public function getManagerName()
    {
        if (null === $this->user && $this->getManagerId()) {
            $this->manager = $this->userFactory->create()
                ->load($this->getManagerId());
        }

        return $this->manager ? $this->manager->getName() : '';
    }

    /**
     * Get manager email by admin user
     *
     * @return string
     */
    public function getManagerEmail()
    {
        if (null === $this->user && $this->getManagerId()) {
            $this->manager = $this->userFactory->create()
                ->load($this->getManagerId());
        }

        return $this->manager ? $this->manager->getEmail() : '';
    }

    /**
     * Get entity order
     *
     * @return Order|null
     */
    public function getOrder()
    {
        if (null === $this->order && $this->getOrderId()) {
            $this->order = $this->orderFactory
                ->create()
                ->load($this->getOrderId());
        }

        return $this->order;
    }

    /**
     * Get return items collection
     *
     * @return \Plumrocket\RMA\Model\ResourceModel\Returns\Item\Collection
     */
    public function getItemsCollection()
    {
        return $this->itemFactory->create()
            ->getCollection()
            ->addReturnsFilter($this->getId())
            ->setOrder('order_item_id', 'asc')
            ->setOrder('entity_id', 'asc');
    }

    /**
     * Get return items
     *
     * @return \Plumrocket\RMA\Model\Returns\Item[]
     */
    public function getItems()
    {
        if (null === $this->items) {
            $this->items = $this->getItemsCollection()->getItems();
            foreach ($this->items as $item) {
                $item->setReturns($this);
            }
        }

        return $this->items;
    }

    /**
     * Set return items
     *
     * @param mixed $items
     * @return $this
     */
    public function setItems($items): ReturnInterface
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Get entity address
     *
     * @return Address
     */
    public function getAddress()
    {
        if (null === $this->address) {
            $this->address = $this->addressFactory
                ->create()
                ->load($this->getId(), 'parent_id');
        }

        return $this->address;
    }

    /**
     * Retrieve tracks collection of return
     *
     * @return \Plumrocket\RMA\Model\ResourceModel\Returns\Track\Collection
     */
    public function getTracksCollection()
    {
        return $this->trackFactory->create()
            ->getCollection()
            ->addReturnsFilter($this->getId())
            ->setOrder('entity_id', 'asc');
    }

    /**
     * Retrieve tracks of return
     *
     * @return \Plumrocket\RMA\Model\Returns\Track[]
     */
    public function getTracks()
    {
        if (null === $this->tracks) {
            $this->tracks = $this->getTracksCollection()->getItems();
        }

        return $this->tracks;
    }

    /**
     * Get return track
     *
     * @param string|int $trackId
     * @return bool|\Plumrocket\RMA\Model\Returns\Track
     */
    public function getTrackById($trackId)
    {
        foreach ($this->getTracks() as $track) {
            if ($trackId == $track->getId()) {
                return $track;
            }
        }

        return false;
    }

    /**
     * Add new track
     *
     * @param string  $from
     * @param string  $carrier
     * @param string  $number
     * @return bool|\Plumrocket\RMA\Model\Returns\Track
     */
    public function addTrack($from, $carrier, $number)
    {
        if (! is_string($carrier) || ! $carrier = trim($carrier)) {
            return false;
        }

        if (! is_string($number) || ! $number = trim($number)) {
            return false;
        }

        if (! $this->getId()) {
            return false;
        }

        $track = $this->trackFactory
            ->create()
            ->setReturns($this)
            ->setParentId($this->getId())
            ->setType($from)
            ->setCarrierCode($carrier)
            ->setTrackNumber($number)
            ->save();

        return $track;
    }

    /**
     * Get return messages collection
     *
     * @return \Plumrocket\RMA\Model\ResourceModel\Returns\Message\Collection
     */
    public function getMessagesCollection()
    {
        return $this->messageFactory
            ->create()
            ->getCollection()
            ->addReturnsFilter($this->getId())
            ->setOrder('entity_id', 'desc');
    }

    /**
     * Get return messages
     *
     * @return Message[]
     */
    public function getMessages()
    {
        if (null === $this->messages) {
            $this->messages = $this->getMessagesCollection()->getItems();
        }

        return $this->messages;
    }

    /**
     * Add new message.
     *
     * @param string  $from
     * @param string  $text
     * @param array   $files
     * @param boolean $isSystem
     * @param boolean $isInternal
     * @return Message|bool
     */
    public function addMessage(
        $from,
        $text,
        $files = null,
        $isSystem = false,
        $isInternal = false
    ) {
        $text = trim($text);
        if (! $text && ! $files) {
            return false;
        }

        if (! $this->getId()) {
            return false;
        }

        $data = [
            'parent_id' => $this->getId(),
            'type' => $from,
            'text' => $text,
            'is_system' => (bool)$isSystem,
            'is_internal' => (bool)$isInternal,
        ];

        switch ($from) {
            case Message::FROM_MANAGER:
                $user = $this->auth->getUser();
                $data['from_id'] = $user->getId();
                $data['name'] = $user->getName();
                break;

            case Message::FROM_CUSTOMER:
                $order = $this->getOrder();
                if ($this->session->isLoggedIn()) {
                    $customer = $this->session->getCustomer();
                    $data['from_id'] = $customer->getId();
                    $data['name'] = $customer->getName();
                } elseif ($order && $order->getId()) {
                    $data['from_id'] = $order->getCustomerId();
                    $data['name'] = $order->getCustomerName();
                }
                break;

            case Message::FROM_SYSTEM:
                $data['name'] = __('RMA');
                $data['is_system'] = true;
                break;

            default:
                return false;
        }

        $files = $this->fileHelper
            ->setAdditionalPath($this->getId())
            ->takeMessageFiles($files);

        if ($files) {
            $data['files'] = json_encode($files);
        }

        $message = $this->messageFactory
            ->create()
            ->setData($data)
            ->save();

        // Mark as read.
        if (Message::FROM_MANAGER === $from
            && $message
            && $message->getId()
            && ! $isSystem
        ) {
            $this->setReadMarkAt($this->dateTime->gmtDate())->save();
        }

        return $message;
    }

    /**
     * Check if return is virtual
     *
     * @return boolean
     */
    public function isVirtual()
    {
        return $this->returnsHelper->isVirtual($this);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ($this->isObjectNew()) {
            $this->setCode($this->returnsHelper->generateCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        parent::afterSave();

        // Generate increment id if is not exists.
        if ($this->isObjectNew() && ! $this->getData('increment_id')) {
            $incrementId = str_pad($this->getId(), 9, '0', STR_PAD_LEFT);
            $this->setData('increment_id', $incrementId)->save();
        }

        return $this;
    }

    /**
     * Send rma email
     *
     * @param  string $template
     * @param  string|array $email
     * @param  array  $vars
     * @return bool
     */
    public function sendEmail($template, $email, $vars = [])
    {
        if (! $template || ! $email) {
            return false;
        }

        // Add current model to email template
        if (empty($vars['returns'])) {
            $vars['returns'] = $this;
        }

        // Add order
        if (empty($vars['order'])) {
            $vars['order'] = $this->getOrder();
        }

        // Add quick view link url
        if (empty($vars['view_url'])) {
            $vars['view_url'] = $this->returnsHelper->getQuickViewUrl($this);
        }

        $storeId = $this->getOrder()->getStoreId();

        $this->inlineTranslation->suspend();

        $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )->setTemplateVars(
            $vars
        )->setFrom(
            [
                'email' => $this->configHelper->getSenderEmail($storeId),
                'name' => $this->configHelper->getSenderName($storeId),
            ]
        )->addTo(
            $email,
            $this->storeManager->getStore()->getName()
        );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return true;
    }
}
