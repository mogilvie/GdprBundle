<?php

namespace SpecShaper\GdprBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use SpecShaper\GdprBundle\Validator\Constraints\PersonalData;
use SpecShaper\GdprBundle\Model\PersonalData as PersonalDataObject;

/**
 * @author Mark Ogilvie <mark.ogilvie@ogilvieconsulting.net>
 */
class PersonalDataValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PersonalData) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\All');
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof PersonalDataObject) {
            throw new UnexpectedTypeException($value, 'PersonalData');
        }

        $context = $this->context;

        $validator = $context->getValidator()->inContext($context);

        foreach ($value as $key => $element) {
            $validator->atPath('['.$key.']')->validate($element, $constraint->constraints);
        }
    }
}
