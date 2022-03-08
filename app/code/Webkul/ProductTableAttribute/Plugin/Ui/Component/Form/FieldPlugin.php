<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_ProductTableAttribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ProductTableAttribute\Plugin\Ui\Component\Form;

class FieldPlugin
{
    /**
     * to not create component for table type attributes
     */
    public function aroundGetComponentName(\Magento\Ui\Component\Form\Field $subject, callable $proceed)
    {
        $formElement = $subject->getData('config/formElement');
        if ($formElement != "table") {
            return $proceed();
        }
    }

    /**
     * to not create component for table type attributes
     */
    public function aroundPrepare(\Magento\Ui\Component\Form\Field $subject, callable $proceed)
    {
        $formElement = $subject->getData('config/formElement');
        if ($formElement != "table") {
            return $proceed();
        }
    }
}
