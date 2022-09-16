# 2.3.1

* Fix: update client to fix sitemaps

# 2.3.0

* Performance: use http-based client

# 2.2.5

* Fix: correct button Test API for newer shopware versions

# 2.2.4

* Fix: do not connect when constructing class

# 2.2.3

* Fix: ignore error thrown by SFTP when its setting visibility

# 2.2.2

* Fix: use SFTP while bunnyCDN killed S3 for the moment

# 2.2.1

* Feat: update config-restriction

# 2.2.0

* Feat: Verwendung des abstrakten Adapters für bunnyCDN

# 2.1.2

* Fix: Support Uploads, die größer als 16MB sind

# 2.1.1

* Fix: Support 6.4.6

# 2.1.0

* Feature: Verwende S3 für die Verbindung mit bunnyCDN
* Feature: Enferne Verwendung von Doctrine Cache

# 2.0.6

* Fix: Plugin-Konfiguration wurde nicht korrekt beim Saleschannel-Wechsel gesperrt

# 2.0.5

* Fix: Testroute für Shopware 6.4 angepasst

# 2.0.4

* Feature: Support für PHP 8
* Feature: Setze Header für Preconnect zu den CDN-Adressen

# 2.0.3

* Behebe doppelte Url in Sitemap

# 2.0.2

* Behebe Fehler beim Button für den API-Test für Shopware 6.3.4

# 2.0.1

* Behebe Fehler beim Holen der Header von BunnyCDN

# 2.0.0

* Erstelle Filesystem-Konfiguration automatisch anhand Plugin-Konfiguration.
* Option zur Nutzung eines Papierkorbs in bunnyCDN hinzugefügt
* Option hinzugefügt, die das Löschen von Dateien unterbindet
* Option hinzugefügt, die es ermöglicht, die Dateien auch lokal zu schreiben
* ACHTUNG: Es müssen bestehende Konfigurationen für vorige Versionen dieses Plugins in der Datei `config/packages/shopware.yml` entfernt und die Plugin-Konfiguratoion gefüllt werden.

# 1.0.11

* Behebe Fehler beim Lesen von Dateien mit speziellen Zeichen im Namen

# 1.0.10

* Behebe falsche Sitemap-Url

# 1.0.9

* Kompatibilität zu 6.3.0.0. Bitte prüfen Sie die Installationsanleitung für notwendige Änderungen an der shopware.yml.

# 1.0.8

* Workaround um klein geschriebene Header von BunnyCDN zu respektieren

# 1.0.7

* Behebe falsche Sitemap-Url

# 1.0.6

* Behebe Problem im SitemapLister

# 1.0.5

* Erster Release im Store
