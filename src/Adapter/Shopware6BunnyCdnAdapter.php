<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\Config;
use League\Flysystem\UnableToDeleteFile;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\WriteBatchFile;
use Shopware\Core\Framework\Adapter\Filesystem\Plugin\CopyBatchInput;
use Shopware\Core\Framework\Adapter\Filesystem\Plugin\WriteBatchInterface;

class Shopware6BunnyCdnAdapter extends BunnyCDNAdapter implements WriteBatchInterface
{
    private readonly bool $neverDelete;

    public function __construct(AdapterConfig $config)
    {
        $this->neverDelete = $config->isNeverDelete();

        // url is managed by shop using public_url at filesystem
        parent::__construct($config->getClient());
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function deleteDirectory(string $path): void
    {
        if ($this->neverDelete) {
            return;
        }

        parent::deleteDirectory($path);
    }

    /**
     * {@inheritDoc}
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

    public function writeBatch(CopyBatchInput|array|Config ...$input): void
    {
        $files = [];
        $config = new Config();

        foreach ($input as $data) {
            if ($data instanceof CopyBatchInput) {
                // migrate CopyBatchInput of Shopware to WriteBatchFile
                foreach ($data->getTargetFiles() as $targetFile) {
                    if (\is_resource($data->getSourceFile())) {
                        $sourcePath = stream_get_meta_data($data->getSourceFile())['uri'];
                        $files[] = new WriteBatchFile($sourcePath, $targetFile);

                        continue;
                    }

                    $files[] = new WriteBatchFile($data->getSourceFile(), $targetFile);
                }

                continue;
            }

            if ($data instanceof Config) {
                $config = $data;

                continue;
            }

            if (\is_array($data)) {
                foreach ($data as $item) {
                    if ($item instanceof WriteBatchFile) {
                        $files[] = $item;

                        continue;
                    }

                    throw new \InvalidArgumentException('Each value of array must be a WriteBatchFile object.');
                }

                continue;
            }

            throw new \InvalidArgumentException('Unsupported input type.');
        }

        if (empty($files)) {
            return;
        }

        parent::writeBatch($files, $config);
    }
}
