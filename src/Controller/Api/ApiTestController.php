<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Controller\Api;

use Frosh\BunnycdnMediaStorage\Adapter\Shopware6BunnyCdnAdapter;
use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use League\Flysystem\Config;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['administration']])]
class ApiTestController
{
    #[Route(path: '/api/_action/bunnycdn-api-test/check')]
    public function check(RequestDataBag $dataBag): JsonResponse
    {
        $configKey = FroshPlatformBunnycdnMediaStorage::CONFIG_KEY;

        $config = [
            'endpoint' => rtrim($dataBag->getString($configKey . '.CdnHostname', ''), '/'),
            'storageName' => $dataBag->getString($configKey . '.StorageName', ''),
            'subfolder' => rtrim($dataBag->getString($configKey . '.CdnSubFolder', ''), '/'),
            'apiKey' => $dataBag->getString($configKey . '.ApiKey', ''),
            'useGarbage' => false,
            'neverDelete' => false,
        ];

        $filename = 'testfile_' . Random::getAlphanumericString(20) . '.jpg';

        try {
            $adapter = new Shopware6BunnyCdnAdapter($config);

            $adapter->write($filename, $filename, new Config());
            $success = $adapter->fileExists($filename);

            if ($success) {
                $adapter->delete($filename);
            }
        } catch (\Exception) {
            $success = false;
        }

        return new JsonResponse(['success' => $success]);
    }
}
