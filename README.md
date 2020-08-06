# BunnyCDN Adapter for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

The BunnyCDN adapter allows you to manage your media files in shopware on a bunnyCDN-Storage.


## Install

Download the plugin from the release page and enable it in shopware.

## Usage
- Upload your existing media-folder using ftp to [BunnyCDN](https://support.bunnycdn.com/hc/en-us/articles/115003780169-How-to-upload-and-access-files-from-your-Storage-Zone).
- Update your `config/packages/shopware.yml` and fill in your own values
    ### Shopware 6.3
    
    SW6.3 inherits config of all new filesystems from public. So we have to set every filesystem on its own.  
    Here is a example to use Storage for media and sitemap. Due to performance-Issues, we don't recommend using CDN-Storage for theme or asset.

    ```yaml
    shopware:
      cdn:
          url: "https://example.b-cdn.net"
      filesystem:
        public: &bunnycdn
          type: "bunnycdn"
          url: "https://example.b-cdn.net"
          config:
            apiUrl: "https://storage.bunnycdn.com/example/"
            apiKey: "secret-ftp-password"
        sitemap:
          <<: *bunnycdn
        theme:
          type: "local"
          url: ""
          config:
            root: "%kernel.project_dir%/public"
        asset:
          type: "local"
          url: ""
          config:
            root: "%kernel.project_dir%/public"
    ```
    
    ### Shopware 6.2.x

    ```yaml
    shopware:
        filesystem:
            public:
                type: "bunnycdn"
                config:
                    apiUrl: "https://storage.bunnycdn.com/example/"
                    apiKey: "secret-ftp-password"
        cdn:
            url: "https://example.b-cdn.net"
    ```
    Due to performance problems and missing implementation in core, you shouldn't transfer the theme to bunnycdn. To achive this, you have to add this, to the `shopware.yml`-file:

    #### Shopware 6.2 - 6.2.3
    
    ```yaml
    parameters:
      filesystem_local_public:
          type: 'local'
          config:
              root: '%kernel.project_dir%/public'
    
    services:
      filesystem.local.public:
          class: 'League\Flysystem\FilesystemInterface'
          factory: ['@Shopware\Core\Framework\Adapter\Filesystem\FilesystemFactory', 'factory']
          arguments:
              - '%filesystem_local_public%'
      Shopware\Storefront\Theme\ThemeCompiler:
          arguments:
              - '@filesystem.local.public'
              - '@shopware.filesystem.temp'
              - '@Shopware\Storefront\Theme\ThemeFileResolver'
              - '%kernel.cache_dir%'
              - '%kernel.debug%'
              - '@Symfony\Component\EventDispatcher\EventDispatcherInterface'
              - '@Shopware\Storefront\Theme\ThemeFileImporter'
      Shopware\Core\Framework\Plugin\Util\AssetService:
          arguments:
              - '@filesystem.local.public'
              - '@kernel'
              - '@Shopware\Core\Framework\Plugin\KernelPluginCollection'
    
    ```

    #### Shopware 6.2.3
    
    ```yaml
    parameters:
      filesystem_local_public:
          type: 'local'
          config:
              root: '%kernel.project_dir%/public'
    
    services:
      filesystem.local.public:
          class: 'League\Flysystem\FilesystemInterface'
          factory: ['@Shopware\Core\Framework\Adapter\Filesystem\FilesystemFactory', 'factory']
          arguments:
              - '%filesystem_local_public%'
      Shopware\Storefront\Theme\ThemeCompiler:
          arguments:
              - '@filesystem.local.public'
              - '@shopware.filesystem.temp'
              - '@Shopware\Storefront\Theme\ThemeFileResolver'
              - '%kernel.cache_dir%'
              - '%kernel.debug%'
              - '@Symfony\Component\EventDispatcher\EventDispatcherInterface'
              - '@Shopware\Storefront\Theme\ThemeFileImporter'
              - '@media.repository'
      Shopware\Core\Framework\Plugin\Util\AssetService:
          arguments:
              - '@filesystem.local.public'
              - '@kernel'
              - '@Shopware\Core\Framework\Plugin\KernelPluginCollection'
    
    ```

## Suggestions

- [FroshPlatformThumbnailProcessor](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
