<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Aws\S3\S3Client;
use Doctrine\Common\Cache\Cache;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;
use League\Flysystem\Util;

class BunnyCdnAdapter implements AdapterInterface
{
    /** @var Cache */
    private $cache;

    /** @var bool */
    private $useGarbage;

    /** @var bool */
    private $neverDelete;

    /** @var AdapterInterface|null */
    private $replication;

    /** @var AbstractAdapter */
    private $s3adapter;

    /** @var string */
    private $subfolder = '';

    public function __construct(array $config, Cache $cache, string $version)
    {
        if (isset($config['subfolder'])) {
            $this->subfolder = $config['subfolder']. '/';
        }

        $this->cache = $cache;
        $this->useGarbage = !empty($config['useGarbage']);
        $this->neverDelete = !empty($config['neverDelete']);

        //backward compatibility
        if (isset($config['apiUrl']) && !isset($config['endpoint'])) {
            $urlParse = parse_url($config['apiUrl']);

            $config['endpoint'] = $urlParse['scheme'] . '://' . $urlParse['host'];
            $parts = explode('/', $urlParse['path']);
            $parts = array_filter($parts);
            $config['storageName'] = $parts[1];

            if (count($parts) > 1) {
                $this->subfolder = implode('/', array_slice($parts, 1)) . '/';
            }
        }

        $s3client = new S3Client([
            'version' => 'latest',
            'region'  => '',
            'endpoint' => $config['endpoint'] . '/',
            'signature_version' => 'v4',
            'mup_threshold' => 99999999,
            'credentials' => [
                'key'    => $config['storageName'],
                'secret' => $config['apiKey'],
            ],
        ]);

        $this->s3adapter = new AwsS3Adapter($s3client, '');

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
    public function write($path, $contents, Config $config)
    {
        $path = $this->subfolder . $path;

        $stream = tmpfile();
        fwrite($stream, $contents);
        rewind($stream);
        $result = $this->writeStream($path, $stream, $config);

        if ($result === false) {
            return false;
        }

        $result['contents'] = $contents;

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
        $path = $this->subfolder . $path;
        $this->garbage($path);

        $filesize = (int) fstat($resource)['size'];

        $currentTime = time();

        $this->s3adapter->writeStream($path, $resource, $config);

        $result = $this->getCached($path);

        if (!isset($result[$path])) {
            $result[$path] = true;
            $this->cache->save($this->getCacheKey($path), $result);
        }

        if ($this->replication) {
            $this->replication->writeStream($path, $resource, $config);
        }

        return [
            'type' => 'file',
            'path' => $path,
            'timestamp' => $currentTime,
            'size' => $filesize,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            'mimetype' => Util::guessMimeType($path, ''),
        ];
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
    public function update($path, $contents, Config $config)
    {
        $path = $this->subfolder . $path;
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
        $path = $this->subfolder . $path;
        $this->delete($path);

        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newPath
     */
    public function rename($path, $newPath): bool
    {
        $path = $this->subfolder . $path;
        $newPath = $this->subfolder . $newPath;

        if ($content = $this->read($path)) {
            $this->write($newPath, $content['contents'], new Config());
            $this->delete($path);

            return true;
        }

        return false;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newPath
     */
    public function copy($path, $newPath): bool
    {
        $path = $this->subfolder . $path;
        $newPath = $this->subfolder . $newPath;

        if ($content = $this->read($path)) {
            $this->write($newPath, $content['contents'], new Config()); //TODO: check config
            return true;
        }

        return false;
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

        $path = $this->subfolder . $path;

        $this->garbage($path);

        $result = $this->s3adapter->delete($path);

        if ($result === false) {
            return false;
        }

        $this->removeFromCache($path);

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
        $dirname = $this->subfolder . $dirname;
        return $this->delete($dirname);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        return [];
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        return [];
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     */
    public function has($path): bool
    {
        $path = $this->subfolder . $path;

        /*
         * If path contains '?', it's variable thumbnail. So always correct.
         */
        if (mb_strpos($path, '?') !== false) {
            return true;
        }

        $result = $this->getCached($path);

        if (isset($result[$path]) && $result[$path]) {
            return true;
        }

        if ($result[$path] = (bool) $this->getSize($path)) {
            $this->cache->save($this->getCacheKey($path), $result);

            return true;
        }

        return false;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $path = $this->subfolder . $path;

        if (!$object = $this->readStream($path)) {
            return false;
        }
        $object['contents'] = stream_get_contents($object['stream']);
        fclose($object['stream']);
        unset($object['stream']);

        return $object;
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $path = $this->subfolder . $path;
        return $this->s3adapter->readStream($path);
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool   $recursive
     */
    public function listContents($directory = '', $recursive = false): array
    {
        $directory = $this->subfolder . $directory;
        return $this->s3adapter->listContents($directory, $recursive);
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        $path = $this->subfolder . $path;
        return $this->s3adapter->getMetadata($path);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        $path = $this->subfolder . $path;
        return $this->s3adapter->getSize($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $path = $this->subfolder . $path;
        return $this->s3adapter->getMimetype($path);
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        $path = $this->subfolder . $path;
        return $this->s3adapter->getTimestamp($path);
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        $path = $this->subfolder . $path;
        return [
            'path' => $path,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ];
    }

    private function removeFromCache(string $path): void
    {
        $path = $this->subfolder . $path;
        $result = $this->getCached($path);

        if (isset($result[$path])) {
            unset($result[$path]);
            $this->cache->save($this->getCacheKey($path), $result);
        }
    }

    private function getCacheKey(string $path): string
    {
        return md5($path)[0];
    }

    private function getCached(string $path): array
    {
        $cacheId = $this->getCacheKey($path);

        $result = $this->cache->fetch($cacheId);

        if ($result) {
            return $result;
        }

        return [];
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
}
