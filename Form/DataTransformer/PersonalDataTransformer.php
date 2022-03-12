<?php

namespace SpecShaper\GdprBundle\Form\DataTransformer;

use SpecShaper\GdprBundle\Model\PersonalData;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class PersonalDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
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

        // If it is expired then return obscured
        // @todo use the expiration methods to return a value.
        if ($value->isExpired()) {
            return 'XXX';
        }

        // Otherwise return the base data.
        return $value->getData();
    }

    /**
     * {@inheritdoc}
     *
     * @return PersonalData|false
     */
    public function reverseTransform($value): ?PersonalData
    {
        // Return if null or white space.
        if (null === $value || ctype_space($value)) {
            return null;
        }

        $personalData = new PersonalData();

        $personalData->setData($value);

        return $personalData;
    }
}
