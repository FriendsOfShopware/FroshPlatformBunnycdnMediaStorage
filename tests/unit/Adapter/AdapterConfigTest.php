<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Tests\Unit\Adapter;

use Frosh\BunnycdnMediaStorage\Adapter\AdapterConfig;
use PHPUnit\Framework\TestCase;

class AdapterConfigTest extends TestCase
{
    public function testPartialAssignment(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'endpoint' => 'https://storage.bunnycdn.com/',
            'storageName' => 'storagezone',
            'apiKey' => '1-2-4-8',
        ]);

        static::assertEquals('https://storage.bunnycdn.com', $adapterConfig->getEndpoint());
        static::assertEquals('storagezone', $adapterConfig->getStorageName());
        static::assertEquals('1-2-4-8', $adapterConfig->getApiKey());
        static::assertEquals('', $adapterConfig->getReplicationRoot());
        static::assertEquals('', $adapterConfig->getRoot());
        static::assertFalse($adapterConfig->isUseGarbage());
        static::assertFalse($adapterConfig->isNeverDelete());
    }

    public function testAssignment(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'endpoint' => 'https://storage.bunnycdn.com/',
            'storageName' => 'storagezone',
            'apiKey' => '1-2-4-8',
            'replicationRoot' => '/var/www/shopware/public',
            'root' => 'subfolder',
            'useGarbage' => true,
            'neverDelete' => true,
        ]);

        static::assertEquals('https://storage.bunnycdn.com', $adapterConfig->getEndpoint());
        static::assertEquals('storagezone', $adapterConfig->getStorageName());
        static::assertEquals('1-2-4-8', $adapterConfig->getApiKey());
        static::assertEquals('/var/www/shopware/public', $adapterConfig->getReplicationRoot());
        static::assertEquals('subfolder', $adapterConfig->getRoot());
        static::assertTrue($adapterConfig->isUseGarbage());
        static::assertTrue($adapterConfig->isNeverDelete());
    }

    public function testBoolConversion(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'useGarbage' => 0,
            'neverDelete' => 1,
        ]);

        static::assertFalse($adapterConfig->isUseGarbage());
        static::assertTrue($adapterConfig->isNeverDelete());

        $adapterConfig->assign([
            'useGarbage' => '1',
            'neverDelete' => '0',
        ]);

        static::assertTrue($adapterConfig->isUseGarbage());
        static::assertFalse($adapterConfig->isNeverDelete());

        $adapterConfig->assign([
            'useGarbage' => '',
            'neverDelete' => ' ',
        ]);

        static::assertFalse($adapterConfig->isUseGarbage());
        static::assertTrue($adapterConfig->isNeverDelete());
    }

    public function testSubfolderResultsInRootWithoutSlash(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'subfolder' => '/path/',
        ]);

        static::assertEquals('/path', $adapterConfig->getRoot());
    }

    public function testRootWithoutSlash(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'root' => '/path/',
        ]);

        static::assertEquals('/path', $adapterConfig->getRoot());
    }

    public function testApiUrlConvertedToNewConfig(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'apiUrl' => 'https://storage.bunnycdn.com/storagezone//subfolder',
        ]);

        static::assertEquals('https://storage.bunnycdn.com', $adapterConfig->getEndpoint());
        static::assertEquals('storagezone', $adapterConfig->getStorageName());
        static::assertEquals('subfolder', $adapterConfig->getRoot());
    }

    public function testApiUrlNotConvertedToNewConfigWithDefinedEndpoint(): void
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign([
            'apiUrl' => 'https://storage.bunnycdn.com/storagezone//subfolder',
            'endpoint' => 'https://any.storage.bunnycdn.com',
        ]);

        static::assertEquals('https://any.storage.bunnycdn.com', $adapterConfig->getEndpoint());

        $this->expectExceptionMessage('Typed property Frosh\BunnycdnMediaStorage\Adapter\AdapterConfig::$storageName must not be accessed before initialization');
        static::assertNotEquals('storagezone', $adapterConfig->getStorageName());

        static::assertEquals('', $adapterConfig->getRoot());
    }
}
