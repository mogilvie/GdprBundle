<?php

namespace SpecShaper\GdprBundle\Subscribers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Column;
use SpecShaper\EncryptBundle\Annotations\Encrypted;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\GdprBundle\Event\AccessEvent;
use SpecShaper\GdprBundle\Event\AccessEvents;
use SpecShaper\GdprBundle\Exception\GdprException;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Doctrine event subscriber which encrypt/decrypt entities.
 */
class GdprSubscriber implements EventSubscriber
{
    /**
     * Encryptor interface namespace.
     */
    public const ENCRYPTOR_INTERFACE_NS = EncryptorInterface::class;

    /**
     * Register to avoid multi decode operations for one entity.
     */
    private array $decodedRegistry = [];

    /**
     * Caches information on an entity's PersonalData fields in an array keyed on
     * the entity's class name. The value will be a list of Reflected fields that are encrypted.
     */
    protected array $personalDataFieldCache = [];

    /**
     * Before flushing the objects out to the database, we modify their data value to the
     * encrypted value. Since we want the data to remain decrypted on the entity after a flush,
     * we have to write the decrypted value back to the entity.
     */
    private array $postFlushDecryptQueue = [];

    /**
     * If encryption is disabled in the app parameters.
     */
    private bool $isDisabled;

    public function __construct(
        protected Reader $annReader,
        protected EncryptorInterface $encryptor,
        protected EventDispatcherInterface $dispatcher,
        bool $isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }

    public function getEncryptor(): EncryptorInterface
    {
        return $this->encryptor;
    }

    /**
     * Set Is Disabled.
     *
     * Used to programmatically disable encryption on flush operations.
     * Decryption still occurs if values have the <ENC> suffix.
     */
    public function setIsDisabled(?bool $isDisabled = true): GdprSubscriber
    {
        $this->isDisabled = $isDisabled;

        return $this;
    }

    /**
     * Realization of EventSubscriber interface method.
     *
     * @return array Return all events which this subscriber is listening
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::onFlush,
            Events::postFlush,
        ];
    }

    /**
     * Update the PersonalData object before we persist it to the database.
     *
     * Notice that we do not recalculate changes otherwise the entity will be written
     * every time (Because it is going to differ from the un-encrypted value)
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        $this->postFlushDecryptQueue = [];

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            // Note that the third parameter is set to true for new entity insertions.
            $this->entityOnFlush($entity, $em, true);
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->entityOnFlush($entity, $em, false);
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        }
    }

    /**
     * Processes the entity for an onFlush event.
     */
    protected function entityOnFlush(object $entity, EntityManagerInterface $em, ?bool $isNewEntity = true): void
    {
        $objId = spl_object_hash($entity);

        $fields = [];

        foreach ($this->getPersonalDataFields($entity, $em) as $field) {
            $fields[$field->getName()] = [
                'field' => $field,
                'value' => $field->getValue($entity),
            ];
        }

        $this->postFlushDecryptQueue[$objId] = [
            'entity' => $entity,
            'fields' => $fields,
        ];

        $this->processFields($entity, $em, true, $isNewEntity);
    }

    /**
     * After we have persisted the entities, we want to have the
     * decrypted information available once more.
     */
    /**
     * After we have persisted the entities, we want to have the
     * decrypted information available once more.
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach ($this->postFlushDecryptQueue as $pair) {
            $fieldPairs = $pair['fields'];
            $entity = $pair['entity'];
            $oid = spl_object_hash($entity);

            foreach ($fieldPairs as $fieldPair) {
                /** @var \ReflectionProperty $field */
                $field = $fieldPair['field'];

                $field->setValue($entity, $fieldPair['value']);

                if ($fieldPair['value'] instanceof PersonalData) {
                    $data = $fieldPair['value']->getData();
                    if (null !== $data) {
                        $data = $this->decryptValue($data);
                    }
                    $fieldPair['value']->setData($data);
                }

                $unitOfWork->setOriginalEntityProperty($oid, $field->getName(), $fieldPair['value']);
            }

            $this->addToDecodedRegistry($entity);
        }

        $this->postFlushDecryptQueue = [];
    }

    /**
     * Listen a postLoad lifecycle event.
     * Decrypt any personal_data if it is encrytpted.
     *
     * @throws GdprException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if (!$this->hasInDecodedRegistry($entity)) {
            if ($this->processFields($entity, $em, false)) {
                $this->addToDecodedRegistry($entity);
            }
        }
    }

    /**
     * Decrypt a value.
     *
     * If the value is an object, or if it does not contain the suffic <ENC> then return the value itself back.
     * Otherwise, decrypt the value and return.
     */
    public function decryptValue(string $value): string
    {
        return $this->encryptor->decrypt($value);
    }

    /**
     * Load or persist a PersonalData entity to a personal_data doctrine field.
     *
     * On Flush methods the process will encrypt the data if the field parameters are set isEncrypted= true.
     *
     * If the entity is newly created then set the createdOn date, and if updated then set the updatedON date.
     *
     * If loading the entity then the method will decrypt the data field.
     */
    protected function processFields(object $entity, EntityManagerInterface $em, ?bool $isFlush = true, ?bool $isNewEntity = true): bool
    {
        $properties = $this->getPersonalDataFields($entity, $em);

        $unitOfWork = $em->getUnitOfWork();
        $oid = spl_object_hash($entity);

        foreach ($properties as $refProperty) {
            $value = $refProperty->getValue($entity);

            // Skip any empty values.
            if (null === $value) {
                continue;
            }

            // If the value is not an instance of PersonalData then convert it to one.
            if (!$value instanceof PersonalData) {
                $originalData = $value;
                $value = new PersonalData();

                $value
                    ->setData($originalData)
                    ->setCreatedOn(new \DateTime('now'))
                ;
            }

            // If the operation is an insertion/update then update with latest annotations and encrypt if required.
            // If the operation is a load then attempt to decrypt the data field.
            if ($isFlush) {
                // Update the PersonalData object with the current entity annotations.
                $this->updateFromAnnotations(get_class($entity), $refProperty->getName(), $value);

                // Set the updatedOn field
                $now = new \DateTime('now');
                $value->setUpdatedOn($now);

                // If encrypt bundle is not disabled, and the annotation is supposed to encrypt
                if (false === $this->isDisabled && true === $value->isEncrypted) {
                    $encrypted = $this->encryptor->encrypt($value->getData());
                    $value->setData($encrypted);
                }

                // Dispatch an event for the persisted value
                $event = new AccessEvent($value);
                $this->dispatcher->dispatch($event, AccessEvents::UPDATE);
            } else {
                $data = $value->getData();
                if (null !== $data) {
                    $data = $this->decryptValue($data);
                }
                $value->setData($data);

                // Dispatch an event for the loaded value
                $event = new AccessEvent($value);
                $this->dispatcher->dispatch($event, AccessEvents::LOAD);
            }

            // Set the PersonalData object back to the entity.
            $refProperty->setValue($entity, $value);

            if (!$isFlush) {
                // we don't want the object to be dirty immediately after reading
                $unitOfWork->setOriginalEntityProperty($oid, $refProperty->getName(), $value);
            }
        }

        return !empty($properties);
    }

    /**
     * Check if we have entity in decoded registry.
     *
     * @param object $entity Some doctrine entity
     */
    protected function hasInDecodedRegistry(object $entity): bool
    {
        return isset($this->decodedRegistry[spl_object_hash($entity)]);
    }

    /**
     * Adds entity to decoded registry.
     *
     * @param object $entity Some doctrine entity
     */
    protected function addToDecodedRegistry(object $entity): void
    {
        $this->decodedRegistry[spl_object_hash($entity)] = true;
    }

    protected function getPersonalDataFields(object $entity, EntityManagerInterface $em): array
    {
        $className = get_class($entity);

        if (isset($this->personalDataFieldCache[$className])) {
            return $this->personalDataFieldCache[$className];
        }

        $meta = $em->getClassMetadata($className);

        $personalDataFields = [];

        foreach ($meta->getReflectionProperties() as $refProperty) {
            /** @var \ReflectionProperty $refProperty */
            foreach ($this->annReader->getPropertyAnnotations($refProperty) as $key => $annotation) {
                // GDPR Bundle, if the annotation is PersonalData then add
                if ($annotation instanceof Column) {
                    if (PersonalDataType::NAME === $annotation->type) {
                        $refProperty->setAccessible(true);
                        $personalDataFields[$refProperty->getName()] = $refProperty;
                    }
                }
            }
        }

        $this->personalDataFieldCache[$className] = $personalDataFields;

        return $personalDataFields;
    }

    public function updateFromAnnotations(string $entity, string $field, PersonalData $personalData): PersonalData
    {
        $refProperty = $this->personalDataFieldCache[$entity][$field];
        $annotation = $this->annReader->getPropertyAnnotation($refProperty, Column::class);

        $options = $annotation->options;

        foreach ($options as $optionName => $value) {
            // Get the setter for the PersonalData field.
            $method_name = 'set'.ucfirst($optionName);

            // Where the setter exists in the PersonalData class then update, else then throw an error.
            if (method_exists($personalData, $method_name)) {
                $personalData->$method_name($value);
            } else {
                throw new GdprException(sprintf('Definition of "personal_data" option %s does not have a matching setter %s in %s::%s', $optionName, $method_name, $entity, $field));
            }
        }

        return $personalData;
    }
}
