<?php

namespace ColocMatching\CoreBundle\Validator\Constraint;

use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AddressValueValidator extends ConstraintValidator
{
    /** @var AddressTypeToAddressTransformer */
    private $addressTransformer;


    public function __construct(AddressTypeToAddressTransformer $addressTransformer)
    {
        $this->addressTransformer = $addressTransformer;
    }


    /**
     * Validates the value is a string representing a postal address
     *
     * @param mixed $value The value to validate
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException if the constraint is not a AddressValue constraint
     * @throws ConstraintDefinitionException if the value is not a string
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_string($value))
        {
            throw new ConstraintDefinitionException("The value must be a string");
        }

        if (!($constraint instanceof AddressValue))
        {
            throw new UnexpectedTypeException($constraint, AddressValue::class);
        }

        try
        {
            $this->addressTransformer->reverseTransform($value);
        }
        catch (TransformationFailedException $e)
        {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }

}