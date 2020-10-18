<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Doctrine\Common\Cache\Cache;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;

class BunnyCdnAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /** @var Cache */
    private $cache;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var bool
     */
    private $useGarbage;

    public function __construct($config, Cache $cache, string $version)
    {
        $this->apiUrl = $config['apiUrl'];
        $this->apiKey = $config['apiKey'];
        $this->cache = $cache;
        $this->userAgent = 'Shopware ' . $version;
        $this->useGarbage = (bool) $config['useGarbage'];
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

        $filesize = (int) fstat($resource)['size'];
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_URL => $this->apiUrl . $this->urlencodePath($path),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 60000,
                CURLOPT_FOLLOWLOCATION => 0,
                CURLOPT_FAILONERROR => 0,
                CURLOPT_INFILE => $resource,
                CURLOPT_INFILESIZE => $filesize,
                CURLOPT_POST => 1,
                CURLOPT_UPLOAD => 1,
                CURLOPT_HTTPHEADER => [
                    'AccessKey: ' . $this->apiKey,
                ],
            ]
        );

        // Send the request
        $currentTime = time();
        curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ((int) $http_code !== 201) {
            return false;
        }

        $result = $this->getCached($path);

        if (!isset($result[$path])) {
            $result[$path] = true;
            $this->cache->save($this->getCacheKey($path), $result);
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
     * @param string $newPath
     */
    public function rename($path, $newPath): bool
    {
        if ($content = $this->read($path)) {
            $this->garbage($path);

            $this->write($newPath, $content['contents'], new Config()); //TODO: check config
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
        $this->garbage($path);

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_URL => $this->apiUrl . $path,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_HTTPHEADER => [
                    'Content-Type:application/json',
                    'AccessKey:' . $this->apiKey,
                ],
            ]
        );

        $result = curl_exec($curl);
        $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($result === false || $http_code !== 200) {
            return false;
        }

        $this->removeFromCache($path);

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
        return [
            'type' => 'file',
            'path' => $path,
            'stream' => fopen($this->apiUrl . $this->urlencodePath($path) . '?AccessKey=' . $this->apiKey, 'rb'),
        ];
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool   $recursive
     */
    public function listContents($directory = '', $recursive = false): array
    {
        return $this->getDirContent($directory, $recursive);
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
        $headers = get_headers($this->apiUrl . $this->urlencodePath($path) . '?AccessKey=' . $this->apiKey, 1);
        if (mb_strpos($headers[0], '200') === false) {
            return false;
        }

        $size = (int) $this->getBunnyCdnHeader($headers, 'Content-Length');

        if (!$size) {
            return false;
        }

        return [
            'type' => 'file',
            'path' => $path,
            'timestamp' => (int) strtotime($this->getBunnyCdnHeader($headers, 'Last-Modified')),
            'size' => $size,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            'mimetype' => $this->getBunnyCdnHeader($headers, 'Content-Type'),
        ];
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
        return $this->getMetadata($path);
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
        return $this->getMetadata($path);
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
        return $this->getMetadata($path);
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
        return [
            'path' => $path,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ];
    }

    private function urlencodePath($path): string
    {
        $parts = explode('/', $path);
        foreach ($parts as &$value) {
            $value = rawurlencode($value);
        }
        unset($value);

        return implode('/', $parts);
    }

    private function removeFromCache($path): void
    {
        $result = $this->getCached($path);

        if (isset($result[$path])) {
            unset($result[$path]);
            $this->cache->save($this->getCacheKey($path), $result);
        }
    }

    private function getCacheKey($path): string
    {
        return md5($path)[0];
    }

    private function getCached($path): array
    {
        $cacheId = $this->getCacheKey($path);

        $result = $this->cache->fetch($cacheId);

        if ($result) {
            return $result;
        }

        return [];
    }

    /**
     * @param string $directory
     * @param bool   $recursive
     */
    private function getDirContent($directory, $recursive): array
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => $this->apiUrl . $directory . '/',
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 60000,
                CURLOPT_FOLLOWLOCATION => 0,
                CURLOPT_FAILONERROR => 0,
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_VERBOSE => 0,
                CURLOPT_HTTPHEADER => [
                    'AccessKey: ' . $this->apiKey,
                ],
            ]
        );
        // Send the request
        $response = (string) curl_exec($curl);
        curl_close($curl);
        $result = [];

        foreach (json_decode($response, false) as $content) {
            $result[] = [
                'basename' => $content->ObjectName,
                'path' => $directory . '/' . $content->ObjectName,
                'type' => ($content->IsDirectory ? 'dir' : 'file'),
                'timestamp' => (new \DateTime($content->LastChanged))->getTimestamp(),
            ];

            if ($recursive && $content->IsDirectory) {
                $subContents = $this->getDirContent($directory . '/' . $content->ObjectName, true);
                foreach ($subContents as $subContent) {
                    $result[] = $subContent;
                }
            }
        }

        return $result;
    }

    private function getBunnyCdnHeader(array $headers, string $header)
    {
        if (isset($headers[$header])) {
            return $headers[$header];
        }

        if (isset($headers[mb_strtolower($header)])) {
            return $headers[mb_strtolower($header)];
        }

        return null;
    }

    private function garbage($path): bool
    {
        if (!$this->useGarbage || !$this->has($path)) {
            return false;
        }

        $garbagePath = 'garbage/' . date('Ymd') . $path;

        /* There could be a file on this day */
        if ($this->has($garbagePath)) {
            $garbagePath .= str_replace('.', '', microtime(true));
        }

        $this->copy($path, $garbagePath);

        return true;
    }
}
