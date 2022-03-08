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

namespace Webkul\ProductTableAttribute\Plugin\Product\Attribute\Source;

class InputtypePlugin
{
    /**
     * to not display table type
     */
    public function afterToOptionArray(\Magento\Catalog\Model\Product\Attribute\Source\Inputtype $subject, $result)
    {
        $inputTypes = [];
        foreach ($result as $type) {
            if ($type['value'] != "table") {
                $inputTypes[] = $type;
            }
        }
        return $inputTypes;
    }
}
