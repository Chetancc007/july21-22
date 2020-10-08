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

use Plumrocket\RMA\Block\Returns\Template;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Model\Returns\Track;

class Tracking extends Template
{
    /**
     * Retrieve tracking numbers
     *
     * @return array
     */
    public function getTracks()
    {
        return $this->getEntity()->getTracks();
    }

    /**
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        return $this->configHelper->getShippingCarriers();
    }

    /**
     * Retrieve carriers element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getCarriersElement()
    {
        return $this->createElement('carrier_code', 'select', [
            'name'      => "carrier_code",
            'options'   => $this->getCarriers(),
        ]);
    }

    /**
     * Retrieve number element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getNumberElement()
    {
        return $this->createElement('track_number', 'text', [
            'name'      => "track_number"
        ]);
    }

    /**
     * Retrieve save url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/track_add', [
            'id' => $this->getEntity()->getId()
        ]);
    }

    /**
     * Retrieve remove url
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/track_remove', [
            'id' => $this->getEntity()->getId()
        ]);
    }

    /**
     * Check if customer can add track
     *
     * @return bool
     */
    public function canSubmit()
    {
        return $this->configHelper->enabledTrackingNumber()
            && ! $this->getEntity()->isClosed()
            && ! $this->getEntity()->isVirtual()
            && $this->returnsHelper->hasAuthorized($this->getEntity());
    }

    /**
     * Check if customer can remove track
     *
     * @param Track $track
     * @return bool
     */
    public function canRemove($track)
    {
        return $this->canSubmit()
            && $track->getType() === Track::FROM_CUSTOMER;
    }
}
