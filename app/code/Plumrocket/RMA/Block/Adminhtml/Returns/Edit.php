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

use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    use TemplateTrait;

    /**
     * @var ReturnsStatus
     */
    protected $status;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param ReturnsStatus                         $status
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        ReturnsStatus $status,
        array $data = []
    ) {
        $this->status = $status;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected $formId = 'edit_form';

    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->templateTraitInit();

        $this->_objectId = 'id';
        $this->_blockGroup = 'Plumrocket_RMA';
        $this->_controller = 'adminhtml_returns';

        parent::_construct();

        $this->removeButton('delete');

        if ($this->_isAllowedAction('Plumrocket_RMA::returns')) {
            if ($this->isNewEntity()) {
                $this->addButton(
                    'saveandcontinue',
                    [
                        'label' => __('Submit Return'),
                        'class' => 'save primary',
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                            ],
                        ]
                    ]
                );

                $this->removeButton('save');
                $this->removeButton('reset');
            } else {
                $this->addButton(
                    'saveandcontinue',
                    [
                        'label' => __('Save and Continue Edit'),
                        'class' => 'save',
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                            ],
                        ]
                    ],
                    -100
                );

                if (! $this->getEntity()->isVirtual()
                    && ! in_array($this->getEntity()->getStatus(), array_keys($this->status->getFinalStatuses()))
                    && $this->returnsHelper->hasAuthorized($this->getEntity())
                ) {
                    $onClick = "popWin('{$this->returnsHelper->getPrintUrl($this->getEntity(), true)}')";
                    $this->addButton(
                        'print',
                        [
                            'label' => __('Print'),
                            'class' => 'print',
                            'onclick' => $onClick
                        ]
                    );
                }

                if ($this->getEntity()->isClosed()) {
                    $this->addButton(
                        'open',
                        [
                            'label' => __('Open RMA'),
                            'class' => 'open',
                            'onclick' => 'actionConfirm(\'' . __(
                                'Open RMA'
                            ) . '\', \'' . __(
                                'Are you sure you want to do this?'
                            ) . '\', \'' . $this->getOpenUrl() . '\')'
                        ]
                    );
                } else {
                    $this->addButton(
                        'close',
                        [
                            'label' => __('Cancel RMA'),
                            'class' => 'close',
                            'onclick' => 'actionConfirm(\'' . __(
                                'Cancel RMA'
                            ) . '\', \'' . __(
                                'Are you sure you want to do this?'
                            ) . '\', \'' . $this->getCancelUrl() . '\')'
                        ]
                    );
                }

                /**
                 * Order credit memo
                 */
                $order = $this->getEntity()->getOrder();
                if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $order && $order->canCreditmemo()) {
                    $message = __(
                        'This will create an offline refund. ' .
                        'To create an online refund, open an invoiceand create credit memo for it.' .
                        'Do you want to continue?'
                    );
                    $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
                    if ($order->getPayment()->getMethodInstance()->isGateway()) {
                        $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
                    }
                    $this->addButton(
                        'order_creditmemo',
                        ['label' => __('Credit Memo'), 'onclick' => $onClick, 'class' => 'credit-memo']
                    );
                }
            }
        } else {
            $this->removeButton('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->isNewEntity()) {
            return __('New Return');
        } else {
            return __('Edit Return');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get URL for open action
     *
     * @return string
     */
    public function getOpenUrl()
    {
        return $this->getUrl('*/returnsarchive/open', [
            'id' => $this->getEntity()->getId()
        ]);
    }

    /**
     * Get URL for cancel action
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', [
            'id' => $this->getEntity()->getId()
        ]);
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->isNewEntity()) {
            return $this->getUrl('sales/order/view', [
                'order_id' => $this->getRequest()->getParam('order_id')
            ]);
        }
        return $this->getUrl('*/*/');
    }

    /**
     * Credit memo URL getter
     *
     * @return string
     */
    public function getCreditmemoUrl()
    {
        $order = $this->getEntity()->getOrder();
        return $this->getUrl('sales/order_creditmemo/start', [
            'order_id' => $order->getId()
        ]);
    }
}
