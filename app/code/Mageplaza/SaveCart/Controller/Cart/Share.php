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

namespace Mageplaza\SaveCart\Controller\Cart;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use RuntimeException;

/**
 * Class Share
 * @package Mageplaza\SaveCart\Controller\Product
 */
class Share extends AbstractShareRestore
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->helper->allowShare()) {
            $this->messageManager->addWarningMessage(__('Sharing is disabled'));

            return $this->_redirect('checkout/cart/');
        }
        try {
            parent::execute();
        } catch (RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('checkout/cart/');
    }
}
