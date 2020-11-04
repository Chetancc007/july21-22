<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.19
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Helper\CategoryMapping\Multiplicity;

use Mirasvit\Feed\Helper\CategoryMapping\ReaderInterface;

interface ReaderMultiplicityInterface
{
    /**
     * @return $this
     */
    public function findAll();

    /**
     * @param ReaderInterface $item
     * @return $this
     */
    public function addItem(ReaderInterface $item);

    /**
     * @return array
     */
    public function getItems();
}