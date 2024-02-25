<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Shopware\Core\Framework\Struct\Struct;

class AdapterConfig extends Struct
{
    protected string $endpoint;

    protected string $storageName;

    protected string $apiKey;

    protected string $replicationRoot = '';

    protected string $subfolder = '';

    protected bool $useGarbage = false;

    protected bool $neverDelete = false;

    /**
     * This is just a helper to convert old config to new config
     */
    public function setApiUrl(string $apiUrl): void
    {
        if (isset($this->endpoint)) {
            return;
        }

        $urlParse = parse_url($apiUrl);

        $this->setEndpoint(($urlParse['scheme'] ?? 'https') . '://' . ($urlParse['host'] ?? ''));
        $parts = explode('/', ($urlParse['path'] ?? ''));
        $parts = array_filter($parts);
        $this->setStorageName($parts[1] ?? '');

        if (\count($parts) > 1) {
            $this->setSubfolder(implode('/', \array_slice($parts, 1)));
        }
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = rtrim($endpoint, '/');
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function setStorageName(string $storageName): void
    {
        $this->storageName = $storageName;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getReplicationRoot(): string
    {
        return $this->replicationRoot;
    }

    public function setReplicationRoot(string $replicationRoot): void
    {
        $this->replicationRoot = $replicationRoot;
    }

    public function isUseGarbage(): bool
    {
        return $this->useGarbage;
    }

    public function setUseGarbage(bool $useGarbage): void
    {
        $this->useGarbage = $useGarbage;
    }

    public function isNeverDelete(): bool
    {
        return $this->neverDelete;
    }

    public function setNeverDelete(bool $neverDelete): void
    {
        $this->neverDelete = $neverDelete;
    }

    public function getSubfolder(): string
    {
        return $this->subfolder;
    }

    public function setSubfolder(string $subfolder): void
    {
        $this->subfolder = \rtrim($subfolder, '/');
    }
}
