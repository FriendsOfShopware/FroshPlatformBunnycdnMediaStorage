<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\UnableToDeleteFile;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion;

class Shopware6BunnyCdnAdapter extends BunnyCDNAdapter
{
    private readonly bool $neverDelete;

    public function __construct(AdapterConfig $config)
    {
        $this->neverDelete = $config->isNeverDelete();

        $region = BunnyCDNRegion::FALKENSTEIN;
        preg_match('/http(s):\/\/(.*).storage.bunnycdn.com/', $config->getEndpoint(), $matches);
        if (\count($matches) === 3) {
            $region = $matches[2];
        }

        $client = new BunnyCDNClient(
            $config->getStorageName(),
            $config->getApiKey(),
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
        // if path is empty or ends with /, it's a directory.
        if (empty($path) || \str_ends_with($path, '/')) {
            throw UnableToDeleteFile::atLocation($path, 'Deletion of directories prevented.');
        }

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
        if ($this->neverDelete) {
            return;
        }

        parent::deleteDirectory($path);
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
