# BunnyCDN Adapter for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

The BunnyCDN adapter allows you to manage your media files in shopware on a bunnyCDN-Storage.


## Install

Download the plugin from the release page and enable it in shopware.

## Usage since plugin version 2.0.0
- Upload existing media (optional)
  - by SCP from shell:
    - log via SSH into you webspace and go into your shopware folder
    - run `scp -r ./public/media STORAGEZONENAME@storage.bunnycdn.com:/` (Replace STORAGEZONENAME) and confirm with the FTP-Passwort of your storage-zone
- Manual by FTP-Client: [see docs at BunnyCDN](https://support.bunnycdn.com/hc/en-us/articles/115003780169-How-to-upload-and-access-files-from-your-Storage-Zone).
- Remove any existing configuration for previous versions in `config/packages/shopware.yml`
- Set up plugin config

## Usage for older plugin version
[see old Readme](README.1.0.11.md)

## Suggestions

- [FroshPlatformThumbnailProcessor](https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
