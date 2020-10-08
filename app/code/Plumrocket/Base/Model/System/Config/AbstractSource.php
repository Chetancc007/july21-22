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
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Base\Model\System\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Allow to create short system config sources
 *
 * @since 2.3.0
 */
abstract class AbstractSource implements OptionSourceInterface
{
    /**
     * Convert ['<value>' => '<label>'] to ['value' => '<value>', 'label' => '<label>']
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->toOptionHash() as $key => $value) {
            $result[] = [
                'value'    => $key,
                'label'    => $value,
            ];
        }
        return $result;
    }

    /**
     * Return array of options
     *
     * @return array Format: array(array('<value>' => '<label>'), ...)
     */
    abstract public function toOptionHash(): array;
}
