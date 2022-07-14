# 2.2.3

* Fix: ignore error thrown by SFTP when its setting visibility

# 2.2.2

* Fix: use SFTP while bunnyCDN killed S3 for the moment

# 2.2.1

* Feat: update config-restriction

# 2.2.0

* Feat: Use abstract adapter for bunnyCDN

# 2.1.2

* Fix: Support uploads bigger than 16MB

# 2.1.1

* Fix: Support 6.4.6

# 2.1.0

* Feature: use S3 for connection with bunnyCDN
* Feature: Remove usage of Doctrine Cache

# 2.0.6

* Fix: Plugin configuration hasn't been disabled proper when switched saleschannel

# 2.0.5

* Fix: Adjust test route for shopware 6.4

# 2.0.4

* Feature: Support for PHP 8
* Feature: Set Header to Preconnect to CDN URLs

# 2.0.3

* fix duplicated url in sitemap

# 2.0.2

* Fix error for API-Test button for shopware 6.3.4

# 2.0.1

* Fix error while requesting headers from bunnyCDN

# 2.0.0

* Create filesystem configuration automatically based on plugin configuration.
* Added option to use a recycle bin in bunnyCDN
* Added option to prevent files from being deleted
* Added option to replicate files locally
* ATTENTION: You have to remove any existing configuration for previous versions of this plugin in the file `config/packages/shopware.yml` and the plugin configuration has to be filled.

# 1.0.11

* Fix error while reading files named with foreign characters

# 1.0.10

* fix wrong sitemap-url

# 1.0.9

* Compatiblity to 6.3.0.0. Please check the installation manual for changes in your shopware.yml.

# 1.0.8

* add workaround to respect lowercase headers of BunnyCDN

# 1.0.7

* fix wrong sitemap-url

# 1.0.6

* fix problem with SitemapLister

# 1.0.5

* First release in Store
