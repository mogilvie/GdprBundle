<?php

/**
 * AppBundle/Form/DataTransformer/MagicSuggestTransformer.php.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */

namespace SpecShaper\GdprBundle\Form\DataTransformer;

use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use SpecShaper\GdprBundle\Model\PersonalData;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use AppBundle\Entity\Organisation\Contact;

/**
 * MagicSuggestTransformer.
 *
 * Transformer for converting manytomany to json and tranforming
 * back to an array for persistance.
 *
 * Creates new entities if additional categories have been added.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 */
class PersonalDataTransformer implements DataTransformerInterface
{

    /**
     * Transform.
     *
     * Transfrom from code to form.
     *
     * @param $data
     *
     * @return null|string
     */
    public function transform($data = null)
    {
        // If null then return an empty space.
        if ($data === null) {
            return '';
        }

        // If the data is not an object then return it as is.
        if(!is_object($data)){
            return $data;
        }

        // If the data is not an instance of PersonalData object
        if(!$data instanceof PersonalData) {
            return $data;
        }

        // If it is expired then return obscured
        // @todo use the expiration methods to return a value.
        if ($data->isExpired()) {
            return 'XXX';
        }

        // Otherwise return the base data.
        return $data->getData();
    }

    /**
     * @param $data
     *
     * @return \SpecShaper\GdprBundle\Model\PersonalData|void
     */
    public function reverseTransform($data)
    {
        // Return if null or white space.
        if ($data === null || ctype_space($data)) {
            return;
        }

        $personalData = new PersonalData();

        $personalData->setData($data);

        return $personalData;
    }
}
