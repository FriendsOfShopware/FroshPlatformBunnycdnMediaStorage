<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use Shopware\Core\Framework\Struct\Struct;

class AdapterConfig extends Struct
{
    protected string $endpoint;

    protected string $storageName;

    protected string $apiKey;

    protected string $replicationRoot = '';

    protected string $root = '';

    protected bool $useGarbage = false;

    protected bool $neverDelete = false;

    public function assign(array $options): AdapterConfig
    {
        parent::assign($options);

        foreach ($options as $key => $value) {
            $method = 'set' . \ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * This is for backwards compatibility with old plugin versions
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
            $this->setRoot(implode('/', \array_slice($parts, 1)));
        }
    }

    /**
     * This is for backwards compatibility with old plugin versions
     */
    public function setSubfolder(string $subfolder): void
    {
        $this->setRoot($subfolder);
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

    public function setUseGarbage(bool|int|string $useGarbage): void
    {
        $this->useGarbage = !empty($useGarbage);
    }

    public function isNeverDelete(): bool
    {
        return $this->neverDelete;
    }

    public function setNeverDelete(bool|int|string $neverDelete): void
    {
        $this->neverDelete = !empty($neverDelete);
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function setRoot(string $root): void
    {
        $this->root = \rtrim($root, '/');
    }
}
