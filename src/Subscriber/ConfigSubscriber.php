<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Subscriber;

use Frosh\BunnycdnMediaStorage\PluginConfig;
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
        $configKey = PluginConfig::CONFIG_KEY;

        $newConfig = [];

        foreach ($event->getPayloads() as $payload) {
            $payloadConfigKey = (string) $payload['configurationKey'];
            if (str_starts_with($payloadConfigKey, $configKey)) {
                $newConfig[$payloadConfigKey] = $payload['configurationValue'];
            }
        }

        if ($newConfig !== []) {
            $this->configUpdater->update($newConfig);
        }
    }

    public function onAdminConfigSaved(SystemConfigChangedHook $event): void
    {
        $payload = $event->getWebhookPayload();

        if (!\array_key_exists('changes', $payload)) {
            return;
        }

        $changes = $payload['changes'];

        if (empty($changes) || !\is_array($changes)) {
            return;
        }

        foreach ($changes as $change) {
            if (!\str_starts_with((string) $change, PluginConfig::CONFIG_KEY)) {
                continue;
            }

            $this->configUpdater->update();

            break;
        }
    }
}
