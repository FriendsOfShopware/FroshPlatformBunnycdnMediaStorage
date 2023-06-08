<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FroshPlatformBunnycdnMediaStorage extends Plugin
{
    public const CONFIG_KEY = 'FroshPlatformBunnycdnMediaStorage.config';

    public function executeComposerCommands(): bool
    {
        return true;
    }

    public function build(ContainerBuilder $container): void
    {
        $configPath = $this->getConfigPath($container);
        $container->setParameter('frosh_bunnycdn_media_storage.config_path', $configPath);

        $this->loadConfig($configPath, $container);

        parent::build($container);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            parent::uninstall($uninstallContext);

            return;
        }

        $configPath = $this->getConfigPath();
        if (file_exists($configPath)) {
            unlink($configPath);
        }
        parent::uninstall($uninstallContext);
    }

    private function getConfigPath(?ContainerBuilder $container = null): string
    {
        if ($container === null) {
            $container = $this->container;
        }

        $projectDir = (string) $container->getParameter('kernel.project_dir');

        return $projectDir . '/var/bunnycdn_config.yml';
    }

    private function loadConfig(string $configPath, ContainerBuilder $container): void
    {
        if (!\is_file($configPath)) {
            return;
        }

        $pathInfo = pathinfo($configPath);

        if (empty($pathInfo['dirname']) || empty($pathInfo['basename'])) {
            return;
        }

        $loader = new YamlFileLoader($container, new FileLocator($pathInfo['dirname']));
        $loader->load($pathInfo['basename']);
    }
}
