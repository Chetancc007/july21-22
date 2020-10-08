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

namespace Plumrocket\Base\Model\Debug;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Export data for quick debug
 *
 * @since 2.3.0
 */
class Export
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var \Plumrocket\Base\Model\Debug\CollectInfo
     */
    private $collectInfo;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Export constructor.
     *
     * @param \Plumrocket\Base\Model\Debug\CollectInfo         $collectInfo
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        CollectInfo $collectInfo,
        DateTime $dateTime,
        FileFactory $fileFactory,
        SerializerInterface $serializer
    ) {
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->collectInfo = $collectInfo;
        $this->serializer = $serializer;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $date = $this->dateTime->date('Y-m-d_H-i-s');
        $zipFileName = 'debug_info_' . $date . '.json';

        $debugInfo = $this->collectInfo->execute();

        $this->fileFactory->create(
            $zipFileName,
            $this->serializer->serialize($debugInfo),
            DirectoryList::PUB,
            'json',
            null
        );
    }
}
