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

namespace Plumrocket\Base\Model;

/**
 * Class GetModuleVersion
 * @since 2.1.6
 */
class GetModuleVersion implements \Plumrocket\Base\Api\GetModuleVersionInterface
{
    const CACHE_IDENTIFIER = 'PR_EXTENSION_VERSION';

    /**
     * @var \Magento\Framework\App\Utility\Files
     */
    private $files;

    /**
     * For example [ Plumrocket_ModuleName => '2.1.3' ]
     *
     * @var string[]
     */
    private $versionsLocalCache = [];

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * GetModuleVersion constructor.
     *
     * @param \Magento\Framework\App\Utility\Files  $files
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Framework\App\Utility\Files $files,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->files = $files;
        $this->cache = $cache;
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function execute($moduleName)
    {
        if (! isset($this->versionsLocalCache[$moduleName]) || ! $this->versionsLocalCache[$moduleName]) {
            $moduleVersionsJson = $this->cache->load(self::CACHE_IDENTIFIER);
            if ($moduleVersionsJson) {
                $moduleVersions = (array) json_decode($moduleVersionsJson, true);
            } else {
                $moduleVersions = [];
            }

            if (! isset($moduleVersions[$moduleName])) {
                $modulePathName = str_replace('_', '/', $moduleName);
                $this->versionsLocalCache[$moduleName] = '';

                $composerFilePaths = $this->files->getComposerFiles(
                    \Magento\Framework\Component\ComponentRegistrar::MODULE
                );

                $version = $this->getModuleVersionFromAppCode($composerFilePaths, $modulePathName);
                if ($version) {
                    $this->versionsLocalCache[$moduleName] = $version;
                } else {
                    $versions = $this->getModulesVersionFromVendor($composerFilePaths, 'plumrocket');
                    $this->versionsLocalCache = array_merge($this->versionsLocalCache, $versions);
                }

                $moduleVersions = array_merge($moduleVersions, $this->versionsLocalCache);

                $this->cache->save(
                    json_encode($moduleVersions),
                    self::CACHE_IDENTIFIER,
                    [\Magento\Framework\App\Config::CACHE_TAG]
                );
            }

            $this->versionsLocalCache = $moduleVersions;
        }

        return $this->versionsLocalCache[$moduleName];
    }

    /**
     * @param array  $composerFilePaths
     * @param string $modulePathName
     * @return mixed|string
     */
    private function getModuleVersionFromAppCode(array $composerFilePaths, $modulePathName)
    {
        foreach ($composerFilePaths as $path => $absolutePath) {
            if (false !== strpos($path, "code/$modulePathName/composer.json")) {
                return $this->extractDataFromComposerJson($path)['version'];
            }
        }

        return '';
    }

    /**
     * @param array  $composerFilePaths
     * @param string $vendorName
     * @return mixed|string
     */
    private function getModulesVersionFromVendor(array $composerFilePaths, $vendorName)
    {
        $versions = [];
        foreach ($composerFilePaths as $path => $absolutePath) {
            if (false !== strpos($path, $vendorName)) {
                $data = $this->extractDataFromComposerJson($path);
                if ($data['version']) {
                    $versions[$data['name']] = $data['version'];
                }
            }
        }

        return $versions;
    }

    /**
     * @param string $path
     * @return array
     */
    public function extractDataFromComposerJson($path)
    {
        if (0 === strpos(trim($path, '/'), 'app')
            || 0 === strpos(trim($path, '/'), 'vendor')
        ) {
            $path = BP . DIRECTORY_SEPARATOR . trim($path, '/');
        }

        $content = file_get_contents($path);
        $result = [
            'version' => '',
            'name' => '',
        ];

        if ($content) {
            $jsonContent = json_decode($content, true);
            if (isset($jsonContent['version']) && ! empty($jsonContent['version'])) {
                $result['version'] = $jsonContent['version'];
            }
            if (isset($jsonContent['autoload']['psr-4']) && ! empty($jsonContent['autoload']['psr-4'])) {
                $directoryPath = trim(array_keys($jsonContent['autoload']['psr-4'])[0], '\\');
                $result['name'] = str_replace('\\', '_', $directoryPath);
            }
        }

        return $result;
    }
}
