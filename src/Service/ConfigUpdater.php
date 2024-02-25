<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use Frosh\BunnycdnMediaStorage\PluginConfig;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Yaml\Yaml;

class ConfigUpdater
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly CacheClearer $cacheClearer,
        private readonly ConfigGenerator $configGenerator,
        private readonly string $configPath
    ) {
    }

    /**
     * @param array<string, string|bool|int> $config
     */
    public function update(array $config = []): void
    {
        $configKey = FroshPlatformBunnycdnMediaStorage::CONFIG_KEY;
        $pluginConfig = $this->getPluginConfig($config, $configKey);

        $data = $this->configGenerator->generate($pluginConfig);

        if ($data === null) {
            if (file_exists($this->configPath)) {
                unlink($this->configPath);

                $this->cacheClearer->clearContainerCache();
            }

            return;
        }

        file_put_contents($this->configPath, Yaml::dump($data));
        $this->cacheClearer->clearContainerCache();
    }

    /**
     * @param array<string, string|bool|int> $config
     */
    private function getPluginConfig(array $config, string $configKey): PluginConfig
    {
        $pluginConfig = new PluginConfig();

        $pluginDomainConfig = $this->systemConfigService->getDomain(FroshPlatformBunnycdnMediaStorage::CONFIG_KEY);
        foreach ($pluginDomainConfig as $key => $value) {
            $pluginConfig->assign([
                \str_replace($configKey . '.', '', $key) => $value,
            ]);
        }

        foreach ($config as $key => $value) {
            $pluginConfig->assign([
                \str_replace($configKey . '.', '', $key) => $value,
            ]);
        }

        return $pluginConfig;
    }
}
