<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion;

class Shopware6BunnyCdnAdapter extends BunnyCDNAdapter
{

    private readonly bool $neverDelete;

    /**
     * @param array{
     *     endpoint: string,
     *     storageName: string,
     *     apiKey: string,
     *     neverDelete: bool|int
     * } $config
     */
    public function __construct(array $config)
    {
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
    public function delete($path): void
    {
        if ($this->neverDelete) {
            return;
        }

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
}
