# BunnyCDN Adapter for Shopware 6

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

The BunnyCDN adapter allows you to manage your media files in shopware on a bunnyCDN-Storage.


## Install

Download the plugin from the release page and enable it in shopware.

## Usage
- Upload your media-folder using ftp to [BunnyCDN](https://support.bunnycdn.com/hc/en-us/articles/115003780169-How-to-upload-and-access-files-from-your-Storage-Zone).
- Update your `config/packages/shopware.yml` and fill in your own values
    
    ```
    shopware:
        filesystem:
            public:
                type: "bunnycdn"
                config:
                    apiUrl: "https://storage.bunnycdn.com/example/"
                    apiKey: "secret-api-key"
        cdn:
            url: "https://example.b-cdn.net"
    ```

## Migration

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
