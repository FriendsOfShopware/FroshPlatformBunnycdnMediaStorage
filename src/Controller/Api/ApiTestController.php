<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Controller\Api;

use Doctrine\Common\Cache\FilesystemCache;
use Frosh\BunnycdnMediaStorage\Adapter\BunnyCdnAdapter;
use League\Flysystem\Config;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Util\Random;

/**
 * @RouteScope(scopes={"administration"})
 */
class ApiTestController
{
    /**
     * @var FilesystemCache
     */
    private $cache;

    public function __construct(FilesystemCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route(path="/api/v{version}/_action/bunnycdn-api-test/check")
     */
    public function check(RequestDataBag $dataBag): JsonResponse
    {
        $success = false;

        $config = [
            'apiUrl' =>
                rtrim($dataBag->get('FroshPlatformBunnycdnMediaStorage.config.CdnHostname', ''), '/')
                . '/' .
                $dataBag->get('FroshPlatformBunnycdnMediaStorage.config.StorageName', '') . '/',
            'apiKey' => $dataBag->get('FroshPlatformBunnycdnMediaStorage.config.ApiKey', ''),
        ];

        $filename = Random::getString(20) . '.jpg';
        try {
            $adapter = new BunnyCdnAdapter($config, $this->cache, 'test api');
            if ($adapter->write($filename, $filename, new Config())) {
                $success = true;
                $adapter->delete($filename);
            }
        } catch (\Exception $e) {
            $success = false;
        }

        return new JsonResponse(['success' => $success]);
    }
}
