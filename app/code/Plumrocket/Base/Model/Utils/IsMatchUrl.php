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

namespace Plumrocket\Base\Model\Utils;

/**
 * Check if url is matched by pattern, compare url without "get" params and fragment
 *
 * @since 2.3.1
 */
class IsMatchUrl
{
    /**
     * @var \Plumrocket\Base\Model\Utils\GetRelativePathFromUrl
     */
    private $getRelativePathFromUrl;

    /**
     * @param \Plumrocket\Base\Model\Utils\GetRelativePathFromUrl $getRelativePathFromUrl
     */
    public function __construct(GetRelativePathFromUrl $getRelativePathFromUrl)
    {
        $this->getRelativePathFromUrl = $getRelativePathFromUrl;
    }

    /**
     * @param string $url
     * @param string $pattern
     * @return bool
     */
    public function execute(string $url, string $pattern): bool
    {
        $relativeUrl = $this->getRelativePathFromUrl->execute($url);
        $rexep = $this->patternToRegex($pattern);
        return 1 === preg_match('~^' . $rexep . '$~', $relativeUrl);
    }

    /**
     * @param string $url
     * @param array  $patterns
     * @return bool
     */
    public function executeList(string $url, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($this->execute($url, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pattern
     * @return string|string[]
     */
    private function patternToRegex(string $pattern)
    {
        $pattern = $this->getRelativePathFromUrl->execute($pattern);
        $rexep = str_replace('/', '\/', preg_quote($pattern, '~'));
        return str_replace('\*', '(?:.*)', $rexep);
    }
}
