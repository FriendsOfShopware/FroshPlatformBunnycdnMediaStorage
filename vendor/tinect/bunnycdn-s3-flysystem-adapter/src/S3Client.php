<?php declare(strict_types=1);

namespace Tinect\Flysystem\BunnyCDN;

class S3Client extends \Aws\S3\S3Client
{
    public function upload(
        $bucket,
        $key,
        $body,
        $acl = 'public',
        array $options = []
    ) {
        $options['mup_threshold'] = PHP_INT_MAX;
        parent::upload($bucket, $key, $body, $acl, $options);
    }
}