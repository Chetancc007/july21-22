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

namespace Plumrocket\RMA\Block\Returns\Address;

use Magento\Directory\Block\Data as DirectoryData;
use Plumrocket\RMA\Block\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

/**
 * Edit returns address form container block
 */
class Edit extends \Magento\Customer\Block\Address\Edit
{
    use TemplateTrait;

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        DirectoryData::_prepareLayout();

        $this->_address = $this->registry->registry('returns_address');

        if (null === $this->_address || ! $this->_address->getId()) {
            $this->_address = $this->getOrder()->getShippingAddress();
        }

        $this->pageConfig->getTitle()->set($this->getTitle());

        return $this;
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Edit Address');
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        return $this->getUrl(Data::SECTION_ID . '/returns/create', [
            'order_id' => $this->getOrder()->getId()
        ]);
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('*/*/addressSave', [
            'order_id' => $this->getOrder()->getId(),
            '_secure' => true
        ]);
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        $data = $this->dataHelper->getFormData('returns_address');
        if ($data && is_array($data) && $this->_address) {
            $this->_address->addData($data);
        }

        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region;
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $regionId = $this->getAddress()->getRegionId();
        return $regionId === null ? 0 : $regionId;
    }

    /**
     * Retrieve the number of addresses associated with the customer given a customer Id.
     *
     * @return int
     */
    public function getCustomerAddressCount()
    {
        return 1;
    }

    /**
     * Determine if the address can be set as the default billing address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultBilling()
    {
        return false;
    }

    /**
     * Determine if the address can be set as the default shipping address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultShipping()
    {
        return false;
    }

    /**
     * Is the address the default billing address?
     *
     * @return bool
     */
    public function isDefaultBilling()
    {
        return false;
    }

    /**
     * Is the address the default shipping address?
     *
     * @return bool
     */
    public function isDefaultShipping()
    {
        return false;
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }

    /**
     * Return back button Url, either to customer address or account.
     *
     * @return string
     */
    public function getBackButtonUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/create', [
            'order_id' => $this->getOrder()->getId()
        ]);
    }
}
