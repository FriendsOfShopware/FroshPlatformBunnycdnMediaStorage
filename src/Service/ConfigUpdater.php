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

    /**
     * @var array
     */
    private $filesystemCurrentConfig;

    public function __construct(
        SystemConfigService $systemConfigService,
        string $configPath,
        array $filesystemPublic,
        array $filesystemSitemap,
        array $filesystemTheme,
        array $filesystemAsset
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->configPath = $configPath;
        $this->filesystemCurrentConfig = [
            'public' => $filesystemPublic,
            'sitemap' => $filesystemSitemap,
            'theme' => $filesystemTheme,
            'asset' => $filesystemAsset,
        ];
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
            ],
        ];

        $filesystemDefaultConfig = [
            'type' => 'local',
            'url' => $defaultUrl,
            'config' => [
                'root' => '%kernel.project_dir%/public',
            ],
        ];

        foreach ($this->filesystemCurrentConfig as $type => &$filesystem) {
            if (!empty($pluginConfig['Filesystem' . ucfirst($type)])) {
                $filesystem = $filesystemBunnyCdnConfig;
            } elseif ($filesystem['type'] === $filesystemBunnyCdnConfig['type']) {
                //we reset to default if it was type bunnycdn
                $filesystem = $filesystemDefaultConfig;
            }

            if (!empty($pluginConfig['Filesystem' . ucfirst($type) . 'Url'])) {
                $filesystem['url'] = $pluginConfig['Filesystem' . ucfirst($type) . 'Url'];
            }

            //url is mandatory
            if (empty($filesystem['url'])) {
                $filesystem['url'] = $defaultUrl;
            }
        }
        unset($filesystem);

        $data['shopware'] = [
            'cdn' => ['url' => $this->systemConfigService->get('FroshPlatformBunnycdnMediaStorage.config.CdnUrl')],
            'filesystem' => $this->filesystemCurrentConfig,
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
