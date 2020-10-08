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

namespace Plumrocket\Base\Test\Unit\Model\Utils;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Plumrocket\Base\Model\Utils\GetRelativePathFromUrl;

/**
 * @since 2.3.1
 */
class GetRelativePathFromUrlTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Plumrocket\Base\Model\Utils\GetRelativePathFromUrl
     */
    private $getRelativePathFromUrl;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeMock;

    protected function setUp()
    {
        $this->storeMock = $this->createMock(Store::class);

        $storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
                                 ->disableOriginalConstructor()
                                 ->getMockForAbstractClass();

        $storeManagerMock
            ->method('getStore')
            ->willReturn($this->storeMock);

        $objectManager = new ObjectManager($this);
        $this->getRelativePathFromUrl = $objectManager->getObject(
            GetRelativePathFromUrl::class,
            [
                'storeManager' => $storeManagerMock,
            ]
        );
    }

    public function testEmptyUrl()
    {
        $this->storeMock
            ->method('getBaseUrl')
            ->willReturn('https://example.shop.com');

        $this->assertSame('/', $this->getRelativePathFromUrl->execute(''));
    }

    public function testBaseUrl()
    {
        $this->storeMock
            ->method('getBaseUrl')
            ->willReturn('https://example.shop.com');

        $this->assertSame('/', $this->getRelativePathFromUrl->execute('https://example.shop.com'));
    }

    /**
     * @dataProvider urlsProvider
     *
     * @param string $result
     * @param string $baseUrl
     * @param string $url
     * @param bool   $removeGetParams
     * @param bool   $removeFragment
     */
    public function testNotHaveConsent(
        string $result,
        string $baseUrl,
        string $url,
        bool $removeGetParams,
        bool $removeFragment
    ) {
        $this->storeMock
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->assertSame($result, $this->getRelativePathFromUrl->execute($url, $removeGetParams, $removeFragment));
    }

    /**
     * @return \Generator
     */
    public function urlsProvider()
    {
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => 'https://example.shop.com/test/',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test/',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test?a=1',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test?a',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test#anchor',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test/?a=1#anchor',
            'removeGetParams' => true,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/?a=1',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test/?a=1#anchor',
            'removeGetParams' => false,
            'removeFragment'  => true,
        ];
        yield [
            'result'          => '/test/#anchor',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test/?a=1#anchor',
            'removeGetParams' => true,
            'removeFragment'  => false,
        ];
        yield [
            'result'          => '/test/?a=1#anchor',
            'baseUrl'         => 'https://example.shop.com',
            'url'             => '/test/?a=1#anchor',
            'removeGetParams' => false,
            'removeFragment'  => false,
        ];
    }
}
