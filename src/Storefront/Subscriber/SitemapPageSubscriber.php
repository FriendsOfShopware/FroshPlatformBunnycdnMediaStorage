<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Storefront\Subscriber;

use Shopware\Storefront\Page\Sitemap\SitemapPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SitemapPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var string|null
     */
    private $cdnUrl;

    public function __construct(?string $cdnUrl)
    {
        $this->cdnUrl = $cdnUrl;
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
            if (mb_strpos($sitemap->getFileName(), $this->cdnUrl) !== false) {
                continue;
            }

            if (mb_strpos($sitemap->getFileName(), 'https://') === 0
                || mb_strpos($sitemap->getFileName(), 'http://') === 0) {
                continue;
            }

            $sitemap->setFileName($this->cdnUrl . '/' . $sitemap->getFileName());
        }
    }
}
