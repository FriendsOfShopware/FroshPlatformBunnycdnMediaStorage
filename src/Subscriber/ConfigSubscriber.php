<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Subscriber;

use Frosh\BunnycdnMediaStorage\Service\ConfigUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigUpdater
     */
    private $configUpdater;

    public function __construct(ConfigUpdater $configUpdater)
    {
        $this->configUpdater = $configUpdater;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'system_config.written' => 'onSaveConfig',
        ];
    }

    public function onSaveConfig(EntityWrittenEvent $event): void
    {
        $froshConfigKey = 'FroshPlatformBunnycdnMediaStorage.config.';

        $newConfig = [];

        foreach ($event->getPayloads() as $payload) {
            if (mb_strpos($payload['configurationKey'], $froshConfigKey) === 0) {
                $newConfig[str_replace($froshConfigKey, '', $payload['configurationKey'])] = $payload['configurationValue'];
            }
        }

        if (!empty($newConfig)) {
            $this->configUpdater->update($newConfig);
        }
    }
}
