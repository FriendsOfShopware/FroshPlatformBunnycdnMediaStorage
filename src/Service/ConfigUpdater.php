<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Yaml\Yaml;

class ConfigUpdater
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly CacheClearer $cacheClearer,
        private readonly string $configPath
    ) {
    }

    public function update(array $config): void
    {
        $data = [];
        $pluginConfig = $this->systemConfigService->get('FroshPlatformBunnycdnMediaStorage.config');
        $pluginConfig = array_merge($pluginConfig, $config);

        if (empty($pluginConfig['FilesystemPublic'])
            && empty($pluginConfig['FilesystemSitemap'])
            && empty($pluginConfig['FilesystemTheme'])
            && empty($pluginConfig['FilesystemAsset'])) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);
                $this->cacheClearer->clearContainerCache();
            }

            return;
        }

        if (!isset($pluginConfig['CdnUrl'],
            $pluginConfig['CdnHostname'],
            $pluginConfig['StorageName'],
            $pluginConfig['ApiKey'])) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);
                $this->cacheClearer->clearContainerCache();
            }

            return;
        }

        $defaultUrl = EnvironmentHelper::getVariable('APP_URL');

        if ($pluginConfig['CdnUrl'] === '') {
            $pluginConfig['CdnUrl'] = $defaultUrl;
        }

        $pluginConfig = $this->convertCdnSubFolder($pluginConfig);

        $filesystemBunnyCdnConfig = [
            'type' => 'bunnycdn',
            'url' => rtrim((string) $pluginConfig['CdnUrl'], '/') . '/' . trim((string) $pluginConfig['CdnSubFolder'], '/'),
            'config' => [
                'endpoint' => rtrim((string) $pluginConfig['CdnHostname'], '/'),
                'storageName' => $pluginConfig['StorageName'],
                'subfolder' => rtrim((string) $pluginConfig['CdnSubFolder'], '/'),
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

        $this->cacheClearer->clearContainerCache();
    }

    private function convertCdnSubFolder(array $pluginConfig): array
    {
        if (!isset($pluginConfig['CdnSubFolder'])) {
            $pluginConfig['CdnSubFolder'] = '';
        } elseif (rtrim($pluginConfig['CdnSubFolder'], '/') === '') {
            $pluginConfig['CdnSubFolder'] = '';
        }

        return $pluginConfig;
    }
}
