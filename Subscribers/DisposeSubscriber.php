<?php

namespace SpecShaper\GdprBundle\Subscribers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Column;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\EncryptBundle\Annotations\Encrypted;
use SpecShaper\EncryptBundle\Exception\EncryptException;
use SpecShaper\EncryptBundle\Subscribers\DoctrineEncryptSubscriberInterface;
use SpecShaper\GdprBundle\Event\AccessEvent;
use SpecShaper\GdprBundle\Event\AccessEvents;
use SpecShaper\GdprBundle\Event\DisposeEvent;
use SpecShaper\GdprBundle\Event\DisposeEvents;
use SpecShaper\GdprBundle\Utils\Disposal\AnonymiseDate;
use SpecShaper\GdprBundle\Utils\Disposal\AnonymiseIP;
use SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface;
use SpecShaper\GdprBundle\Utils\Disposal\RegexReplace;
use SpecShaper\GdprBundle\Utils\Disposal\SetNull;
use SpecShaper\GdprBundle\Utils\Disposer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SpecShaper\GdprBundle\Exception\GdprException;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Translator\Fixture\Person;

/**
 * Doctrine event subscriber which encrypt/decrypt entities
 */
class DisposeSubscriber implements EventSubscriber
{

    /**
     * @return array Return all events which this subscriber is listening
     */
    public function getSubscribedEvents()
    {
        return array(
            DisposeEvents::DISPOSE => 'onDispose'
        );
    }

    /**
     * @param DisposeEvent $event
     */
    public function onDispose(DisposeEvent $event)
    {

        $disposer = new Disposer();

        $disposed = $disposer->dispose($event->getParameter(), $event->getMethod(), $event->getArgs());

        $event->setParameter($disposed);
    }



}
