<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FroshPlatformBunnycdnMediaStorage extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $configPath = $container->getParameter('kernel.project_dir') . '/' .$container->getParameter('frosh_bunnycdn_media_storage.config_path');

        if (file_exists($configPath)) {
            $pathInfo = pathinfo($configPath);
            $loader = new YamlFileLoader($container, new FileLocator($pathInfo['dirname']));
            $loader->load($pathInfo['basename']);
        }

    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $configPath = $this->container->getParameter('kernel.project_dir') . '/' .$this->container->getParameter('frosh_bunnycdn_media_storage.config_path');
        if (file_exists($configPath)) {
            unlink($configPath);
        }
        parent::deactivate($deactivateContext);
    }
}
