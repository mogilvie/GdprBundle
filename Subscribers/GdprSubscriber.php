<?php

namespace SpecShaper\GdprBundle\Subscribers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Column;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\GdprBundle\Exception\GdprException;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use SpecShaper\GdprBundle\Utils\ValueExtractor;

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
     * An array of decoded values populated during the onLoad event.
     * Used to compare any resubmitted values during onFlush event.
     * If the flushed unencoded value is the same as in the array then there is no change
     * to the value and the entity field update is removed from the Unit of Work change set.
     */
    private array $decodedValues = [];

    /**
     * An array of entity parameters where the loaded attributes do not match the entity persisted attributes.
     */
    private array $annotationsFieldChanged = [];

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

    private ValueExtractor $valueExtractor;

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
        $this->valueExtractor = new ValueExtractor();
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
     * After we have persisted the entities, we want to have the
     * decrypted information available once more.
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $unitOfWork = $args->getObjectManager()->getUnitOfWork();

        // Step through the cache of entity classes that were encrypted during onFlush
        foreach ($this->postFlushDecryptQueue as $class => $entities) {
            // Step through each specific entity.
            foreach ($entities as $entityId => $entity) {
                // Step through the entity fields and get the unencrypted value.
                foreach ($entity as $fieldName => $unencryptedValue) {
                    // Strip procy
                    // Get current entity representation from the UoW identify map.
                    $entity = $unitOfWork->getIdentityMap()[$class][$entityId];

//                    // Create a reflection and get the personal data object.
//                    $reflection = new \ReflectionObject($entity);

                    $personalDataObject = $this->valueExtractor->extractValue($entity, $fieldName);
//                    $personalDataObject = $reflection->getProperty($fieldName)->getValue($entity);

                    // Overwrite the encrypted value with the unencrypted value cached during onFlush.
                    if($personalDataObject instanceof PersonalData){
                        $personalDataObject->setData($unencryptedValue);
                    }

                    // Update the unit of work.
                    $unitOfWork->setOriginalEntityProperty(spl_object_id($entity), $fieldName, $personalDataObject);
                }
            }
        }

        // Clear the queue once all encrypted values are decrypted again.
        $this->postFlushDecryptQueue = [];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getObjectManager();

        $unitOfWork = $em->getUnitOfWork();

        $this->postFlushDecryptQueue = [];

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            // Note that the third parameter is set to true for new entity insertions.
            $this->onInsert($entity, $em);
        }

        // The PersonalDataTransformer returns
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->onUpdate($entity, $em);
            $className = ClassUtils::getClass($entity);
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata($className), $entity);
        }
    }

    /**
     * Newly inserted entities do not have PersonalData objects do not have fully populated fields.
     *
     * For new entities we need to encrypt values if required, and populate the PersonalData fields from the annotation
     * reader.
     *
     * @return void
     */
    private function onInsert(object $entity, EntityManagerInterface $em)
    {
        // Get a reflection of the entity.
//        $entityReflection = new \ReflectionObject($entity);

        // Get the PersonalData fields in the entity class.
        $personalDataFields = $this->getPersonalDataFields($em, $entity);

        foreach ($personalDataFields as $fieldName => $personalDataProperties) {
            // Get the PersonalData object, or create one.
            $value = $this->valueExtractor->extractValue($entity, $fieldName);

            if ($value instanceof PersonalData) {
                // Encrypt the data for insertion.
                $className = ClassUtils::getClass($entity);

                $this->encryptData($className, spl_object_id($entity), $entity->getId(), $fieldName, $value);
            }

            $this->valueExtractor->setValue($entity, $fieldName, $value);
        }
    }

    /**
     * Updated entities which contain PersonalData objects have their fields populated and decrypted during the onLoad event.
     *
     * If an entity has been updated via a form and the PersonalDataType with PersonalDataTransformer, then the submitted PersonalData objects
     * are clones of the onLoad objects, so are triggered for update with the unit of work.
     *
     * If an entity PersonalData field has had its data modified outside a PersonalDataType then we need to match
     */
    private function onUpdate(object $entity, EntityManagerInterface $em)
    {
        $unitOfWork = $em->getUnitOfWork();

        // Get classname
        $className = ClassUtils::getClass($entity);

        // Get the PersonalData fields in the entity class.
        $personalDataFields = $this->getPersonalDataFields($em, $entity);

        if (empty($personalDataFields)) {
            return;
        }

        // For each of the personal data fields in the flushed entity.
        foreach ($personalDataFields as $fieldName => $personalDataProperties) {
            // Get the value flushed.
            $flushedValue = $this->valueExtractor->extractValue($entity, $fieldName);

            // If the original data was never encrypted, but it now needs to be by the PersonalData attribute isEncrypted = true,
            // Then trigger an the hasDataChanged check.
            if(!str_ends_with($flushedValue, "=<ENC>") && $personalDataProperties['isEncrypted'] === true){
                unset($this->decodedValues[spl_object_id($entity)][$fieldName]);
            }
            
            // Check if the flushed value is different from the onLoad event value.
            $hasDataChanged = $this->hasDataChanged(spl_object_id($entity), $fieldName, $flushedValue);

            $haveAnnotationsChanged = $this->haveAnnotationsChanged($className, $fieldName);

            // If there is no change to the data or to the annotation options then remove the field from the changeset.
            if (false === $hasDataChanged && false === $haveAnnotationsChanged) {
                unset($unitOfWork->getEntityChangeSet($entity)[$fieldName]);
                continue;
            }

            // Get the PersonalData object from cache, the entity, or create one.
            $personalData = $this->getPersonalDataObject($fieldName, $entity);

            if ($personalData instanceof PersonalData) {
                // Encrypt the data for insertion. If the object data hasn't changed between onLoad and onFlush then return false.
                $this->encryptData($className, spl_object_id($entity), $entity->getId(), $fieldName, $personalData);
            }

            $this->valueExtractor->setValue($entity, $fieldName, $personalData);
        }
    }

    private function hasDataChanged(string $oid, string $fieldName, $data)
    {
        if (!$data instanceof PersonalData) {
            return true;
        }

        $data = $data->getData();

        if (!array_key_exists($oid, $this->decodedValues) || !array_key_exists($fieldName, $this->decodedValues[$oid])) {
            return true;
        }

        $onLoadValue = $this->decodedValues[$oid][$fieldName]['decrypted'];

        // If the unencrypted data in the Object is the same as the original data at from onLoad then reset with the original encryption.
        if ($data === $onLoadValue) {
            return false;
        }

        return true;
    }

    private function haveAnnotationsChanged($entityClassName, $fieldName)
    {
        if (!isset($this->annotationsFieldChanged[$entityClassName])) {
            return false;
        }

        if (!isset($this->annotationsFieldChanged[$entityClassName][$fieldName])) {
            return false;
        }

        return true;
    }

    /**
     * @return bool|PersonalData return false if there is no change to the unencrypted data
     */
    private function encryptData(string $className, string $oid, string $entityId, string $fieldName, PersonalData $personalData): bool|PersonalData
    {
        $onFlushDataValue = $personalData->getData();

        // If the unencrypted data in the Object is the same as the original data at from onLoad then reset with the original encryption.
        if (isset($this->decodedValues[$oid][$fieldName])) {
            // Get the onLoad unencrypted value.
            $decryptedValue = $this->decodedValues[$oid][$fieldName]['decrypted'];

            // If the onFlush value is the same as the onLoad decrypted value
            if ($onFlushDataValue === $decryptedValue) {
                // Set the original encryption to the PersonalData object.
                $personalData->setData($this->decodedValues[$oid][$fieldName]['original']);

                // Store the decrypted value for the post flush decryption.
                $this->addToPostFlushDecryptQueue($className, $entityId, $fieldName, $decryptedValue);

                return $personalData;
            }
        }

        // If the data is not supposed to be encrypted then return the PersonalData object
        if (false === $personalData->isEncrypted) {
            return $personalData;
        }

        // Otherwise, encrypt the new data, and update the object
        $encrypted = $this->encryptor->encrypt($onFlushDataValue);

        $personalData
            ->setData($encrypted)
            ->setUpdatedOn(new \DateTime('now'))
        ;

        $this->addToPostFlushDecryptQueue($className, $entityId, $fieldName, $onFlushDataValue);

        return $personalData;
    }

    private function addToPostFlushDecryptQueue(string $className, string $entityId, string $fieldName, $decryptedValue)
    {
        if (!array_key_exists($className, $this->postFlushDecryptQueue)) {
            $this->postFlushDecryptQueue[$className] = [];
        }

        if (!array_key_exists($entityId, $this->postFlushDecryptQueue)) {
            $this->postFlushDecryptQueue[$className][$entityId] = [];
        }

        $this->postFlushDecryptQueue[$className][$entityId][$fieldName] = $decryptedValue;
    }

    /**
     * Listen a postLoad lifecycle event.
     * Decrypt any personal_data if it is encrytpted.
     *
     * @throws GdprException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $this->loadEntity($args);
    }

    private function loadEntity(LifecycleEventArgs $args)
    {
        // Get the entity.
        $entity = $args->getObject();
        $om = $args->getObjectManager();
        $unitOfWork = $om->getUnitOfWork();

        // Get the entity reflection
        $personalDataFields = $this->getPersonalDataFields($om, $entity);

        // If there are no personal data reflection properties then do nothing.;
        if (empty($personalDataFields)) {
            return;
        }

        /*
         * Step through each of the personal_data properties in the entity.
         */
        foreach ($personalDataFields as $entityField => $personalDataOptions) {
            // We now have a PersonalData object.
            $personalDataObject = $this->getPersonalDataObject($entityField, $entity);

            if (null === $personalDataObject) {
                continue;
            }

            // Get the original data value as loaded from the manager.
            $originalData = $personalDataObject->getData();

            // Decrypt that value.
            $decryptedValue = $this->encryptor->decrypt($originalData);

            // Cache the original and the decrypted values for later use.
            $this->cacheDecodedValue(spl_object_id($entity), $entityField, $originalData, $decryptedValue);

            // If the original and decrypted are different then set the decrypted value.
            if ($originalData !== $decryptedValue) {
                $personalDataObject->setData($decryptedValue);
            }

            // Store the original decoded PersonalData object to be used during onFlush event.
            $this->decodedRegistry[spl_object_id($entity)][$entityField] = $personalDataObject;

            // Set this as the original entity property.
            $unitOfWork->setOriginalEntityProperty(spl_object_id($entity), $entityField, $personalDataObject);

            // Update the PersonalData object fields with the latest annotation personal_data options.
            // We will compare the object during flush against the original.

            foreach ($personalDataOptions as $annotationOption => $annotationValue) {
                $this->valueExtractor->setValue($personalDataObject, $annotationOption, $annotationValue);
            }
        }
    }

    private function getPersonalDataObject(string $fieldName, object $entity): ?PersonalData
    {
        $cachedDataObject = null;

        // If the personal data object was already loaded and cached then get it.
        if (isset($this->decodedRegistry[spl_object_id($entity)][$fieldName])) {
            // Get the onLoad PersonalData object that was updated at onLoadEvent.
            $cachedDataObject = $this->decodedRegistry[spl_object_id($entity)][$fieldName];
        }

        $value =  $this->valueExtractor->extractValue($entity, $fieldName);

        if (null === $value) {
            return null;
        }

        if ($cachedDataObject instanceof PersonalData) {
            $cachedDataObject->setData($value);

            return $cachedDataObject;
        }

        // If the property is not currently a PersonalData object, but should be, then create one using the value as data.
        if ($value instanceof PersonalData) {
            return $value;
        }

        // Otherwise return a new PersonalData Object.
        return (new PersonalData())
            ->setData($value)
            ->setCreatedOn(new \DateTime('now'))
        ;
    }

    private function cacheDecodedValue(string $oid, string $fieldName, ?string $originalValue, ?string $decodedValue)
    {
        if (!array_key_exists($oid, $this->decodedValues)) {
            $this->decodedValues[$oid] = [];
        }

        if (!array_key_exists($fieldName, $this->decodedValues[$oid])) {
            $this->decodedValues[$oid][$fieldName] = [];
        }

        $this->decodedValues[$oid][$fieldName]['original'] = $originalValue;
        $this->decodedValues[$oid][$fieldName]['decrypted'] = $decodedValue;
    }

    private function getPersonalDataFields(EntityManagerInterface $em, object $entity): array
    {
        $className = ClassUtils::getClass($entity);

        if (isset($this->personalDataFieldCache[$className])) {
            return $this->personalDataFieldCache[$className];
        }

        $meta = $em->getClassMetadata($className);

        $personalDataFields = [];

        // Step through each of the entity properties
        foreach ($meta->fieldMappings as $fieldName => $fieldMapping) {
            if (PersonalDataType::NAME === $fieldMapping['type']) {
                $personalDataFields[$fieldName] = $fieldMapping['options'];
            }
        }

        $this->personalDataFieldCache[$className] = $personalDataFields;

        return $personalDataFields;
    }

    public function updateFromAnnotations(string $entityClassName, string $field, PersonalData $personalData): bool
    {
        $refProperty = $this->personalDataFieldCache[$entityClassName][$field];

        // Get the latest set of annotations from the entity property.
        $annotation = $this->annReader->getPropertyAnnotation($refProperty, Column::class);

        // Get the personal_data options from the annotation.
        $options = $annotation->options;

        $hasAnnotationChange = false;

        // Get a reflection of the PersonalData object entity.
        $personalDataReflectionObject = new \ReflectionObject($personalData);

        // For each option in the personal_data annotation, attempt to set the property in the PersonalData object.
        foreach ($options as $optionName => $annotationValue) {
            // If the annotation option doesn't exist in the personal data object properties then throw an error.
            if (false === $personalDataReflectionObject->hasProperty($optionName)) {
                throw new GdprException(sprintf('Definition of "personal_data" option %s does not have a matching property in %s', $optionName, $entityClassName));
            }

            // If the annotation option is the same as is already defined in the object properties then skip.
            if ($annotationValue === $personalDataReflectionObject->getProperty($optionName)) {
                continue;
            }

            // The "retainFor" value is a string in the annotations, but needs to be a \DateInterval in the object.
            if ('retainFor' === $optionName) {
                // Create a date interval based on the annotation P6Y for example.
                $annotationRetainFor = (new \DateInterval($annotationValue));

                // If the string is the same as the date interval
                if (null !== $personalData->getRetainFor() && $personalData->getRetainFor() === $annotationRetainFor) {
                    continue;
                }

                $annotationValue = $annotationRetainFor;
            }

            // Update the PersonalData object with the new annotation value.
            $personalDataReflectionObject->getProperty($optionName)->setValue($personalData, $annotationValue);
            $hasAnnotationChange = true;

            if (!array_key_exists($entityClassName, $this->annotationsFieldChanged)) {
                $this->annotationsFieldChanged[$entityClassName] = [];
            }

            if (!array_key_exists($optionName, $this->annotationsFieldChanged[$entityClassName])) {
                $this->annotationsFieldChanged[$entityClassName][] = $optionName;
            }
        }

        return $hasAnnotationChange;
    }
}
