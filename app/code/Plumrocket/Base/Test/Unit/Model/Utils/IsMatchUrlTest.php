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
use Plumrocket\Base\Model\Utils\IsMatchUrl;

/**
 * @since 2.3.1
 */
class IsMatchUrlTest extends TestCase
{
    /**
     * @var \Plumrocket\Base\Model\Utils\IsMatchUrl
     */
    private $isMatchUrl;

    protected function setUp()
    {
        $storeMock = $this->createMock(Store::class);

        $storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
                                 ->disableOriginalConstructor()
                                 ->getMockForAbstractClass();

        $storeManagerMock
            ->method('getStore')
            ->willReturn($storeMock);

        $objectManager = new ObjectManager($this);
        $getRelativePathFromUrl = $objectManager->getObject(
            GetRelativePathFromUrl::class,
            ['storeManager' => $storeManagerMock]
        );

        $this->isMatchUrl = $objectManager->getObject(
            IsMatchUrl::class,
            ['getRelativePathFromUrl' => $getRelativePathFromUrl]
        );

        $storeMock
            ->method('getBaseUrl')
            ->willReturn('https://example.shop.com');
    }

    public function testExactUrl()
    {
        $this->assertTrue($this->isMatchUrl->execute('https://example.shop.com/test', 'https://example.shop.com/test'));
    }

    public function testExactRelativeUrl()
    {
        $this->assertTrue($this->isMatchUrl->execute('test/', 'test/'));
    }

    /**
     * @dataProvider regexPatternProvider
     *
     * @param string $url
     * @param string $pattern
     * @param bool   $result
     */
    public function testRegexPattern(
        string $url,
        string $pattern,
        bool $result
    ) {
        $this->assertSame(
            $result,
            $this->isMatchUrl->execute($url, $pattern),
            "Url: '$url', pattern: '$pattern'"
        );
    }

    /**
     * @return \Generator
     */
    public function regexPatternProvider()
    {
        yield [
            'url'     => 'https://example.shop.com/test/',
            'pattern' => '/tes*/',
            'result'  => true,
        ];
        yield [
            'url'     => 'https://example.shop.com/category/id/1',
            'pattern' => '/category/*',
            'result'  => true,
        ];
        yield [
            'url'     => 'https://example.shop.com/category',
            'pattern' => '/category/*',
            'result'  => false,
        ];
        yield [
            'url'     => 'https://example.shop.com/category/id/4',
            'pattern' => '*category*',
            'result'  => true,
        ];
        yield [
            'url'     => 'https://example.shop.com/category/id/4',
            'pattern' => '*product*',
            'result'  => false,
        ];
        yield [
            'url'     => 'https://example.shop.com/c-a',
            'pattern' => 'c-b',
            'result'  => false,
        ];
        yield [
            'url'     => 'https://example.shop.com/test/abc',
            'pattern' => '/tes*/abc',
            'result'  => true,
        ];
        yield [
            'url'     => 'https://example.shop.com/testabc',
            'pattern' => '/tes*/abc',
            'result'  => false,
        ];
    }
}
