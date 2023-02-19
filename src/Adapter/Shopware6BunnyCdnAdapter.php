<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion;

FroshPlatformBunnycdnMediaStorage::classLoader();

class Shopware6BunnyCdnAdapter extends BunnyCDNAdapter
{
    /** @var bool */
    private $useGarbage;

    /** @var bool */
    private $neverDelete;

    /** @var AdapterInterface|null */
    private $replication;

    public function __construct(array $config)
    {
        $subfolder = $config['subfolder'] ?? '';

        $this->useGarbage = !empty($config['useGarbage']);
        $this->neverDelete = !empty($config['neverDelete']);

        //backward compatibility
        if (isset($config['apiUrl']) && !isset($config['endpoint'])) {
            $urlParse = parse_url($config['apiUrl']);

            $config['endpoint'] = ($urlParse['scheme'] ?? 'https') . '://' . ($urlParse['host'] ?? '');
            $parts = explode('/', ($urlParse['path'] ?? ''));
            $parts = array_filter($parts);
            $config['storageName'] = $parts[1] ?? '';

            if (count($parts) > 1) {
                $subfolder = implode('/', array_slice($parts, 1));
            }
        }

        $region = BunnyCDNRegion::FALKENSTEIN;
        preg_match('/http(s):\/\/(.*).storage.bunnycdn.com/', $config['endpoint'], $matches);
        if (count($matches) === 3) {
            $region = $matches[2];
        }

        $client = new BunnyCDNClient(
            $config['storageName'],
            $config['apiKey'],
            $region,
        );

        parent::__construct($client, '', \rtrim($subfolder, '/'));

        if (!empty($config['replicationRoot'])) {
            $this->replication = new Local($config['replicationRoot']);
        }
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config): bool
    {
        $this->garbage($path);

        $result = parent::write($path, $contents, $config);

        if ($result !== false && $this->replication) {
            $this->replication->write($path, $contents, $config);
        }

        return $result;
    }

    /**
     * Write a new file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        $this->garbage($path);

        $result = parent::writeStream($path, $resource, $config);

        if ($this->replication) {
            $this->ensureSeekable($resource, $path);
            $this->replication->writeStream($path, $resource, $config);
        }

        return $result;
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config): bool
    {
        $this->delete($path);

        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        $this->delete($path);

        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     */
    public function delete($path): bool
    {
        if ($this->neverDelete) {
            return true;
        }

        $this->garbage($path);

        $result = parent::delete($path);

        if ($result === false) {
            return false;
        }

        if ($this->replication && $this->replication->has($path)) {
            $this->replication->delete($path);
        }

        return true;
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     */
    public function deleteDir($dirname): bool
    {
        return $this->delete($dirname);
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     */
    public function has($path): bool
    {
        /*
         * If path contains '?', it's variable thumbnail. So always correct.
         */
        if (mb_strpos($path, '?') !== false) {
            return true;
        }

        return parent::has($path);
    }

    private function garbage(string $path): void
    {
        if (!$this->useGarbage || !$this->has($path)) {
            return;
        }

        $garbagePath = 'garbage/' . date('Ymd') . '/' . $path;

        /* There could be a file on this day */
        if ($this->has($garbagePath)) {
            $garbagePath .= str_replace('.', '', (string) microtime(true));
        }

        $this->copy($path, $garbagePath);
    }

    private function ensureSeekable($resource, string $path)
    {
        if (stream_get_meta_data($resource)['seekable'] && rewind($resource)) {
            return $resource;
        }

        return $this->readStream($path);
    }
}
