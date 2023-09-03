<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Yaml\Yaml;

class ConfigUpdater
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly string $configPath
    ) {
    }

    /**
     * @param array<string, string|bool|int> $config
     */
    public function update(array $config = []): void
    {
        $data = [];
        $pluginConfig = $this->systemConfigService->get(FroshPlatformBunnycdnMediaStorage::CONFIG_KEY);

        if (\is_array($pluginConfig)) {
            $pluginConfig = array_merge($pluginConfig, $config);
        } else {
            $pluginConfig = $config;
        }

        if (empty($pluginConfig['FilesystemPublic'])
            && empty($pluginConfig['FilesystemSitemap'])
            && empty($pluginConfig['FilesystemTheme'])
            && empty($pluginConfig['FilesystemAsset'])) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);
            }

            return;
        }

        if (!isset($pluginConfig['CdnUrl'],
            $pluginConfig['CdnHostname'],
            $pluginConfig['StorageName'],
            $pluginConfig['ApiKey'])) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);
            }

            return;
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
            'url' => $defaultUrl,
            'config' => [
                'root' => '%kernel.project_dir%/public',
            ],
        ];

        $filesystemData = [
            'public' => $filesystemDefaultConfig,
            'sitemap' => $filesystemDefaultConfig,
            'theme' => $filesystemDefaultConfig,
            'asset' => $filesystemDefaultConfig,
        ];

        foreach ($filesystemData as $type => &$filesystem) {
            $filesystem = $filesystemDefaultConfig;

            if (!empty($pluginConfig['Filesystem' . ucfirst($type)])) {
                $filesystem = $filesystemBunnyCdnConfig;
            }

            if (!empty($pluginConfig['Filesystem' . ucfirst($type) . 'Url'])) {
                $filesystem['url'] = $pluginConfig['Filesystem' . ucfirst($type) . 'Url'];
            }
        }
        unset($filesystem);

        $data['shopware'] = [
            'cdn' => ['url' => $pluginConfig['CdnUrl']],
            'filesystem' => $filesystemData,
        ];

        file_put_contents($this->configPath, Yaml::dump($data));
    }

    private function cleanupCdnSubFolder(string $cdnSubfolder): string
    {
        if (rtrim($cdnSubfolder, '/') !== '') {
            return $cdnSubfolder;
        }

        return '';
    }
}
