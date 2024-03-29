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
- Remove any existing configuration regarding filesystem in `config/packages/shopware.yml`
- Set up plugin config

### Notes on Automatic Deployments
The plugin configuration is stored in `var/bunnycdn_config.yml`. You must ensure that this file is shared and available in this location after deployment after you configured the plugin once.

## Suggestions

- [FroshPlatformThumbnailProcessor](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
