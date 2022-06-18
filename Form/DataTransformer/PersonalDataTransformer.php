<?php

namespace SpecShaper\GdprBundle\Form\DataTransformer;

use SpecShaper\GdprBundle\Model\PersonalData;
use Symfony\Component\Form\DataTransformerInterface;

class PersonalDataTransformer implements DataTransformerInterface
{
    private ?PersonalData $personalDataObject = null;

    /**
     * {@inheritdoc}
     */
    public function transform($value): mixed
    {
        // If null then return an empty space.
        if (null === $value) {
            return '';
        }

        // If the data is not an object then return it as is.
        if (!is_object($value)) {
            return $value;
        }

        // If the data is not an instance of PersonalData object
        if (!$value instanceof PersonalData) {
            return $value;
        }

        // Store the personal data object for submission.
        $this->personalDataObject = $value;

        // Otherwise, return the base data.
        return $value->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): ?PersonalData
    {

        // Return if null or white space.
        if (null === $value || ctype_space($value)) {
            return null;
        }

        // If there is no PersonalData object on form render, then create one for submission.
        if(null === $this->personalDataObject){
            $this->personalDataObject = new PersonalData();
        }

        // If the value is same as the original value then return the original personal data object.
        // The returned object has the same doctrine oid number, so will not trigger an entity update.
        if($value === $this->personalDataObject->getData($value)){
            return $this->personalDataObject;
        }

        // Otherwise create a clone of the original personal data object, in order to trigger UoW update.
        $clonedObject = clone $this->personalDataObject;

        // Set the submitted data to the cloned object.
        $clonedObject->setData($value);

        return $clonedObject;

    }
}
