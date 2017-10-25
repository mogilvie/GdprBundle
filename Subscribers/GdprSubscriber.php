<?php

namespace SpecShaper\GdprBundle\Subscribers;

use Doctrine\ORM\EntityManager;
use SpecShaper\EncryptBundle\Subscribers\DoctrineEncryptSubscriber;
use SpecShaper\EncryptBundle\Subscribers\DoctrineEncryptSubscriberInterface;
use SpecShaper\GdprBundle\Annotations\PersonalData;
use SpecShaper\EncryptBundle\Annotations\Encrypted;


/**
 * Doctrine event subscriber which encrypt/decrypt entities
 */
class GdprSubscriber extends DoctrineEncryptSubscriber implements DoctrineEncryptSubscriberInterface
{

    const GDPR_ANN_NAME = PersonalData::class;

    /**
     * @param $entity
     * @param EntityManager $em
     * @return \ReflectionProperty[]
     */
    protected function getEncryptedFields($entity, EntityManager $em)
    {

        $className = get_class($entity);

        if (isset($this->encryptedFieldCache[$className])) {
            return $this->encryptedFieldCache[$className];
        }

        $meta = $em->getClassMetadata($className);

        $encryptedFields = array();
        foreach ($meta->getReflectionProperties() as $refProperty) {
            /** @var \ReflectionProperty $refProperty */
            foreach($this->annReader->getPropertyAnnotations($refProperty) as $key => $annotation){

                // If the annotation type is in the Encrypt config as an encrypted annotation then add.
                if (in_array(get_class($annotation), $this->annotationArray)) {
                    $refProperty->setAccessible(true);
                    $encryptedFields[] = $refProperty;
                }

                // GDPR Bundle, if the annotation is PersonalData and it is encrypted then add.
                if ($annotation instanceof PersonalData
                    && $annotation->isEncrypted
                ) {
                    $refProperty->setAccessible(true);
                    $encryptedFields[] = $refProperty;
                }

            }
        }

        $this->encryptedFieldCache[$className] = $encryptedFields;

        return $encryptedFields;
    }

    /**
     * @return array
     */
    public function getEncryptionableProperties($allProperties)
    {
        $encryptedFields = [];

        foreach ($allProperties as $refProperty) {
            /** @var \ReflectionProperty $refProperty */
            foreach($this->annReader->getPropertyAnnotations($refProperty) as $key => $annotation){

                // If the annotation type is in the Encrypt config as an encrypted annotation then add.
                if (in_array(get_class($annotation), $this->annotationArray)) {
                    $refProperty->setAccessible(true);
                    $encryptedFields[] = $refProperty;
                    continue;
                }

                // GDPR Bundle, if the annotation is PersonalData and it is encrypted then add.
                if ($annotation instanceof PersonalData
                    && $annotation->isEncrypted
                ) {
                    $refProperty->setAccessible(true);
                    $encryptedFields[] = $refProperty;
                }

            }
        }

        return $encryptedFields;
    }
}
