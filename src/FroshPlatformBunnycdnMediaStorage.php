<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FroshPlatformBunnycdnMediaStorage extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $configPath = $this->getPath() . '/Resources/config/services.yml';
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
