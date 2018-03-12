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
use SpecShaper\GdprBundle\Exception\GdprException;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Translator\Fixture\Person;

/**
 * Doctrine event subscriber which encrypt/decrypt entities
 */
class GdprSubscriber implements EventSubscriber
{
    /**
     * Encryptor interface namespace
     */
    const ENCRYPTOR_INTERFACE_NS = EncryptorInterface::class;

    /**
     * Encryptor
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Annotation reader
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $annReader;

    /**
     * Register to avoid multi decode operations for one entity
     * @var array
     */
    private $decodedRegistry = array();

    /**
     * Caches information on an entity's PersonalData fields in an array keyed on
     * the entity's class name. The value will be a list of Reflected fields that are encrypted.
     *
     * @var array
     */
    protected $personalDataFieldCache = array();

    /**
     * Before flushing the objects out to the database, we modify their data value to the
     * encrypted value. Since we want the data to remain decrypted on the entity after a flush,
     * we have to write the decrypted value back to the entity.
     * @var array
     */
    private $postFlushDecryptQueue = array();

    private $isDisabled;

    /**
     * @param \Doctrine\Common\Annotations\Reader                     $annReader
     * @param \SpecShaper\EncryptBundle\Encryptors\EncryptorInterface $encryptor
     * @param                                                         $isDisabled
     */
    public function __construct(Reader $annReader, EncryptorInterface $encryptor, $isDisabled)
    {
        $this->annReader = $annReader;
        $this->encryptor = $encryptor;
        $this->isDisabled = $isDisabled;
    }


    /**
     * Return the encryptor.
     *
     * @return \SpecShaper\EncryptBundle\Encryptors\EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->encryptor;
    }

    /**
     * Set Is Disabled
     *
     * Used to programmatically disable encryption on flush operations.
     * Decryption still occurs if values have the <ENC> suffix.
     *
     * @param bool $isDisabled
     *
     * @return $this
     */
    public function setIsDisabled($isDisabled = true){
        $this->isDisabled = $isDisabled;

        return $this;
    }

    /**
     * Realization of EventSubscriber interface method.
     * @return array Return all events which this subscriber is listening
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postLoad,
            Events::onFlush,
            Events::postFlush,
        );
    }

    /**
     * Update the PersonalData object before we persist it to the database.
     *
     * Notice that we do not recalculate changes otherwise the entity will be written
     * every time (Because it is going to differ from the un-encrypted value)
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        $this->postFlushDecryptQueue = array();

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
     *
     * @param object $entity
     * @param EntityManager $em
     */
    protected function entityOnFlush($entity, EntityManager $em, $isNewEntity = true)
    {
        $objId = spl_object_hash($entity);

        $fields = array();

        foreach ($this->getPersonalDataFields($entity, $em) as $field) {
            $fields[$field->getName()] = array(
                'field' => $field,
                'value' => $field->getValue($entity),
            );
        }

        $this->postFlushDecryptQueue[$objId] = array(
            'entity' => $entity,
            'fields' => $fields,
        );

        $this->processFields($entity, $em, true,  $isNewEntity);
    }

    /**
     * After we have persisted the entities, we want to have the
     * decrypted information available once more.
     * @param PostFlushEventArgs $args
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
                $unitOfWork->setOriginalEntityProperty($oid, $field->getName(), $fieldPair['value']);
            }

            $this->addToDecodedRegistry($entity);
        }

        $this->postFlushDecryptQueue = array();
    }

    /**
     * Listen a postLoad lifecycle event.
     * Decrypt any personal_data if it is encrytpted.
     * @param LifecycleEventArgs $args
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
     *
     * @param $value
     *
     * @return string
     */
    public function decryptValue($value){

        // Else decrypt value and return.
        $decrypted = $this->encryptor->decrypt($value);

        return $decrypted;

    }

    /**
     * Load or persist a PersonalData entity to a personal_data doctrine field.
     *
     * On Flush methods the process will encrypt the data if the field parameters are set isEncrypted= true.
     *
     * If the entity is newly created then set the createdOn date, and if updated then set the updatedON date.
     *
     * If loading the entity then the method will decrypt the data field.
     *
     * @param               $entity
     * @param EntityManager $em
     * @param bool          $isFlush
     * @param bool          $isNewEntity
     * @return bool
     * @throws GdprException
     */
    protected function processFields($entity, EntityManager $em, $isFlush = true, $isNewEntity = true)
    {
        $properties = $this->getPersonalDataFields($entity, $em);

        $unitOfWork = $em->getUnitOfWork();
        $oid = spl_object_hash($entity);

        foreach ($properties as $refProperty) {

            $value = $refProperty->getValue($entity);

            // Skip any empty values.
            if($value === null){
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
            if($isFlush) {
                // Update the PersonalData object with the current entity annotations.
                $this->updateFromAnnotations(get_class($entity), $refProperty->getName(), $value);

                // Set the updatedOn field
                $now = new \DateTime('now');
                $value->setUpdatedOn($now);
                
                // If encrypt bundle is not disabled, and the annotation is supposed to encrypt
                if($this->isDisabled === false && $value->isEncrypted === true) {
                    $encrypted = $this->encryptor->encrypt($value->getData());
                    $data = $encrypted.DoctrineEncryptSubscriberInterface::ENCRYPTED_SUFFIX;
                    $value->setData($data);
                }
            } else {
                $data = $this->decryptValue($value->getData());
                $value->setData($data);
            }

            // Set the PersonalData object back to the entity.
            $refProperty->setValue($entity, $value);

            if (!$isFlush) {
                //we don't want the object to be dirty immediately after reading
                $unitOfWork->setOriginalEntityProperty($oid, $refProperty->getName(), $value);
            }
        }

        return !empty($properties);
    }

    /**
     * Check if we have entity in decoded registry
     * @param object $entity Some doctrine entity
     * @return boolean
     */
    protected function hasInDecodedRegistry($entity)
    {
        return isset($this->decodedRegistry[spl_object_hash($entity)]);
    }

    /**
     * Adds entity to decoded registry
     * @param object $entity Some doctrine entity
     */
    protected function addToDecodedRegistry($entity)
    {
        $this->decodedRegistry[spl_object_hash($entity)] = true;
    }

    /**
     * @param $entity
     * @param EntityManager $em
     * @return \ReflectionProperty[]
     */
    protected function getPersonalDataFields($entity, EntityManager $em)
    {
        $className = get_class($entity);

        if (isset($this->personalDataFieldCache[$className])) {
            return $this->personalDataFieldCache[$className];
        }

        $meta = $em->getClassMetadata($className);

        $personalDataFields = array();

        foreach ($meta->getReflectionProperties() as $refProperty) {
            /** @var \ReflectionProperty $refProperty */

            foreach($this->annReader->getPropertyAnnotations($refProperty) as $key => $annotation){

                // GDPR Bundle, if the annotation is PersonalData then add
                if ($annotation instanceof Column) {
                    if($annotation->type === PersonalDataType::NAME){
                        $refProperty->setAccessible(true);
                        $personalDataFields[$refProperty->getName()] = $refProperty;
                    }
                }
            }
        }

        $this->personalDataFieldCache[$className] = $personalDataFields;

        return $personalDataFields;
    }

    public function updateFromAnnotations($entity, $field, PersonalData $personalData)
    {
        /** @var \ReflectionProperty $refProperty */
        $refProperty = $this->personalDataFieldCache[$entity][$field];
        $annotation = $this->annReader->getPropertyAnnotation($refProperty, Column::class);

        $options = $annotation->options;

        foreach($options as $optionName => $value){
            // Get the setter for the PersonalData field.
            $method_name = 'set' . ucfirst($optionName);

            // Where the setter exists in the PersonalData class then update, else then throw an error.
            if(method_exists($personalData, $method_name)){
                $personalData->$method_name($value);
            } else { 
                throw new GdprException('Definition of "personal_data" option "' . $optionName . '" does not have a matching setter "'
                . $method_name . '" in ' .$entity . '::' . $field);
            }
        }

        return $personalData;
    }

}
