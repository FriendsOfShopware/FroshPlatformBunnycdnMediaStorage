<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;

class BunnyCdnAdapter extends AwsS3Adapter
{
    /** @var bool */
    private $useGarbage;

    /** @var bool */
    private $neverDelete;

    /** @var AdapterInterface|null */
    private $replication;

    public function __construct(array $config)
    {
        $subfolder = '';
        if (isset($config['subfolder'])) {
            $subfolder = $config['subfolder']. '/';
        }

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
                $subfolder = implode('/', array_slice($parts, 1)) . '/';
            }
        }

        $s3client = new S3Client([
            'version' => 'latest',
            'region'  => '',
            'endpoint' => $config['endpoint'] . '/',
            'use_path_style_endpoint' => true,
            'signature_version' => 'v4',
            'mup_threshold' => 99999999,
            'credentials' => [
                'key'    => $config['storageName'],
                'secret' => $config['apiKey'],
            ],
        ]);

        parent::__construct($s3client, $config['storageName'], $subfolder);

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
        $this->garbage($path);

        $result = parent::writeStream($path, $resource, $config);

        if ($this->replication) {
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
    public function update($path, $contents, Config $config)
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
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     */
    public function rename($path, $newpath): bool
    {
        if ($content = $this->read($path)) {
            $this->write($newpath, $content['contents'], new Config());
            $this->delete($path);

            return true;
        }

        return false;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     */
    public function copy($path, $newpath): bool
    {
        if ($content = $this->read($path)) {
            $this->write($newpath, $content['contents'], new Config());
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
     * Create a directory.
     *
     * @param string $dirname directory name
     *
     * @return array
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
     * @return array file meta data
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
        /*
         * If path contains '?', it's variable thumbnail. So always correct.
         */
        if (mb_strpos($path, '?') !== false) {
            return true;
        }

        return (bool) $this->getSize($path);
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
        if (!$object = $this->readStream($path)) {
            return false;
        }
        $object['contents'] = stream_get_contents($object['stream']);
        fclose($object['stream']);
        unset($object['stream']);

        return $object;
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array
     */
    public function getVisibility($path)
    {
        return [
            'path' => $path,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ];
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
