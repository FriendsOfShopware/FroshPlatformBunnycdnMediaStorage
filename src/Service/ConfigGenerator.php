<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Shopware\Core\DevOps\Environment\EnvironmentHelper;

class ConfigGenerator
{
    private const FILESYSTEM_TYPES = [
        'public',
        'sitemap',
        'theme',
        'asset',
    ];

    /**
     * @param array<string, string|bool|int> $pluginConfig
     */
    public function generate(array $pluginConfig = []): ?array
    {
        $data = [];

        if ($this->isValidConfig($pluginConfig) === false) {
            return null;
        }

        $defaultUrl = EnvironmentHelper::getVariable('APP_URL');

        if (empty($pluginConfig['CdnUrl']) || !\is_string($pluginConfig['CdnUrl'])) {
            $pluginConfig['CdnUrl'] = (string) $defaultUrl;
        }

        $pluginConfig['CdnSubFolder'] = $this->cleanupCdnSubFolder($pluginConfig['CdnSubFolder'] ?? '');

        $filesystemBunnyCdnConfig = [
            'type' => 'bunnycdn',
            'url' => rtrim($pluginConfig['CdnUrl'], '/') . '/' . trim($pluginConfig['CdnSubFolder'], '/'),
            'config' => [
                'endpoint' => rtrim((string) $pluginConfig['CdnHostname'], '/'),
                'storageName' => $pluginConfig['StorageName'],
                'subfolder' => rtrim($pluginConfig['CdnSubFolder'], '/'),
                'apiKey' => $pluginConfig['ApiKey'],
                'useGarbage' => $pluginConfig['useGarbage'] ?? false,
                'neverDelete' => $pluginConfig['neverDelete'] ?? false,
            ],
        ];

        if (!empty($pluginConfig['replicateLocal'])) {
            $filesystemBunnyCdnConfig['config']['replicationRoot'] = '%kernel.project_dir%/public';
        }

        $filesystemDefaultConfig = [
            'type' => 'local',
            'url' => '',
            'config' => [
                'root' => '%kernel.project_dir%/public',
            ],
        ];

        $filesystemData = [];

        foreach (self::FILESYSTEM_TYPES as $type) {
            if (!empty($pluginConfig['Filesystem' . ucfirst($type)])) {
                $filesystemData[$type] = $filesystemBunnyCdnConfig;
            } else {
                $filesystemData[$type] = $filesystemDefaultConfig;
            }

            if (!empty($pluginConfig['Filesystem' . ucfirst($type) . 'Url'])) {
                $filesystemData[$type]['url'] = $pluginConfig['Filesystem' . ucfirst($type) . 'Url'];
            }
        }

        $data['shopware'] = [
            'cdn' => ['url' => $pluginConfig['CdnUrl']],
            'filesystem' => $filesystemData,
        ];

        return $data;
    }

    private function cleanupCdnSubFolder(string $cdnSubFolder): string
    {
        if (rtrim($cdnSubFolder, '/') !== '') {
            return $cdnSubFolder;
        }

        return '';
    }

    private function isValidConfig(array $pluginConfig): bool
    {
        if (empty($pluginConfig)) {
            return false;
        }

        if (empty($pluginConfig['CdnUrl'])) {
            return false;
        }

        if (empty($pluginConfig['CdnHostname'])) {
            return false;
        }

        if (empty($pluginConfig['StorageName'])) {
            return false;
        }

        if (empty($pluginConfig['ApiKey'])) {
            return false;
        }

        if ($this->shouldTransferAnyFilesystem($pluginConfig) === false) {
            return false;
        }

        return true;
    }

    private function shouldTransferAnyFilesystem(array $pluginConfig): bool
    {
        foreach (self::FILESYSTEM_TYPES as $type) {
            if (!empty($pluginConfig['Filesystem' . ucfirst($type)])) {
                return true;
            }
        }

        return false;
    }
}
