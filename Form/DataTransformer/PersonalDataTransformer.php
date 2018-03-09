<?php

/**
 * AppBundle/Form/DataTransformer/MagicSuggestTransformer.php.
 *
 * LICENSE: Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential. SpecShaper is an SaaS product and no license is
 * granted to copy or distribute the source files.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2015, SpecShaper - All rights reserved
 * @license     http://URL name
 *
 * @version     Release: 1.0.0
 *
 * @since       Available since Release 1.0.0
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
 * Transfomer for converting manytomany to json and tranfoming
 * back to an array for pesistance.
 *
 * Creates new entities if additional categories have been added.
 *
 * @author      Mark Ogilvie <mark.ogilvie@specshaper.com>
 * @copyright   (c) 2015, SpecShaper - All rights reserved
 * @license     http://URL name
 *
 * @version     Release: 1.0.0
 *
 * @since       Available since Release 1.0.0
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
    public function transform($personalData = null)
    {
        if ($personalData === null) {
            return '';
        }

        if($personalData->isPurged){
            return 'XXX';
        }

        if(!is_object($personalData)){
            return $personalData;
        }



        return $personalData->getData();
    }

    /**
     * Reverse Transform.
     *
     * Get ID arrays returned from magic suggest text form and
     * gets the matchings categories.
     *
     * Create and persist any new Categories.
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
