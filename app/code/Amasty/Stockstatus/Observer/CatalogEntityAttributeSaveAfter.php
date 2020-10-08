<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Observer;

use Amasty\Stockstatus\Model\ResourceModel\Ranges as RangesModel;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogEntityAttributeSaveAfter implements ObserverInterface
{
    const RULE_CODE = 'custom_stock_status_qty_rule';

    const STATUS_CODE = 'custom_stock_status';

    /**
     * @var RangesModel
     */
    private $ranges;

    /**
     * @var \Amasty\Stockstatus\Model\RangesFactory
     */
    private $rangesFactory;

    /**
     * @var \Amasty\Stockstatus\Helper\Image
     */
    private $imageHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RangesModel $ranges,
        \Amasty\Stockstatus\Model\RangesFactory $rangesFactory,
        \Amasty\Stockstatus\Helper\Image $imageHelper,
        RequestInterface $request
    ) {
        $this->ranges = $ranges;
        $this->rangesFactory = $rangesFactory;
        $this->imageHelper = $imageHelper;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $attribute = $observer->getData('data_object');
        if ($attribute && in_array($attribute->getAttributeCode(), [self::RULE_CODE, self::STATUS_CODE])) {
            $this->saveStockStatusRanges();

            $deletedOptions = $attribute->getOption()['delete'] ?? [];
            $deletedOptions = array_filter($deletedOptions);
            if ($deletedOptions) {
                $deletedOptions = array_keys($deletedOptions);
                $this->ranges->deleteByField(
                    $attribute->getAttributeCode() == self::STATUS_CODE ? 'status_id' : 'rule',
                    $deletedOptions
                );
            }
        }
    }

    /**
     * @return void
     */
    protected function saveStockStatusRanges()
    {
        $ranges = $this->request->getParam('amstockstatus_range');
        if ($ranges && is_array($ranges)) {
            $ids = [];
            foreach ($ranges as $range) {
                $id = (int)$range['entity_id'] ?? 0;
                unset($range['entity_id']);
                $model = $this->rangesFactory->create();
                if ($id) {
                    $model->load($id);
                }

                $model->addData($range);
                $model->save();
                $ids[] = $model->getEntityId();
            }

            $this->ranges->deleteAllInstead($ids);
        }
        /**
         * Deleting
         */
        $toDelete = $this->request->getParam('amstockstatus_icon_delete');
        if ($toDelete) {
            foreach ($toDelete as $optionId => $del) {
                if ($del) {
                    $this->imageHelper->delete($optionId);
                }
            }
        }

        /**
         * Uploading files
         */
        if ($this->request->getFiles('amstockstatus_icon')) {
            $files = $this->request->getFiles('amstockstatus_icon');
            foreach ($files as $optionId => $file) {
                if (isset($file['name']) && UPLOAD_ERR_OK == $file['error']) {
                    $this->imageHelper->delete($optionId);
                    $this->imageHelper->uploadImage($optionId, $file);
                }
            }
        }
    }
}
