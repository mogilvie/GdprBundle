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
     * Passes straight through as a json string
     *
     * @since   Available since Release 1.0.0
     *
     * @param PersonalData $personalData
     *
     * @return string
     */
    public function transform($data = null)
    {
        if ($data === null) {
            return '';
        }

        if(!is_object($data)){
            return $data;
        }

        if($data->isExpired()){
            return 'XXX';
        }

        return $data->getData();
    }

    /**
     * Reverse Transform.
     *
     * @since   Available since Release 1.0.0
     *
     * @param string $ms
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($data)
    {
        // Return if nothing to save
        if ($data === null || $data === ' ') {
            return;
        }

        $personalData = new PersonalData();

        $personalData->setData($data);

        return $personalData;
    }
}
