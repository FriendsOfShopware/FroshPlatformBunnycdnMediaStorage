<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Ajgl\Flysystem\Replicate\ReplicateFilesystemAdapter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    public function create(array $config): FilesystemAdapter
    {
        $config['subfolder'] ??= '';
        $this->convertOldConfig($config);

        $config['subfolder'] = \rtrim($config['subfolder'], '/');

        $adapter = new Shopware6BunnyCdnAdapter($config);

        if (!empty($config['subfolder'])) {
            $adapter = new PathPrefixedAdapter($adapter, $config['subfolder']);
        }

        if (!empty($config['replicationRoot'])) {
            $adapter = new ReplicateFilesystemAdapter($adapter, new LocalFilesystemAdapter($config['replicationRoot']));
        }

        return $adapter;
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }

    private function convertOldConfig(array &$config): void
    {
        if (!isset($config['apiUrl']) && isset($config['endpoint'])) {
            return;
        }

        $urlParse = parse_url($config['apiUrl']);

        $config['endpoint'] = ($urlParse['scheme'] ?? 'https') . '://' . ($urlParse['host'] ?? '');
        $parts = explode('/', ($urlParse['path'] ?? ''));
        $parts = array_filter($parts);
        $config['storageName'] = $parts[1] ?? '';

        if (\count($parts) > 1) {
            $config['subfolder'] = implode('/', \array_slice($parts, 1));
        }
    }
}
