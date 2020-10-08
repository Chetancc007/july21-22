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

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    use TemplateTrait;

    /**
     * @var \Plumrocket\RMA\Model\Config\Source\AdminUser
     */
    protected $adminUserSource;

    /**
     * @param \Magento\Backend\Block\Template\Context         $context
     * @param \Magento\Framework\Registry                     $registry
     * @param \Magento\Sales\Helper\Admin                     $adminHelper
     * @param \Magento\Customer\Api\GroupRepositoryInterface  $groupRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer     $addressRenderer
     * @param \Plumrocket\RMA\Model\Config\Source\AdminUser   $adminUserSource
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Plumrocket\RMA\Model\Config\Source\AdminUser $adminUserSource,
        array $data = []
    ) {
        $this->adminUserSource = $adminUserSource;
        parent::__construct(
            $context,
            $registry,
            $adminHelper,
            $groupRepository,
            $metadata,
            $elementFactory,
            $addressRenderer,
            $data
        );
    }

    /**
     * Retrieve users for managers list
     *
     * @return array
     */
    public function getAdminUsers()
    {
        return $this->adminUserSource->toArray();
    }

    /**
     * Get users select element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getAdminUsersSelect()
    {
        $value =  $this->dataHelper->getFormData('manager_id') ?: $this->getEntity()->getManagerId();
        return $this->createElement('returns_manager_id', 'select', [
            'name'      => 'manager_id',
            'label'     => __('RMA Manager'),
            'options'   => $this->getAdminUsers(),
            'value'     => $value,
        ]);
    }

    /**
     * Retrieve link for a guest
     *
     * @return string|null
     */
    public function getDirectLink()
    {
        $entity = $this->getEntity();
        if (! $entity || ! $entity->getCode()) {
            return null;
        }

        return $this->returnsHelper->getQuickViewUrl($entity);
    }

    /**
     * Retrieve required options from parent
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _beforeToHtml()
    {
        AbstractOrder::_beforeToHtml();
    }

    /**
     * Get link to edit order address page
     *
     * @param Address $address
     * @param string $label
     * @return string
     */
    public function getAddressEditLink($address, $label = '')
    {
        if (empty($label)) {
            $label = __('Edit');
        }

        $params = [];
        if ($this->isNewEntity()) {
            $params['order_id'] = $this->getOrder()->getId();
        } else {
            $params['parent_id'] = $this->getEntity()->getId();
        }

        $url = $this->getUrl(Data::SECTION_ID . '/returns/address', $params);
        return '<a id="address-edit" href="' . $url . '">' . $label . '</a>';
    }

    /**
     * Returns string with formatted address
     *
     * @param OrderAddress $address
     * @return string
     */
    public function getFormattedAddress(OrderAddress $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * Get entity address
     *
     * @return OrderAddress|null
     */
    public function getAddress()
    {
        if ($this->getEntity()->isVirtual()) {
            return;
        }

        $address = $this->getEntity()->getAddress();
        if (! $address->getId()) {
            /**
             * For create page
             */
            $order = $this->getOrder();
            if ($order && $order->getId()) {
                $unassignedaddress = $address->getUnassigned($order->getId());
                if ($this->isNewEntity() && $unassignedaddress) {
                    $address = $unassignedaddress;
                } else {
                    $address = $order->getShippingAddress();
                }
            }
        }

        return $address && $address->getId() ? $address : null;
    }
}
