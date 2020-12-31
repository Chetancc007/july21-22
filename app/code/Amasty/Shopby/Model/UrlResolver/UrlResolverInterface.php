<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\UrlResolver;

interface UrlResolverInterface
{
    /**
     * Resolve an url
     *
     * @return string
     */
    public function resolve(): string;
}
