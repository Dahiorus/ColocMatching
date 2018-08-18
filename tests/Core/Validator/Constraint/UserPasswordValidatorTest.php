<?php

namespace App\Tests\Core\Validator\Constraint;

use App\Core\Entity\Announcement\Address;
use App\Core\Entity\User\User;
use App\Core\Security\User\EditPassword;
use App\Core\Validator\Constraint\UserPassword;
use App\Core\Validator\Constraint\UserPasswordValidator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserPasswordValidatorTest extends AbstractValidatorTest
{
    /**
     * @var UserPasswordEncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordEncoder;


    protected function setUp()
    {
        parent::setUp();
        $this->passwordEncoder = $this->createPartialMock(UserPasswordEncoder::class,
            array ("isPasswordValid"));
    }


    protected function getValidatorInstance() : ConstraintValidatorInterface
    {
        return new UserPasswordValidator($this->passwordEncoder);
    }


    /**
     * @test
     */
    public function oldPasswordIsValid()
    {
        $user = new User("user@test.fr", "password", "User", "Test");
        $editPassword = new EditPassword($user);

        $editPassword->setOldPassword($user->getPlainPassword());
        $editPassword->setNewPassword("new_password");

        $this->passwordEncoder->method("isPasswordValid")
            ->with($user, $editPassword->getOldPassword())
            ->willReturn(true);

        $userPasswordConstraint = new UserPassword();
        $userPasswordValidator = $this->initValidator(null);
        $userPasswordValidator->validate($editPassword, $userPasswordConstraint);
    }


    /**
     * @test
     */
    public function oldPasswordIsInvalid()
    {
        $user = new User("user@test.fr", "password", "User", "Test");
        $editPassword = new EditPassword($user);

        $editPassword->setOldPassword("other password");
        $editPassword->setNewPassword("new_password");

        $this->passwordEncoder->method("isPasswordValid")
            ->with($user, $editPassword->getOldPassword())
            ->willReturn(false);

        $userPasswordConstraint = new UserPassword();
        $userPasswordValidator = $this->initValidator($userPasswordConstraint->message);
        $userPasswordValidator->validate($editPassword, $userPasswordConstraint);
    }


    /**
     * @test
     */
    public function validateOtherValueShouldThrowConstraintDefinitionException()
    {
        $this->expectException(ConstraintDefinitionException::class);

        $validator = $this->initValidator(null);

        $validator->validate(new Address(), new UserPassword());
    }


    /**
     * @test
     */
    public function validateWithOtherConstraintShouldThrowUnexpectedTypeException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $validator = $this->initValidator(null);

        $validator->validate(new EditPassword(new User("test", "password", "test", "test")), new DateTime());
    }

}