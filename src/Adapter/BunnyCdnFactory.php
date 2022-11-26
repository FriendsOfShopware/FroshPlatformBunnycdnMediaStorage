<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\FilesystemAdapter;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    public function create(array $config): FilesystemAdapter
    {
        $config['subfolder'] = $config['subfolder'] ?? '';

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

        if (!empty($config['subfolder'])) {
            //TODO: boot prefixAdapter
        }

        return new Shopware6BunnyCdnAdapter($config);
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }
}
