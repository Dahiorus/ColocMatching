<?php

namespace ColocMatching\CoreBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateRangeValidator extends ConstraintValidator {


    public function validate($value, Constraint $constraint) {
        if (!empty($value->getEndDate()) && $value->getEndDate() < $value->getStartDate()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

}