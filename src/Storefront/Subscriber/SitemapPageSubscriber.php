<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Storefront\Subscriber;

use Shopware\Storefront\Page\Sitemap\SitemapPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SitemapPageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ?string $cdnUrl)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPageLoadedEvent::class => 'changeSitemapPageData',
        ];
    }

    public function changeSitemapPageData(SitemapPageLoadedEvent $event): void
    {
        if (!$this->cdnUrl) {
            return;
        }

        foreach ($event->getPage()->getSitemaps() as $sitemap) {
            if (str_contains($sitemap->getFileName(), $this->cdnUrl)) {
                continue;
            }

            if (str_starts_with($sitemap->getFileName(), 'https://')
                || str_starts_with($sitemap->getFileName(), 'http://')) {
                continue;
            }

            $sitemap->setFileName($this->cdnUrl . '/' . $sitemap->getFileName());
        }
    }
}
