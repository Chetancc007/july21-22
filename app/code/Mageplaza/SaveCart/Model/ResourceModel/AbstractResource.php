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

namespace Mageplaza\SaveCart\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class AbstractResource
 * @package Mageplaza\SaveCart\Model\ResourceModel
 */
abstract class AbstractResource extends AbstractDb
{
    /**
     * @var Random
     */
    private $randomDataGenerator;

    /**
     * AbstractResource constructor.
     *
     * @param Context $context
     * @param Random $randomDataGenerator
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Random $randomDataGenerator,
        $connectionName = null
    ) {
        $this->randomDataGenerator = $randomDataGenerator;

        parent::__construct($context, $connectionName);
    }

    /**
     * Check if token exists
     *
     * @param string $token
     *
     * @return bool
     * @throws LocalizedException
     */
    public function exists($token)
    {
        $connection = $this->getConnection();
        $select     = $connection->select();
        $select->from($this->getMainTable(), 'token');
        $select->where('token = :token');

        return !($connection->fetchOne($select, ['token' => $token]) === false);
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractResource
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $attempt = 0;
            do {
                if ($attempt >= 10) {
                    throw new LocalizedException(
                        __('Something went wrong while saving object. Token exist')
                    );
                }
                $token = $this->randomDataGenerator->getUniqueHash();
                ++$attempt;
            } while ($this->exists($token));
            $object->setToken($token);
        }

        return parent::_beforeSave($object);
    }
}
