<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage;

use Shopware\Core\Framework\Struct\Struct;

class PluginConfig extends Struct
{
    public const CONFIG_KEY = 'FroshPlatformBunnycdnMediaStorage.config';
    public const SUPPORTED_FILESYSTEM_TYPES = [
        'public',
        'sitemap',
        'theme',
        'asset',
    ];

    public string $CdnUrl = '';

    public string $StorageName = '';

    public string $CdnHostname = '';

    public string $ApiKey = '';

    public string $CdnSubFolder = '';

    public bool $FilesystemPublic = true;

    public string $FilesystemPublicUrl = '';

    public bool $FilesystemSitemap = true;

    public string $FilesystemSitemapUrl = '';

    public bool $FilesystemTheme = false;

    public string $FilesystemThemeUrl = '';

    public bool $FilesystemAsset = false;

    public string $FilesystemAssetUrl = '';

    public bool $useGarbage = false;

    public bool $neverDelete = false;

    public bool $replicateLocal = false;
}
