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

    public function update(): void
    {
        $pluginConfig = $this->systemConfigService->get('FroshPlatformBunnycdnMediaStorage.config');

        if (empty($pluginConfig['FilesystemPublic']) &&
            empty($pluginConfig['FilesystemSitemap']) &&
            empty($pluginConfig['FilesystemTheme']) &&
            empty($pluginConfig['FilesystemAsset'])) {
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
            'url' => $pluginConfig['CdnUrl'],
            'config' => [
                'apiUrl' => rtrim($pluginConfig['CdnHostname'], '/') . '/' . $pluginConfig['StorageName'] . '/'. $pluginConfig['CdnSubFolder'],
                'apiKey' => $pluginConfig['ApiKey'],
            ],
        ];

        $filesystemDefaultConfig = [
            'type' => 'local',
            'url' => $defaultUrl,
            'config' => [
                'root' => "%kernel.project_dir%/public",
            ],
        ];

        $data['shopware'] = [
            'cdn' => ['url' => $this->systemConfigService->get('FroshPlatformBunnycdnMediaStorage.config.CdnUrl')],
            'filesystem' => [
                'public' => !empty($pluginConfig['FilesystemPublic']) ? $filesystemBunnyCdnConfig : $filesystemDefaultConfig,
                'sitemap' => !empty($pluginConfig['FilesystemSitemap']) ? $filesystemBunnyCdnConfig : $filesystemDefaultConfig,
                'theme' => !empty($pluginConfig['FilesystemTheme']) ? $filesystemBunnyCdnConfig : $filesystemDefaultConfig,
                'asset' => !empty($pluginConfig['FilesystemAsset']) ? $filesystemBunnyCdnConfig : $filesystemDefaultConfig,
            ]
        ];

        file_put_contents($this->configPath, Yaml::dump($data));
    }

    private function endsWith(
        $haystack,
        $needle
    ): bool {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
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
