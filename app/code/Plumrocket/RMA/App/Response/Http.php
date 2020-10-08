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

namespace Plumrocket\RMA\App\Response;

/**
 * Class Http
 *
 * @deprecated
 * @package Plumrocket\RMA\App\Response
 */
class Http extends \Magento\Framework\App\Response\Http
{
    /**
     * @var array
     */
    protected $finalHeaders = [];

    /**
     * {@inheritdoc}
     */
    public function setHeader($name, $value, $replace = false)
    {
        if (in_array($name, $this->finalHeaders)) {
            return $this;
        }

        return parent::setHeader($name, $value, $replace);
    }

    /**
     * Set a header and disable replace of it
     *
     * @param string  $name
     * @param string  $value
     * @param boolean $replace
     * @return $this
     */
    public function setFinalHeader($name, $value, $replace = false)
    {
        $this->setHeader($name, $value, $replace);
        $this->finalHeaders[] = $name;
        return $this;
    }
}
