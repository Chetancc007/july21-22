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

use Magento\Framework\DataObject;
use Plumrocket\RMA\Block\Adminhtml\Returns\Template;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Model\Returns\Track;

class Tracking extends Template
{
    /**
     * @var \Plumrocket\RMA\Model\Returns\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context        $context
     * @param \Plumrocket\RMA\Model\Returns\ValidatorFactory $validatorFactory
     * @param array                                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Plumrocket\RMA\Model\Returns\ValidatorFactory $validatorFactory,
        array $data = []
    ) {
        $this->validatorFactory = $validatorFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve tracking numbers
     *
     * @return array
     */
    public function getTracks()
    {
        $tracks = $this->getEntity()->getTracks();

        // Autofill tracks that prepared to remove
        $tracksRemove = $this->getDataHelper()->getFormData('track_remove');
        foreach ($tracks as $track) {
            if (! empty($tracksRemove[$track->getid()])) {
                $track->setNeedRemove(true);
            }
        }

        // Autofill unsaved after refresh with validation error
        $tracksAutofill = $this->getDataHelper()->getFormData('track_add');
        if (is_array($tracksAutofill)) {
            foreach ($tracksAutofill as $track) {
                // Skip invalidated tracks.
                $validator = $this->validatorFactory->create()
                    ->validateTrack(
                        isset($track['carrier_code']) ? $track['carrier_code'] : null,
                        isset($track['track_number']) ? $track['track_number'] : null
                    );

                if (! $validator->isValid()) {
                    continue;
                }

                $tracks[] = new DataObject([
                    'carrier_code'  => $track['carrier_code'],
                    'track_number'  => $track['track_number'],
                    'is_unsaved'    => true,
                    'need_remove'   => false,
                ]);
            }
        }

        return $tracks;
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
     * Check if customer can add track
     *
     * @return bool
     */
    public function canSubmit()
    {
        return ! $this->getEntity()->isVirtual();
    }

    /**
     * Check if customer can remove track
     *
     * @param Track $track
     * @return bool
     */
    public function canRemove($track)
    {
        return $this->canSubmit();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getEntity()->isVirtual()) {
            return '';
        }

        return parent::_toHtml();
    }
}
