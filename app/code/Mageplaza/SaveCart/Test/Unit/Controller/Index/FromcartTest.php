<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SaveCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SaveCart\Test\Unit\Controller\Index;

use Exception;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\SaveCart\Controller\Index\Fromcart;
use Mageplaza\SaveCart\Model\Product;
use Mageplaza\SaveCart\Model\ProductFactory;
use Mageplaza\SaveCart\Model\ResourceModel\Product as ProductResource;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class TestFromcart
 * @package Mageplaza\SaveCart\Test\Unit\Controller\Index
 */
class FromcartTest extends TestCase
{
    /**
     * @var CheckoutCart|PHPUnit_Framework_MockObject_MockObject
     */
    private $cart;

    /**
     * @var CartHelper|PHPUnit_Framework_MockObject_MockObject
     */
    private $cartHelper;

    /**
     * @var Escaper|PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var Validator|PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var ProductResource|PHPUnit_Framework_MockObject_MockObject
     */
    private $productResource;

    /**
     * @var ProductFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $productFactory;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var ResultRedirect|PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var Http|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var ResultFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var Fromcart
     */
    private $controller;

    protected function setUp()
    {
        $this->prepareContext();

        $this->cart             = $this->getMockBuilder(CheckoutCart::class)->disableOriginalConstructor()->getMock();
        $this->cartHelper       = $this->getMockBuilder(CartHelper::class)->disableOriginalConstructor()->getMock();
        $this->escaper          = $this->getMockBuilder(Escaper::class)->disableOriginalConstructor()->getMock();
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)->disableOriginalConstructor()->getMock();
        $this->productResource  = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()->getMock();
        $this->productFactory   = $this->getMockBuilder(ProductFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->session          = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();

        $this->controller = new Fromcart(
            $this->context,
            $this->cart,
            $this->cartHelper,
            $this->escaper,
            $this->formKeyValidator,
            $this->productFactory,
            $this->productResource,
            $this->session
        );
    }

    protected function prepareContext()
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder(ResultRedirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->method('create')->with(ResultFactory::TYPE_REDIRECT)->willReturn($this->resultRedirect);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
    }

    public function testExecuteWithInvalidFormKey()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecuteNoCartItem()
    {
        $itemId  = 1;
        $cartUrl = 'cart_url';

        $this->formKeyValidator->expects($this->once())->method('validate')->willReturn(true);
        $this->request->method('getParam')->with('item')->willReturn($itemId);

        $quoteMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $quoteMock->method('getItemById')->with($itemId)->willReturn(null);

        $this->cart->method('getQuote')->willReturn($quoteMock);
        $this->cartHelper->method('getCartUrl')->willReturn($cartUrl);
        $this->resultRedirect->method('setUrl')->with($cartUrl)->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecuteWithException()
    {
        $cartUrl          = 'cart_url';
        $exceptionMessage = 'exception_message';
        $exception        = new Exception($exceptionMessage);

        $this->formKeyValidator->method('validate')->with($this->request)->willReturn(true);
        $this->request->method('getParam')->with('item')->willThrowException($exception);
        $this->messageManager->method('addExceptionMessage')
            ->with($exception, 'This item cannot be moved to your Saved Products List.')->willReturnSelf();
        $this->cartHelper->method('getCartUrl')->willReturn($cartUrl);
        $this->resultRedirect->method('setUrl')->with($cartUrl)->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecute()
    {
        $cartUrl     = 'cart_url';
        $itemId      = [1, 2];
        $customerId  = 1;
        $storeId     = 1;
        $productId   = 1;
        $productQty  = 2;
        $productName = 'product_name';
        $allItems    = ['item'];

        $this->formKeyValidator->method('validate')->with($this->request)->willReturn(true);
        $quoteMock     = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getStoreId', 'removeItem', 'getItemById', 'getAllItems'])
            ->getMock();
        $quoteItemMock = $this->getMockBuilder(Item::class)
            ->setMethods(['getProductId', 'getBuyRequest', 'getQty', 'getProduct'])
            ->disableOriginalConstructor()->getMock();

        $this->cart->method('getQuote')->willReturn($quoteMock);
        $this->request->method('getParam')->with('item')->willReturn($itemId);
        $quoteMock->method('getItemById')->with($itemId)->willReturn($quoteItemMock);

        $this->session->method('getQuote')->willReturn($quoteMock);
        $quoteMock->method('getCustomerId')->willReturn($customerId);
        $quoteMock->method('getStoreId')->willReturn($storeId);

        $dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()->getMock();
        $quoteItemMock->method('getProductId')->willReturn($productId);
        $quoteItemMock->method('getBuyRequest')->willReturn($dataObject);
        $quoteItemMock->method('getQty')->willReturn($productQty);
        $productSaveCart = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->setMethods(['setProductId', 'setQty', 'setCustomerId', 'setStoreId', 'setBuyRequest'])
            ->getMock();
        $this->productFactory->method('create')->willReturn($productSaveCart);

        $dataObject->method('getData')->willReturn(['a']);
        $productSaveCart->method('setProductId')->with($productId)->willReturnSelf();
        $productSaveCart->method('setQty')->with($productQty)->willReturnSelf();
        $productSaveCart->method('setCustomerId')->with($customerId)->willReturnSelf();
        $productSaveCart->method('setStoreId')->with($storeId)->willReturnSelf();
        $productSaveCart->method('setBuyRequest')
            ->with($dataObject->getData())->willReturnSelf();
        $this->productResource->method('save')->with($productSaveCart)->willReturnSelf();

        $this->cart->method('getQuote')->willReturn($quoteMock);
        $quoteMock->method('removeItem')->with($itemId)->willReturnSelf();
        $this->cart->method('save')->willReturnSelf();

        $productCatalog = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()->getMock();
        $quoteItemMock->method('getProduct')->willReturn($productCatalog);
        $productCatalog->method('getName')->willReturn($productName);
        $this->escaper->method('escapeHtml')->with($productName)->willReturn($productName);
        $this->messageManager->method('addSuccessMessage')
            ->with(__('%1 has been moved to your Saved Products List.', $productName))->willReturnSelf();

        $this->cart->method('getQuote')->willReturn($quoteMock);
        $quoteMock->method('getAllItems')->willReturn($allItems);

        $this->controller->execute();
        $this->cartHelper->method('getCartUrl')->willReturn($cartUrl);
        $this->resultRedirect->method('setUrl')->with($cartUrl)->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }
}
