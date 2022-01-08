<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\AdapterInterface;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    public function create(array $config): AdapterInterface
    {
        return new Shopware6BunnyCdnAdapter($config);
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }
}
