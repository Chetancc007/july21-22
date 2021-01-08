<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickup
 */


namespace Amasty\StorePickup\Block\Adminhtml\Methods\Edit\Tab\Rates\Grid;

class ColumnSet extends \Magento\Backend\Block\Widget\Grid\ColumnSet
{
    /**
     * @var \Amasty\StorePickup\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory $generatorFactory,
        \Magento\Backend\Model\Widget\Grid\SubTotals $subtotals,
        \Magento\Backend\Model\Widget\Grid\Totals $totals,
        \Amasty\StorePickup\Helper\Data $helper,
        array $data
    ) {
        $this->helper = $helper;

        parent::__construct($context, $generatorFactory, $subtotals, $totals, $data);
    }

    protected function _prepareLayout()
    {
        $this->addColumn(
            'country',
            [
                'header' => __('Country'),
                'index' => 'country',
                'type' => 'options',
                'options' => $this->helper->getCountries(),
            ]
        );

        $this->addColumn(
            'state',
            [
                'header' => __('State'),
                'index' => 'state',
                'type' => 'options',
                'options' => $this->helper->getStates(),
            ]
        );

        $this->addColumn(
            'city',
            [
                'header' => __('City'),
                'index' => 'city',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'zip_from',
            [
                'header' => __('Zip From'),
                'index' => 'zip_from',
            ]
        );

        $this->addColumn(
            'zip_to',
            [
                'header' => __('Zip To'),
                'index' => 'zip_to',
            ]
        );

        $this->addColumn(
            'time_delivery',
            [
                'header' => __('Store Name'),
                'index' => 'time_delivery',
            ]
        );

        $link = $this->getUrl('amstorepick/rates/delete') . 'id/$id';
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getVid',
                'actions' => [
                    [
                        'caption' => __('Delete'),
                        'url' => $link,
                        'field' => 'id',
                        'confirm' => __('Are you sure?')
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'is_system' => true,
            ]
        );

        return parent::_prepareLayout();
    }

    public function addColumn($title, $data)
    {
        $column =
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Grid\Column::class, $title)->addData($data);
        $this->setChild($title, $column);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('amstorepick/rates/edit', ['id' => $item->getId()]);
    }

}
