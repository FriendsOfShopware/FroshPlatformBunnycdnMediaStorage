# BunnyCDN Adapter for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md) [![Shopware Store](https://img.shields.io/badge/shopware-store-blue.svg?style=flat-square)](https://store.shopware.com/en/frosh48851065217f/bunnycdn-media-storage-plugin-v3.html)

The BunnyCDN adapter allows you to manage your media files in shopware on a bunnyCDN-Storage.


## Install

### By composer
```
composer require frosh/platform-bunnycdn-media-storage
```
### By zip
download latest release and upload into admin:  
https://github.com/FriendsOfShopware/FroshPlatformBunnycdnMediaStorage/releases/latest/download/FroshPlatformBunnycdnMediaStorage.zip

## Usage
- Upload existing media (optional)
  - by SCP from shell:
    - log via SSH into you webspace and go into your shopware folder
    - run following commands (Replace STORAGEZONENAME) and confirm with the FTP-Password of your storage-zone
      ```
      scp -r ./public/media STORAGEZONENAME@storage.bunnycdn.com:/
      scp -r ./public/thumbnail STORAGEZONENAME@storage.bunnycdn.com:/
      ```
  - Manual by FTP-Client: [see docs at BunnyCDN](https://support.bunnycdn.com/hc/en-us/articles/115003780169-How-to-upload-and-access-files-from-your-Storage-Zone).

- Install and activate the plugin.
- Configure the filesystems in your `config/packages/shopware.yml`. Check the [Shopware documentation](https://developer.shopware.com/docs/guides/hosting/infrastructure/filesystem.html) for additional information.
  - Possible configurations for filesystem type `bunnycdn`:
    - `endpoint`: The endpoint of your storage zone
    - `apiKey`: The FTP Password of your storage zone
    - `storageName`: The name of your storage zone
    - `replicationRoot` (optional): Setting this path will write files also into known local folders. This negates the advantage of saving locally storage. This needs to be an absolute path.
    - `root` (optional): The root/subfolder within your storage zone.
    - `useGarbage` (optional): When set to `true`, deleted, renamed and overwritten files are also saved to a folder named `garbage/[currentDate]/`.
    - `neverDelete` (optional): When set to `true`, deleting files will not use garbage option, too. Attention: This will result in more storage usage and orphaned files in storage.
    
    - Example with filesystems `public` and `sitemap` saved into `bunnycdn`:
      ```yaml
    shopware:
      cdn:
          url: "https://my-really-cool-company.b-cdn.net"
      filesystem:
        public: &bunnycdn
          type: "bunnycdn"
          url: "https://my-really-cool-company.b-cdn.net"
          config:
            endpoint: "https://storage.bunnycdn.com"
            apiKey: "secret-ftp-password"
            storageName: "my-really-cool-company"
            useGarbage: true
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

## Suggestions

- [FroshPlatformThumbnailProcessor](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
