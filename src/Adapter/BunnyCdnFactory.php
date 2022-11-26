<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\FilesystemAdapter;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    public function create(array $config): FilesystemAdapter
    {
        return new Shopware6BunnyCdnAdapter($config);
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }
}
