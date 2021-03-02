<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StockstatusGraphQl
 */


declare(strict_types=1);

namespace Amasty\StockstatusGraphQl\Model\Resolver;

use Amasty\Stockstatus\Model\Stockstatus\Renderer\Info as InfoRenderer;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class InfoLink implements ResolverInterface
{
    /**
     * @var InfoRenderer
     */
    private $infoRenderer;

    public function __construct(
        InfoRenderer $infoRenderer
    ) {
        $this->infoRenderer = $infoRenderer;
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
            $message = $this->infoRenderer->render((int) $context->getExtensionAttributes()->getStore()->getId());
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return [
            'link' => $message
        ];
    }
}
