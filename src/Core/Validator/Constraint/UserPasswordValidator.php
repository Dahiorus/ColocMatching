<?php

namespace App\Core\Validator\Constraint;

use App\Core\Security\User\EditPassword;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserPasswordValidator extends ConstraintValidator
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;


    /**
     * UserPasswordValidator constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * Validates that the user's current password and the specified old password are equal
     *
     * @param mixed $value The value to validate
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException if the constraint is not a UserPassword constraint
     * @throws ConstraintDefinitionException if the value is not an announcement
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($constraint instanceof UserPassword))
        {
            throw new UnexpectedTypeException($constraint, UserPassword::class);
        }

        if (!($value instanceof EditPassword))
        {
            throw new ConstraintDefinitionException("The value to validate must be an instance of "
                . EditPassword::class);
        }

        $user = $value->getUser();
        $oldPassword = $value->getOldPassword();

        if (!$this->passwordEncoder->isPasswordValid($user, $oldPassword))
        {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath("oldPassword")
                ->addViolation();
        }
    }
}