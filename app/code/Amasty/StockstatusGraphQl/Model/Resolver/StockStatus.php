<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StockstatusGraphQl
 */


declare(strict_types=1);

namespace Amasty\StockstatusGraphQl\Model\Resolver;

use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class StockStatus implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Processor
     */
    private $processor;

    public function __construct(
        Processor $processor,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->processor = $processor;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $product = $this->productRepository->getById(
                $args['id'],
                false,
                $context->getExtensionAttributes()->getStore()->getId()
            );
            $this->processor->execute([$product]);
            $stockstatusInformation = $product->getExtensionAttributes()->getStockstatusInformation();
            $message = $stockstatusInformation->getStatusMessage();
            $statusIcon = $stockstatusInformation->getStatusIcon();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return [
            'message' => $message,
            'status_icon' => $statusIcon
        ];
    }
}
