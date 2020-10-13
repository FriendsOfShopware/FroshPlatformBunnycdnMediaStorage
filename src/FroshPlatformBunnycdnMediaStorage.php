<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage;

use Frosh\BunnycdnMediaStorage\DependencyInjection\AddConfigPath;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class FroshPlatformBunnycdnMediaStorage extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $configPath = $this->getPath(). '/Resources/config/services.yml';
        $container->setParameter('frosh_bunnycdn_media_storage.config_path', $configPath);

        parent::build($container);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $configPath = $this->container->getParameter('frosh_bunnycdn_media_storage.config_path');
        if (file_exists($configPath)) {
            unlink($configPath);
        }
        parent::deactivate($deactivateContext);
    }
}
