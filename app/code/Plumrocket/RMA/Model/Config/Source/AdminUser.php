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
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Config\Source;

use Magento\Framework\Authorization\Policy\Acl;
use Magento\User\Model\UserFactory;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Controller\Adminhtml\Returns;

class AdminUser extends AbstractSource
{
    /**
     * User Factory
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * ACL
     * @var Acl
     */
    protected $acl;

    /**
     * @param Data        $dataHelper
     * @param UserFactory $userFactory
     * @param Acl         $acl
     */
    public function __construct(
        Data $dataHelper,
        UserFactory $userFactory,
        Acl $acl
    ) {
        $this->userFactory = $userFactory;
        $this->acl = $acl;
        parent::__construct($dataHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $users = $this->userFactory->create()->getCollection();

            $this->options = [];
            foreach ($users as $user) {
                if ($this->acl) {
                    if (! $this->acl->isAllowed($user->getAclRole(), 'Plumrocket_RMA::' . Data::SECTION_ID)) {
                        continue;
                    }
                }

                $this->options[] = [
                    'label' => ($user->getFirstname() . ' ' . $user->getLastname()),
                    'value' => $user->getId()
                ];
            }
        }

        return $this->options;
    }
}
