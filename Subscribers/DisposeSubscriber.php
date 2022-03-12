<?php

namespace SpecShaper\GdprBundle\Subscribers;

use Doctrine\Common\EventSubscriber;
use SpecShaper\GdprBundle\Event\DisposeEvent;
use SpecShaper\GdprBundle\Event\DisposeEvents;
use SpecShaper\GdprBundle\Utils\Disposer;

/**
 * Doctrine event subscriber which encrypt/decrypt entities.
 */
class DisposeSubscriber implements EventSubscriber
{
    /**
     * @return array Return all events which this subscriber is listening
     */
    public function getSubscribedEvents(): array
    {
        return [
            DisposeEvents::DISPOSE => 'onDispose',
        ];
    }

    public function onDispose(DisposeEvent $event)
    {
        $disposer = new Disposer();

        $disposed = $disposer->dispose($event->getParameter(), $event->getMethod(), $event->getArgs());

        $event->setParameter($disposed);
    }
}
