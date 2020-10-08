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

namespace Plumrocket\RMA\Block\Returns;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\Factory as FactoryElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Plumrocket\RMA\Helper\Config;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returns;
use Plumrocket\RMA\Helper\Returns\Item;

trait TemplateTrait
{
    /**
     * Standard for most classes init method
     *
     * @return void
     */
    public function _construct()
    {
        if (method_exists(parent::class, '_construct')) {
            parent::_construct();
        }

        $this->templateTraitInit();
    }

    /**
     * Trait init method
     *
     * @return void
     */
    public function templateTraitInit()
    {
        if (empty($this->objectManager)) {
            $this->objectManager = ObjectManager::getInstance();
        }

        $this->registry = $this->objectManager->get(Registry::class);
        $this->factoryElement = $this->objectManager->get(FactoryElement::class);
        $this->formFactory = $this->objectManager->get(FormFactory::class);
        $this->dataHelper = $this->objectManager->get(Data::class);
        $this->configHelper = $this->objectManager->get(Config::class);
        $this->returnsHelper = $this->objectManager->get(Returns::class);
        $this->itemHelper = $this->objectManager->get(Item::class);
    }

    /**
     * Retrieve entity model
     *
     * @return \Plumrocket\RMA\Model\Returns
     */
    public function getEntity()
    {
        return $this->registry->registry('current_model');
    }

    /**
     * Retrieve order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (! $this->getEntity()->hasOrderId()) {
            $orderId = $this->getRequest()->getParam('order_id');
            $this->getEntity()->setOrderId($orderId);
        }

        $order = $this->getEntity()->getOrder();
        $this->setData('order', $order);

        return parent::getOrder();
    }

    /**
     * Get data helper
     *
     * @return Data
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    /**
     * Get config helper
     *
     * @return Config
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * Get returns helper
     *
     * @return Returns
     */
    public function getReturnsHelper()
    {
        return $this->returnsHelper;
    }

    /**
     * Check for new entity
     *
     * @return boolean
     */
    public function isNewEntity()
    {
        $isNew = false;
        if (! $this->getEntity()->getId()) {
            $isNew = true;
        }

        return $isNew;
    }

    /**
     * Check if is guest mode
     *
     * @return boolean
     */
    public function isGuestMode()
    {
        return (bool)$this->registry->registry(Data::SECTION_ID . '_guest_mode');
    }

    /**
     * Create form element
     *
     * @param  string $elementId
     * @param  string $type
     * @param  array $config
     * @param  \Magento\Framework\Data\Form|null $form
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function createElement($elementId, $type, $config, $form = null)
    {
        if (null === $form) {
            $form = $this->formFactory->create();
        }

        if (empty($config['title']) && ! empty($config['label'])) {
            $config['title'] = $config['label'];
        }

        $element = $this->factoryElement->create($type, ['data' => $config]);
        $element->setId($elementId);
        $element->setForm($form);
        return $element;
    }
}
