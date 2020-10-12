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

    public function __construct(ConfigUpdater $configUpdater) {
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
        foreach($event->getPayloads() as $payload) {
            if (strpos($payload['configurationKey'], 'FroshPlatformBunnycdnMediaStorage.config') === 0) {
                $this->configUpdater->update();
                break;
            }
        }
    }
}
