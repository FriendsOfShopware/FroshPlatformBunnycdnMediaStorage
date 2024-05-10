<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Tests\Unit\Adapter;

use Frosh\BunnycdnMediaStorage\Adapter\AdapterConfig;
use Frosh\BunnycdnMediaStorage\Adapter\Shopware6BunnyCdnAdapter;
use League\Flysystem\UnableToDeleteFile;
use PHPUnit\Framework\TestCase;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;

class Shopware6BunnyCdnAdapterTest extends TestCase
{
    public function testDeletionPreventedForDirectories(): void
    {
        $this->expectException(UnableToDeleteFile::class);

        $client = $this->createMock(BunnyCDNClient::class);
        $client->expects($this->never())->method('delete');

        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $adapter->delete('/');
    }

    public function testDeletionSkippedWhenNeverDeleteIsTrue(): void
    {
        $client = $this->createMock(BunnyCDNClient::class);
        $client->expects($this->never())->method('delete');

        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);
        $config->method('isNeverDelete')->willReturn(true);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $adapter->delete('file.txt');
    }

    public function testDeletionOfDirectorySkippedWhenNeverDeleteIsTrue(): void
    {
        $client = $this->createMock(BunnyCDNClient::class);
        $client->expects($this->never())->method('delete');

        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);
        $config->method('isNeverDelete')->willReturn(true);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $adapter->deleteDirectory('directory');
    }

    public function testFileExistsReturnsTrueForVariableThumbnail(): void
    {
        $client = $this->createMock(BunnyCDNClient::class);
        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $this->assertTrue($adapter->fileExists('thumbnail?variable'));
    }

    public function testDeleteIsCalled(): void
    {
        $client = $this->createMock(BunnyCDNClient::class);
        $client->expects($this->once())->method('delete');

        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $adapter->delete('file.txt');
    }

    public function testDeleteDirectoryIsCalled(): void
    {
        $client = $this->createMock(BunnyCDNClient::class);
        $client->expects($this->once())->method('delete');

        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $adapter->deleteDirectory('file.txt');
    }

    public function testFileExistsIsCalled(): void
    {
        $client = $this->createMock(BunnyCDNClient::class);
        $client->expects($this->once())->method('list');

        $config = $this->createMock(AdapterConfig::class);
        $config->method('getClient')->willReturn($client);

        $adapter = new Shopware6BunnyCdnAdapter($config);

        $adapter->fileExists('file.txt');
    }
}