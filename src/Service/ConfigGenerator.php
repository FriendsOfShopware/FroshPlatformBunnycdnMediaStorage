<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Frosh\BunnycdnMediaStorage\PluginConfig;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;

class ConfigGenerator
{
    public function generate(PluginConfig $pluginConfig): ?array
    {
        if ($this->isValidConfig($pluginConfig) === false) {
            return null;
        }

        $defaultUrl = EnvironmentHelper::getVariable('APP_URL');

        if (empty($pluginConfig->CdnUrl)) {
            $pluginConfig->CdnUrl = (string) $defaultUrl;
        }

        $pluginConfig->CdnSubFolder = $this->cleanupCdnSubFolder($pluginConfig->CdnSubFolder);

        $filesystemBunnyCdnConfig = [
            'type' => 'bunnycdn',
            'url' => \sprintf(
                '%s/%s',
                rtrim($pluginConfig->CdnUrl, '/'),
                trim($pluginConfig->CdnSubFolder, '/')
            ),
            'config' => [
                'endpoint' => rtrim($pluginConfig->CdnHostname, '/'),
                'storageName' => $pluginConfig->StorageName,
                'root' => rtrim($pluginConfig->CdnSubFolder, '/'),
                'apiKey' => $pluginConfig->ApiKey,
                'useGarbage' => $pluginConfig->useGarbage,
                'neverDelete' => $pluginConfig->neverDelete,
            ],
        ];

        if (!empty($pluginConfig->replicateLocal)) {
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

        foreach (PluginConfig::SUPPORTED_FILESYSTEM_TYPES as $type) {
            $fileSystemActive = $pluginConfig->{'Filesystem' . \ucfirst($type)};

            if ($fileSystemActive) {
                $filesystemData[$type] = $filesystemBunnyCdnConfig;
            } else {
                $filesystemData[$type] = $filesystemDefaultConfig;
            }

            $fileSystemUrl = $pluginConfig->{'Filesystem' . \ucfirst($type) . 'Url'};
            if (!empty($fileSystemUrl)) {
                $filesystemData[$type]['url'] = $fileSystemUrl;
            }
        }

        $data = [];
        $data['shopware'] = [
            'cdn' => ['url' => $pluginConfig->CdnUrl],
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

    private function isValidConfig(PluginConfig $pluginConfig): bool
    {
        if (empty($pluginConfig->getVars())) {
            return false;
        }

        if (empty($pluginConfig->CdnUrl)) {
            return false;
        }

        if (empty($pluginConfig->CdnHostname)) {
            return false;
        }

        if (empty($pluginConfig->StorageName)) {
            return false;
        }

        if (empty($pluginConfig->ApiKey)) {
            return false;
        }

        if ($this->shouldTransferAnyFilesystem($pluginConfig) === false) {
            return false;
        }

        return true;
    }

    private function shouldTransferAnyFilesystem(PluginConfig $pluginConfig): bool
    {
        foreach (PluginConfig::SUPPORTED_FILESYSTEM_TYPES as $type) {
            if ($pluginConfig->{'Filesystem' . \ucfirst($type)}) {
                return true;
            }
        }

        return false;
    }
}
