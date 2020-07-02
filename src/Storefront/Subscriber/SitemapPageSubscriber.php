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
            $sitemap->setFileName($this->cdnUrl . '/' . $sitemap->getFileName());
        }
    }
}
