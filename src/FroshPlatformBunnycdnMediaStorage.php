<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FroshPlatformBunnycdnMediaStorage extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $configPath = $this->getConfigPath($container);
        $container->setParameter('frosh_bunnycdn_media_storage.config_path', $configPath);

        if (file_exists($configPath)) {
            $pathInfo = pathinfo($configPath);
            $loader = new YamlFileLoader($container, new FileLocator($pathInfo['dirname']));
            $loader->load($pathInfo['basename']);
        }

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

    private function getConfigPath($container = null): string
    {
        if (!$container && $this->container) {
            $container = $this->container;
        }

        return $container->getParameter('kernel.project_dir') . '/var/bunnycdn_config.yml';
    }
}
