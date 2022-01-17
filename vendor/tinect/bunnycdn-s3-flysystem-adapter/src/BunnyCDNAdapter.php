<?php declare(strict_types=1);

namespace Tinect\Flysystem\BunnyCDN;

use Aws\S3\Exception\S3Exception;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Adapter\Polyfill\StreamedCopyTrait;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class BunnyCDNAdapter extends AwsS3Adapter
{
    use NotSupportingVisibilityTrait;
    use StreamedCopyTrait;

    public function __construct($storageName, $apiKey, $endpoint, $subfolder = '')
    {
        if ($subfolder !== '') {
            $subfolder = rtrim($subfolder, '/') .  '/';
        }

        if (strpos($endpoint, 'http') !== 0) {
            $endpoint = 'https://' . $endpoint;
        }

        $s3client = new S3Client([
            'version' => 'latest',
            'region'  => '',
            'endpoint' => rtrim($endpoint, '/') . '/',
            'use_path_style_endpoint' => true,
            'signature_version' => 'v4',
            'credentials' => [
                'key'    => $storageName,
                'secret' => $apiKey,
            ],
        ]);

        parent::__construct($s3client, $storageName, $subfolder);
    }

    /*
     * we need to catch here while S3 results in Exception when deleting a not existing resource
     */
    public function delete($path): bool
    {
        try {
            return parent::delete($path);
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404) {
                return false;
            }

            throw $e;
        }
    }

    public function deleteDir($path): bool
    {
        return $this->delete(rtrim($path, '/') . '/');
    }
}
