<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    public function create(array $config): FilesystemAdapter
    {
        $config['subfolder'] ??= '';

        //backward compatibility
        if (isset($config['apiUrl']) && !isset($config['endpoint'])) {
            $urlParse = parse_url($config['apiUrl']);

            $config['endpoint'] = ($urlParse['scheme'] ?? 'https') . '://' . ($urlParse['host'] ?? '');
            $parts = explode('/', ($urlParse['path'] ?? ''));
            $parts = array_filter($parts);
            $config['storageName'] = $parts[1] ?? '';

            if (count($parts) > 1) {
                $config['subfolder'] = implode('/', array_slice($parts, 1));
            }
        }

        $config['subfolder'] = \rtrim($config['subfolder'], '/');

        $adapter = new Shopware6BunnyCdnAdapter($config);

        if (!empty($config['subfolder'])) {
            $adapter = new PathPrefixedAdapter($adapter, $config['subfolder']);
        }

        return $adapter;
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }
}
