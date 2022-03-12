<?php

namespace SpecShaper\GdprBundle\Validator\Constraints;

use SpecShaper\GdprBundle\Model\PersonalData as PersonalDataObject;
use SpecShaper\GdprBundle\Validator\Constraints\PersonalData as PersonalDataConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        if (!$constraint instanceof PersonalDataConstraint) {
            throw new UnexpectedTypeException($constraint, PersonalDataConstraint::class);
        }

        if (null === $value) {
            return;
        }

        if ($value instanceof PersonalDataObject) {
            // throw new UnexpectedTypeException($value, PersonalDataObject::class);
            $value = $value->getData();
        }

        // Get the validator.
        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        // Validate the object against each of the constraints.
        $validator->validate($value, $constraint->constraints);
    }
}
