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

namespace Plumrocket\RMA\Block\Returns;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Instructions extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * Instructions block html
     *
     * @var string
     */
    protected $instructions = null;

    /**
     * Check if need to show instructions
     *
     * @return boolean
     */
    public function showInstructions()
    {
        return ReturnsStatus::STATUS_PROCESSED_CLOSED !== $this->returnsHelper->getStatus($this->getEntity())
            && $this->returnsHelper->hasAuthorized($this->getEntity());
    }

    /**
     * Get instructions text
     *
     * @return string
     */
    public function getInstructions()
    {
        if (null === $this->instructions) {
            $this->instructions = $this->getCmsBlockHtml(
                $this->getConfigHelper()->getReturnInstructionsBlock()
            );
        }

        return $this->instructions;
    }

    /**
     * Check if buttons exist in block html
     *
     * @return boolean
     */
    public function hasButtons()
    {
        $html = $this->getInstructions();
        return false !== mb_strpos($html, 'prrma-instructions-buttons');
    }
}
