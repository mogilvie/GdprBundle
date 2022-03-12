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
    public array $constraints = [];

    public function getDefaultOption(): string
    {
        return 'constraints';
    }

    public function getRequiredOptions(): array
    {
        return ['constraints'];
    }

    protected function getCompositeOption(): string
    {
        return 'constraints';
    }
}
