<?php

namespace SpecShaper\GdprBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Composite;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @author Mark Ogilvie <mark.ogilvie@ogilvieconsulting.net>
 */
class PersonalData extends Composite
{
    public $constraints = array();

    public function getDefaultOption()
    {
        return 'constraints';
    }

    public function getRequiredOptions()
    {
        return array('constraints');
    }

    protected function getCompositeOption()
    {
        return 'constraints';
    }
}
