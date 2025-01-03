<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Ajgl\Flysystem\Replicate\ReplicateFilesystemAdapter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;
use Shopware\Core\Framework\Adapter\Filesystem\Plugin\WriteBatchInterface;
use Tinect\Flysystem\Garbage\GarbageFilesystemAdapter;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    /**
     * @param array<string, string|bool|int> $config
     */
    public function create(array $config): FilesystemAdapter
    {
        $adapterConfig = new AdapterConfig();
        $adapterConfig->assign($config);

        $adapter = $this->getBasicAdapter($adapterConfig);

        if (!empty($adapterConfig->isUseGarbage())) {
            $adapter = new GarbageFilesystemAdapter($adapter);
        }

        if (!empty($adapterConfig->getRoot())) {
            $adapter = new PathPrefixedAdapter($adapter, $adapterConfig->getRoot());
        }

        if (!empty($adapterConfig->getReplicationRoot())) {
            $adapter = new ReplicateFilesystemAdapter($adapter, new LocalFilesystemAdapter($adapterConfig->getReplicationRoot()));
        }

        return $adapter;
    }

    private function getBasicAdapter(AdapterConfig $adapterConfig): FilesystemAdapter
    {
        if (\interface_exists(WriteBatchInterface::class)) {
            return new Shopware6BunnyCdnWriteBatchAdapter($adapterConfig);
        }

        return new Shopware6BunnyCdnAdapter($adapterConfig);
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }
}
