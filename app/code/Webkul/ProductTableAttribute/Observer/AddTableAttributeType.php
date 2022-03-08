<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_ProductTableAttribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ProductTableAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\ProductTableAttribute\Logger\Logger;

class AddTableAttributeType implements ObserverInterface
{

    /**
     * Logger $logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Add new attribute type to manage attributes interface
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // adminhtml_product_attribute_types
        try {
            $response = $observer->getEvent()->getResponse();
            $types = $response->getTypes();
            $types[] = [
                'value' => 'table',
                'label' => __('Table'),
                'hide_fields' => [
                    'is_used_for_promo_rules',
                    'is_visible_on_front',
                    'used_in_product_listing',
                    'used_for_sort_by',
                    'is_required',
                    'is_unique',
                    'is_searchable',
                    'is_comparable'
                ],
            ];
            $response->setTypes($types);
            return $this;
        } catch (\Exception $e) {
            $this->logger->addError("Error in observer=AddTableAttributeType =".$e->getMessage());
        }
    }
}
