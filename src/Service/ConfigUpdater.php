<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
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
        $pluginConfig = $this->systemConfigService->getDomain(FroshPlatformBunnycdnMediaStorage::CONFIG_KEY);

        if (empty($pluginConfig)) {
            $pluginConfig = $config;
        } else {
            $pluginConfig = array_merge($pluginConfig, $config);
        }

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
}
