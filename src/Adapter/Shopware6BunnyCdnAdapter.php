<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use League\Flysystem\Config;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion;

FroshPlatformBunnycdnMediaStorage::classLoader();

class Shopware6BunnyCdnAdapter extends BunnyCDNAdapter
{
    private readonly bool $useGarbage;

    private readonly bool $neverDelete;

    public function __construct(array $config)
    {
        $this->useGarbage = !empty($config['useGarbage']);
        $this->neverDelete = !empty($config['neverDelete']);

        $region = BunnyCDNRegion::FALKENSTEIN;
        preg_match('/http(s):\/\/(.*).storage.bunnycdn.com/', $config['endpoint'], $matches);
        if (\count($matches) === 3) {
            $region = $matches[2];
        }

        $client = new BunnyCDNClient(
            $config['storageName'],
            $config['apiKey'],
            $region,
        );

        //url is managed by shop using public_url at filesystem
        parent::__construct($client);
    }

    /**
     * @inheritDoc
     */
    public function write($path, $contents, Config $config): void
    {
        $this->garbage($path);

        parent::write($path, $contents, $config);
    }

    /**
     * @inheritDoc
     */
    public function writeStream($path, $contents, Config $config): void
    {
        $this->garbage($path);
        
        parent::writeStream($path, $contents, $config);
    }

    /**
     * @inheritDoc
     */
    public function delete($path): void
    {
        if ($this->neverDelete) {
            return;
        }

        $this->garbage($path);

        parent::delete($path);
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory(string $path): void
    {
        $this->delete(rtrim($path, '/') . '/');
    }

    /**
     * @inheritDoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->garbage($path);

        parent::createDirectory($path, $config);
    }

    /**
     * @inheritDoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->garbage($source);

        parent::move($source, $destination, $config);
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        /*
         * If path contains '?', it's variable thumbnail. So always correct.
         */
        if (str_contains($path, '?')) {
            return true;
        }

        return parent::fileExists($path);
    }

    private function garbage(string $path): void
    {
        if (!$this->useGarbage) {
            return;
        }

        if (!$this->fileExists($path)) {
            return;
        }

        $garbagePath = 'garbage/' . date('Ymd') . '/' . $path;

        /* There could be a file on this day */
        if ($this->fileExists($garbagePath)) {
            $garbagePath .= str_replace('.', '', (string) microtime(true));
        }

        $this->copy($path, $garbagePath, new Config());
    }

    private function ensureSeekable($resource, string $path)
    {
        if (stream_get_meta_data($resource)['seekable'] && rewind($resource)) {
            return $resource;
        }

        return $this->readStream($path);
    }
}
