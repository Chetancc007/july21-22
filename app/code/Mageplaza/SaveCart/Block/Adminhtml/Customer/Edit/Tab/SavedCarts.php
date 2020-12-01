<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Block\Adminhtml\Customer\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Mageplaza\SaveCart\Block\Adminhtml\Grid\Column\Filter\Text;
use Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer\CartPrice;
use Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer\Items;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Cart\CollectionFactory;

/**
 * Class SavedCarts
 * @package Mageplaza\SaveCart\Block\Adminhtml\Customer\Edit\Tab
 */
class SavedCarts extends Extended
{
    /**
     * @type CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * SavedCarts constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry      = $registry;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('cart_grid');
        $this->setDefaultSort('cart_id');
        $this->setUseAjax(true);
    }

    /**
     * Get Customer Id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->getSelect()
            ->where('main_table.customer_id = ' . $this->getCustomerId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('cart_id', [
            'header' => __('ID'),
            'index'  => 'cart_id',
            'filter' => Text::class,
            'type'   => 'text'
        ]);
        $this->addColumn('created_at', [
            'header' => __('Created At'),
            'index'  => 'created_at',
            'type'   => 'date'
        ]);
        $this->addColumn('cart_name', [
            'header' => __('Cart Name'),
            'index'  => 'cart_name',
            'filter' => Text::class,
            'type'   => 'text'
        ]);
        $this->addColumn('items', [
            'header'           => __('Items'),
            'filter'           => false,
            'index'            => 'cart_id',
            'column_css_class' => 'mp-col-items',
            'type'             => 'text',
            'renderer'         => Items::class
        ]);
        $this->addColumn('description', [
            'header' => __('Description'),
            'index'  => 'description',
            'filter' => Text::class,
            'type'   => 'text'
        ]);
        $this->addColumn('total_price', [
            'header'   => __('Total Price'),
            'index'    => 'cart_id',
            'filter'   => false,
            'type'     => 'text',
            'renderer' => CartPrice::class
        ]);
        $this->addColumn('action', [
            'header'   => __('Action'),
            'index'    => 'cart_id',
            'type'     => 'options',
            'filter'   => false,
            'renderer' => Multiaction::class,
            'actions'  => [
                'delete' => [
                    'caption' => 'Delete',
                    'url'     => '#',
                    'onclick' => 'return cartControl.removeItem($cart_id)'
                ]
            ]
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getAdditionalJavaScript()
    {
        $content = __('Are you sure you want to remove this item?');

        return 'require([
            "jquery",
            "Magento_Ui/js/modal/confirm",
        ], function ($, confirm) {
            cartControl = {
                reload: function (urlParams) {
                    if (!urlParams) {
                        urlParams = \'\';
                    }
                    var url = cart_gridJsObject.
                    url + \'id/\' + cart_gridJsObject +\'/?ajax=true\' + urlParams;
                    $.ajax({
                        url: url,
                        method: \'POST\',
                        data: {form_key: FORM_KEY, reload: 1},
                        showLoader: true,
                        complete: function (res) {
                            $(\'#\' + cart_gridJsObject.containerId
                        ).
                            html(res.responseText);
                            cart_gridJsObject.
                            initGrid();
                        }
                    });
                },
                removeItem: function (itemId) {
                    var self = this;
    
                    confirm({
                        content: \'' . $content . '\',
                        actions: {
                            confirm: function () {
                                self.reload(\'&delete=\' + itemId);
                            }
                        }
                    });
                }
            };
        });
        ';
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpsavecart/customer/gridCart', ['_current' => true]);
    }
}
