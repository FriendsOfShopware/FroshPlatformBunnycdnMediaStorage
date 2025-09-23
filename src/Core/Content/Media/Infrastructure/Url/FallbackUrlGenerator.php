<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Core\Content\Media\Infrastructure\Url;

use Frosh\BunnycdnMediaStorage\Service\CdnHealthCheckService;
use Shopware\Core\Content\Media\Infrastructure\Url\UrlGeneratorInterface;
use Shopware\Core\Framework\Context;

class FallbackUrlGenerator implements UrlGeneratorInterface
{
    private UrlGeneratorInterface $decorated;
    private CdnHealthCheckService $healthCheckService;
    private string $localPublicUrl;

    public function __construct(
        UrlGeneratorInterface $decorated,
        CdnHealthCheckService $healthCheckService,
        string $localPublicUrl
    ) {
        $this->decorated = $decorated;
        $this->healthCheckService = $healthCheckService;
        $this->localPublicUrl = rtrim($localPublicUrl, '/');
    }

    public function generate(array $paths, Context $context): array
    {
        $urls = $this->decorated->generate($paths, $context);

        if ($this->healthCheckService->isCdnAvailable()) {
            return $urls;
        }

        $cdnBaseUrl = rtrim($this->healthCheckService->getCdnBaseUrl(), '/');

        $rewrittenUrls = [];
        foreach ($urls as $key => $url) {
            // Ersetze die CDN-Basis-URL nur, wenn sie am Anfang der URL steht
            if (str_starts_with($url, $cdnBaseUrl)) {
                 $rewrittenUrls[$key] = $this->localPublicUrl . substr($url, strlen($cdnBaseUrl));
            } else {
                 $rewrittenUrls[$key] = $url;
            }
        }

        return $rewrittenUrls;
    }
}