<?php

namespace ColocMatching\CoreBundle\Validator\Constraint;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateRangeValidator extends ConstraintValidator {

    /**
     * Validates the both dates of an announcement
     *
     * @param mixed $value           The announcement to validate
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException if the constraint is not a DateRange constraint
     * @throws ConstraintDefinitionException if the value is not an announcement
     */
    public function validate($value, Constraint $constraint) {
        if (!($constraint instanceof DateRange)) {
            throw new UnexpectedTypeException($constraint, DateRange::class);
        }

        if (!($value instanceof Announcement)) {
            throw new ConstraintDefinitionException("The value must be an instance of " . Announcement::class);
        }

        if (!empty($value->getEndDate()) && $value->getEndDate() < $value->getStartDate()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

}