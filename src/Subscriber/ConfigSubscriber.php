<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Subscriber;

use Frosh\BunnycdnMediaStorage\FroshPlatformBunnycdnMediaStorage;
use Frosh\BunnycdnMediaStorage\Service\ConfigUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SystemConfig\Event\SystemConfigChangedHook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ConfigUpdater $configUpdater)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'system_config.written' => 'onSystemConfigWritten',
            SystemConfigChangedHook::class => 'onAdminConfigSaved',
        ];
    }

    public function onSystemConfigWritten(EntityWrittenEvent $event): void
    {
        $configKey = FroshPlatformBunnycdnMediaStorage::CONFIG_KEY;

        $newConfig = [];

        foreach ($event->getPayloads() as $payload) {
            if (str_starts_with((string) $payload['configurationKey'], $configKey)) {
                $newConfig[str_replace($configKey . '.', '', (string) $payload['configurationKey'])] = $payload['configurationValue'];
            }
        }

        if ($newConfig !== []) {
            $this->configUpdater->update($newConfig);
        }
    }

    public function onAdminConfigSaved(SystemConfigChangedHook $event): void
    {
        $changes = $event->getWebhookPayload()['changes'] ?? [];

        if (empty($changes)) {
            return;
        }

        foreach ($changes as $change) {
            if (!\str_starts_with((string) $change, FroshPlatformBunnycdnMediaStorage::CONFIG_KEY)) {
                continue;
            }

            $this->configUpdater->update();

            break;
        }
    }
}
