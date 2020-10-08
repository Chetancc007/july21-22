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

namespace Plumrocket\RMA\Block\Adminhtml\Grid\Column;

class ActionLink extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add decorator to column
     *
     * @return Array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorator'];
    }

    /**
     * Decorate column values
     *
     * @param string $value
     * @param \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorator($value, $row, $column, $isExport)
    {
        $html = sprintf(
            '<a href="%s"><span>%s</span></a>',
            $this->getUrl('*/*/edit', ['id' => $row->getId()]),
            __('Edit')
        );
        return $html;
    }
}
