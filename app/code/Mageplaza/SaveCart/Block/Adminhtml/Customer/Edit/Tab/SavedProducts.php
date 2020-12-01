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
use Magento\Backend\Block\Widget\Grid\Column\Filter\Range;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Mageplaza\SaveCart\Block\Adminhtml\Grid\Column\Filter\Text;
use Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer\ProductPrice;
use Mageplaza\SaveCart\Block\Adminhtml\Grid\Renderer\Thumbnail;
use Mageplaza\SaveCart\Model\Product as SaveCartProduct;
use Mageplaza\SaveCart\Model\ResourceModel\Product\Collection;
use Mageplaza\SaveCart\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class SavedProducts
 * @package Mageplaza\SaveCart\Block\Adminhtml\Customer\Edit\Tab
 */
class SavedProducts extends Extended
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
     * SavedProducts constructor.
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

        $this->setId('product_grid');
        $this->setDefaultSort('id');
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
     * @throws Exception
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create()->filterCollection($this->getCustomerId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('thumbnail', [
            'header'   => __('Thumbnail'),
            'index'    => 'id',
            'filter'   => false,
            'renderer' => Thumbnail::class
        ]);
        $this->addColumn('product_name', [
            'header' => __('Name'),
            'type'   => 'text',
            'filter' => Text::class,
            'index'  => 'value'
        ]);
        $this->addColumn('sku', [
            'header' => __('SKU'),
            'align'  => 'left',
            'index'  => 'sku',
            'type'   => 'text',
            'filter' => Text::class,
        ]);
        $this->addColumn('price', [
            'header'   => __('Amount Change'),
            'filter'   => false,
            'align'    => 'left',
            'index'    => 'id',
            'type'     => 'text',
            'renderer' => ProductPrice::class
        ]);
        $this->addColumn('qty', [
            'header' => __('Quantity'),
            'filter' => Range::class,
            'align'  => 'left',
            'index'  => 'qty',
            'type'   => 'text'
        ]);
        $this->addColumn('action', [
            'header'   => __('Action'),
            'index'    => 'id',
            'filter'   => false,
            'type'     => 'options',
            'renderer' => Multiaction::class,
            'actions'  => [
                'delete' => [
                    'caption' => 'Delete',
                    'url'     => '#',
                    'onclick' => 'return productControl.removeItem($id);'
                ]
            ]
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param SaveCartProduct|DataObject $item
     *
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $item->getProductId()]);
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
            productControl = {
                reload: function (urlParams) {
                    if (!urlParams) {
                        urlParams = \'\';
                    }
                    var url = product_gridJsObject.
                    url + \'id/\' + product_gridJsObject +\'/?ajax=true\' + urlParams;
                    $.ajax({
                        url: url,
                        method: \'POST\',
                        data: {form_key: FORM_KEY, reload: 1},
                        showLoader: true,
                        complete: function (res) {
                            $(\'#\' + product_gridJsObject.containerId
                        ).
                            html(res.responseText);
                            product_gridJsObject.
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
        return $this->getUrl('mpsavecart/customer/gridProduct', ['_current' => true]);
    }
}
