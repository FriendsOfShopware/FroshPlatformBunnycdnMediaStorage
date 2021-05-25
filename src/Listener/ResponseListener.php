<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Listener;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseListener
{
    /** @var array */
    private $urls = [];

    public function __construct(
        ?string $publicUrl,
        ?string $sitemapUrl,
        ?string $themeUrl,
        ?string $assetUrl
    ) {
        if ($publicUrl) {
            $this->urls[] = $this->parseDomain($publicUrl);
        }

        if ($sitemapUrl) {
            $this->urls[] = $this->parseDomain($sitemapUrl);
        }

        if ($themeUrl) {
            $this->urls[] = $this->parseDomain($themeUrl);
        }

        if ($assetUrl) {
            $this->urls[] = $this->parseDomain($assetUrl);
        }

        $this->urls = array_unique(array_filter($this->urls));
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();

        if ($response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse) {
            return;
        }

        if (strpos($response->headers->get('Content-Type', ''), 'text/html') === false) {
            return;
        }

        foreach ($this->urls as $url) {
            $response->headers->add(['Link' => '<' . $url . '>; rel=preconnect']);
        }
    }

    private function parseDomain(string $url): string
    {
        $urlParse = parse_url($url);

        if (isset($urlParse['scheme'])) {
            $urlParse['scheme'] = 'https';
        }

        return $urlParse['scheme'] . '://' . $urlParse['host'];
    }
}