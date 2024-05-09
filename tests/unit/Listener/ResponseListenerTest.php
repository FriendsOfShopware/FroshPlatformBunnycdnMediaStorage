<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Tests\Unit\Listener;

use Frosh\BunnycdnMediaStorage\Listener\ResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseListenerTest extends TestCase
{
    public function testOnKernelResponseWithNonMainRequest(): void
    {
        $responseListener = new ResponseListener(null, null, null, null);
        $event = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::SUB_REQUEST, new Response());

        $responseListener->onKernelResponse($event);

        static::assertEmpty($event->getResponse()->headers->all('Link'));
    }

    public function testOnKernelResponseWithBinaryFileResponse(): void
    {
        $responseListener = new ResponseListener('https://publicUrl.com', 'https://sitemapUrl.com', 'https://themeUrl.com', 'https://assetUrl.com');
        $event = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, new BinaryFileResponse(__FILE__));

        $responseListener->onKernelResponse($event);

        static::assertEmpty($event->getResponse()->headers->all('Link'));
    }

    public function testOnKernelResponseWithNonHtmlContentType(): void
    {
        $responseListener = new ResponseListener('https://publicUrl.com', 'https://sitemapUrl.com', 'https://themeUrl.com', 'https://assetUrl.com');
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $event = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, $response);

        $responseListener->onKernelResponse($event);

        static::assertEmpty($event->getResponse()->headers->get('Link'));
    }

    public function testOnKernelResponseWithHtmlContentType(): void
    {
        $responseListener = new ResponseListener('https://publicUrl.com', 'https://sitemapUrl.com', 'https://themeUrl.com', 'https://assetUrl.com');
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $event = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, $response);

        $responseListener->onKernelResponse($event);

        static::assertCount(4, $event->getResponse()->headers->all('Link'));
    }

    public function testOnKernelResponseWithHtmlContentTypeAndOneUrl(): void
    {
        $responseListener = new ResponseListener('https://publicUrl.com', 'https://publicUrl.com', null, null);
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $event = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, $response);

        $responseListener->onKernelResponse($event);

        static::assertCount(1, $event->getResponse()->headers->all('Link'));
    }

    public function testOnKernelResponseWithInvalidUrls(): void
    {
        $responseListener = new ResponseListener('publicUrl.com', '', null, null);
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $event = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MAIN_REQUEST, $response);

        $responseListener->onKernelResponse($event);

        static::assertEmpty($event->getResponse()->headers->all('Link'));
    }
}
