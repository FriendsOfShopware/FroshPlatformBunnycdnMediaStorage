<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Tests\Unit;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FroshPlatformBunnycdnMediaStorageTest extends TestCase
{
    public function testBuild(): void
    {
        $pluginBootstrap = new FroshPlatformBunnycdnMediaStorage(true, __DIR__ . '/../../');

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getParameter')->willReturnMap([
            ['kernel.project_dir', '/var/www/shopware'],
        ]);

        $container->expects(static::once())
            ->method('setParameter')->with('frosh_bunnycdn_media_storage.config_path');

        $pluginBootstrap->build($container);
    }

    public function testExecuteComposerCommands(): void
    {
        $pluginBootstrap = new FroshPlatformBunnycdnMediaStorage(true, __DIR__ . '/../../');
        static::assertTrue($pluginBootstrap->executeComposerCommands());
    }
}
