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
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Ui\Component\Listing;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => $item) {
                $dataSource['data']['items'][$key]['class'] = $this->getReplyStatusClass($item['reply_at'], $item);
                $dataSource['data']['items'][$key]['reply_name'] = $this->decorateReplyName($item['reply_name'], $item);
            }
        }

        return $dataSource;
    }

    /**
     * Decorate cells of row which is with reply
     *
     * @param string $value
     * @param array $row
     * @return string
     */
    public function getReplyStatusClass($value, $row)
    {
        $readAt = ! empty($row['read_mark_at']) ? $row['read_mark_at'] : null;
        $replyAt = ! empty($row['reply_at']) ? $row['reply_at'] : null;

        if (null === $readAt
            || ($readAt && strtotime($readAt) < strtotime($replyAt))
        ) {
            return 'prrma-replied';
        }

        return '';
    }

    /**
     * Decorate cell of last reply
     *
     * @param string $value
     * @param array $row
     * @return string
     */
    public function decorateReplyName($value, $row)
    {
        return $value ? __(' by %1', $value) : '';
    }
}