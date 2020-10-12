<?php declare(strict_types=1);

namespace Frosh\BunnycdnMediaStorage\Subscriber;

use Frosh\BunnycdnMediaStorage\Service\ConfigUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;
    /**
     * @var ConfigUpdater
     */
    private $configUpdater;

    public function __construct(SystemConfigService $systemConfigService, ConfigUpdater $configUpdater) {
        $this->systemConfigService = $systemConfigService;
        $this->configUpdater = $configUpdater;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'system_config.written' => 'onSaveConfig'
        ];
    }

    public function onSaveConfig(EntityWrittenEvent $event): void
    {
        $updateConfig = false;
        foreach($event->getPayloads() as $payload) {
            if (strpos($payload['configurationKey'], 'FroshPlatformBunnycdnMediaStorage.config') === 0) {
                $updateConfig = true;
                break;
            }
        }

        if ($updateConfig) {
            $this->configUpdater->update();
        }

    }
}
