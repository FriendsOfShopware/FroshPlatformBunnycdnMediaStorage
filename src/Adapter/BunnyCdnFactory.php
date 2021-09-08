<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Doctrine\Common\Cache\Cache;
use League\Flysystem\AdapterInterface;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;

class BunnyCdnFactory implements AdapterFactoryInterface
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function create(array $config): AdapterInterface
    {
        return new BunnyCdnAdapter($config, $this->cache);
    }

    public function getType(): string
    {
        return 'bunnycdn';
    }
}
