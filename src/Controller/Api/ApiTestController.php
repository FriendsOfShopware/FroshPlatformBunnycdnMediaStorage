<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Controller\Api;

use Frosh\BunnycdnMediaStorage\Adapter\AdapterConfig;
use Frosh\BunnycdnMediaStorage\Adapter\Shopware6BunnyCdnAdapter;
use Frosh\BunnycdnMediaStorage\PluginConfig;
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
        $adapterConfig = new AdapterConfig();
        $adapterConfig->setEndpoint($this->getString($dataBag, 'CdnHostname'));
        $adapterConfig->setStorageName($this->getString($dataBag, 'StorageName'));
        $adapterConfig->setApiKey($this->getString($dataBag, 'ApiKey'));

        $filename = 'testfile_' . Random::getAlphanumericString(20) . '.jpg';

        try {
            $adapter = new Shopware6BunnyCdnAdapter($adapterConfig);

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

    private function getString(RequestDataBag $dataBag, string $key): string
    {
        $value = $dataBag->get(PluginConfig::CONFIG_KEY . '.' . $key, '');

        if (!\is_scalar($value) && !$value instanceof \Stringable) {
            throw new \UnexpectedValueException(sprintf('Parameter value "%s" cannot be converted to "string".', $key));
        }

        return (string) $value;
    }
}
