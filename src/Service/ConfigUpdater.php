<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Yaml\Yaml;

class ConfigUpdater
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var string
     */
    private $configPath;

    public function __construct(SystemConfigService $systemConfigService, string $configPath)
    {
        $this->systemConfigService = $systemConfigService;
        $this->configPath = $configPath;
    }

    public function update(array $config): void
    {
        $pluginConfig = $this->systemConfigService->get('FroshPlatformBunnycdnMediaStorage.config');
        $pluginConfig = array_merge($pluginConfig, $config);

        if (empty($pluginConfig['FilesystemPublic'])
            && empty($pluginConfig['FilesystemSitemap'])
            && empty($pluginConfig['FilesystemTheme'])
            && empty($pluginConfig['FilesystemAsset'])) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);
            }

            return;
        }

        if (!isset($pluginConfig['CdnHostname'], $pluginConfig['StorageName'])) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);
            }

            return;
        }

        $defaultUrl = getenv('APP_URL');

        $pluginConfig = $this->convertCdnSubFolder($pluginConfig);

        $filesystemBunnyCdnConfig = [
            'type' => 'bunnycdn',
            'url' => rtrim($pluginConfig['CdnUrl'], '/') . '/' . $pluginConfig['CdnSubFolder'],
            'config' => [
                'apiUrl' => rtrim($pluginConfig['CdnHostname'], '/') . '/' . $pluginConfig['StorageName'] . '/' . $pluginConfig['CdnSubFolder'],
                'apiKey' => $pluginConfig['ApiKey'],
                'useGarbage' => $pluginConfig['useGarbage'],
            ],
        ];

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
            'cdn' => ['url' => $this->systemConfigService->get('FroshPlatformBunnycdnMediaStorage.config.CdnUrl')],
            'filesystem' => $filesystemData,
        ];

        file_put_contents($this->configPath, Yaml::dump($data));
    }

    private function endsWith(
        $haystack,
        $needle
    ): bool {
        return substr_compare($haystack, $needle, -mb_strlen($needle)) === 0;
    }

    private function convertCdnSubFolder(array $pluginConfig): array
    {
        if (!isset($pluginConfig['CdnSubFolder'])) {
            $pluginConfig['CdnSubFolder'] = '';
        } elseif (rtrim($pluginConfig['CdnSubFolder'], '/') === '') {
            $pluginConfig['CdnSubFolder'] = '';
        } elseif (!$this->endsWith($pluginConfig['CdnSubFolder'], '/')) {
            $pluginConfig['CdnSubFolder'] .= '/';
        }

        return $pluginConfig;
    }
}
