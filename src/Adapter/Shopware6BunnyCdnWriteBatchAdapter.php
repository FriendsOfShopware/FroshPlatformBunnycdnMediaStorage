<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Adapter;

use League\Flysystem\Config;
use PlatformCommunity\Flysystem\BunnyCDN\WriteBatchFile;
use Shopware\Core\Framework\Adapter\Filesystem\Plugin\CopyBatchInput;
use Shopware\Core\Framework\Adapter\Filesystem\Plugin\WriteBatchInterface;

if (!\interface_exists(WriteBatchInterface::class)) {
    class Shopware6BunnyCdnWriteBatchAdapter extends Shopware6BunnyCdnAdapter
    {}
} else {
    class Shopware6BunnyCdnWriteBatchAdapter extends Shopware6BunnyCdnAdapter implements WriteBatchInterface
    {
        public function writeBatch(CopyBatchInput|array|Config ...$input): void
        {
            $files = [];
            $config = new Config();

            foreach ($input as $data) {
                if ($data instanceof CopyBatchInput) {
                    // migrate CopyBatchInput of Shopware to WriteBatchFile
                    foreach ($data->getTargetFiles() as $targetFile) {
                        $sourceFile = $data->getSourceFile();

                        if (\is_string($sourceFile)) {
                            $files[] = new WriteBatchFile($sourceFile, $targetFile);

                            continue;
                        }

                        $metaData = stream_get_meta_data($sourceFile);
                        if (empty($metaData['uri'])) {
                            throw new \InvalidArgumentException('Cannot get source file path from stream.');
                        }

                        $sourcePath = $metaData['uri'];

                        $files[] = new WriteBatchFile($sourcePath, $targetFile);
                    }

                    continue;
                }

                if ($data instanceof Config) {
                    $config = $data;

                    continue;
                }

                if (\is_array($data)) {
                    foreach ($data as $item) {
                        if (!$item instanceof WriteBatchFile) {
                            throw new \InvalidArgumentException('Each value of array must be a WriteBatchFile object.');
                        }

                        $files[] = $item;
                    }
                }
            }

            if (empty($files)) {
                return;
            }

            parent::writeBatch($files, $config);
        }
    }
}