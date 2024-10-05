<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Tests\Unit\Adapter;

use Ajgl\Flysystem\Replicate\ReplicateFilesystemAdapter;
use Frosh\BunnycdnMediaStorage\Adapter\BunnyCdnFactory;
use Frosh\BunnycdnMediaStorage\Adapter\Shopware6BunnyCdnAdapter;
use Frosh\BunnycdnMediaStorage\Adapter\Shopware6BunnyCdnWriteBatchAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Filesystem\Plugin\WriteBatchInterface;
use Tinect\Flysystem\Garbage\GarbageFilesystemAdapter;

class BunnyCdnFactoryTest extends TestCase
{
    public function testFactoryCreatesBunnyCdnAdapter(): void
    {
        $factory = new BunnyCdnFactory();
        $config = ['endpoint' => 'https://storage.bunnycdn.com', 'apiKey' => 'a', 'storageName' => 'a', 'useGarbage' => false];
        $adapter = $factory->create($config);

        static::assertInstanceOf(Shopware6BunnyCdnAdapter::class, $adapter);
    }

    public function testFactoryCreatesGarbageFilesystemAdapter(): void
    {
        $factory = new BunnyCdnFactory();
        $config = ['endpoint' => 'https://storage.bunnycdn.com', 'apiKey' => 'a', 'storageName' => 'a', 'useGarbage' => true];
        $adapter = $factory->create($config);

        static::assertInstanceOf(GarbageFilesystemAdapter::class, $adapter);
    }

    public function testFactoryCreatesPathPrefixedAdapter(): void
    {
        $factory = new BunnyCdnFactory();
        $config = ['endpoint' => 'https://storage.bunnycdn.com', 'apiKey' => 'a', 'storageName' => 'a', 'root' => 'root'];
        $adapter = $factory->create($config);

        static::assertInstanceOf(PathPrefixedAdapter::class, $adapter);
    }

    public function testFactoryCreatesReplicateFilesystemAdapter(): void
    {
        $factory = new BunnyCdnFactory();
        $config = ['endpoint' => 'https://storage.bunnycdn.com', 'apiKey' => 'a', 'storageName' => 'a', 'replicationRoot' => 'replicationRoot'];
        $adapter = $factory->create($config);

        static::assertInstanceOf(ReplicateFilesystemAdapter::class, $adapter);
    }

    public function testFactoryCreatesWriteBatchAdapter(): void
    {
        if (!\interface_exists(WriteBatchInterface::class)) {
            static::markTestSkipped('WriteBatchInterface not found');
        }

        $factory = new BunnyCdnFactory();
        $config = ['endpoint' => 'https://storage.bunnycdn.com', 'apiKey' => 'a', 'storageName' => 'a', 'useGarbage' => false];
        $adapter = $factory->create($config);

        static::assertInstanceOf(WriteBatchInterface::class, $adapter);
    }

    public function testFactoryReturnsBunnycdnType(): void
    {
        $factory = new BunnyCdnFactory();

        static::assertEquals('bunnycdn', $factory->getType());
    }
}
