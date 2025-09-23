<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CdnHealthCheckService
{
    private AdapterInterface $cache;
    private LoggerInterface $logger;
    private string $cdnUrl;
    private bool $fallbackEnabled;
    private const CACHE_KEY = 'bunnycdn.health_check.status';
    private const CACHE_LIFETIME = 60; // 60 Sekunden

    public function __construct(
        AdapterInterface $systemCache,
        LoggerInterface $logger,
        string $cdnUrl,
        ?bool $fallbackEnabled = false
    ) {
        $this->cache = $systemCache;
        $this->logger = $logger;
        $this->cdnUrl = rtrim($cdnUrl, '/') . '/';
        $this->fallbackEnabled = $fallbackEnabled ?? false;
    }

    public function isCdnAvailable(): bool
    {
        // Wenn das Feature deaktiviert ist, immer true zurückgeben, um den Fallback zu verhindern.
        if (!$this->fallbackEnabled) {
            return true;
        }

        $cacheItem = $this->cache->getItem(self::CACHE_KEY);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $isAvailable = $this->performHealthCheck();

        $cacheItem->set($isAvailable);
        $cacheItem->expiresAfter(self::CACHE_LIFETIME);
        $this->cache->save($cacheItem);

        return $isAvailable;
    }

    private function performHealthCheck(): bool
    {
        if (empty($this->cdnUrl) || $this->cdnUrl === '/') {
            return false;
        }

        try {
            $client = new Client(['timeout' => 3, 'connect_timeout' => 2]);
            $response = $client->head($this->cdnUrl);

            $isSuccess = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
            if (!$isSuccess) {
                 $this->logger->warning('CDN health check failed with status code: ' . $response->getStatusCode(), ['url' => $this->cdnUrl]);
            }
            return $isSuccess;
        } catch (RequestException $e) {
            $this->logger->warning('CDN health check failed with exception: ' . $e->getMessage(), ['url' => $this->cdnUrl]);
            return false;
        }
    }

    public function getCdnBaseUrl(): string
    {
        return $this->cdnUrl;
    }
}