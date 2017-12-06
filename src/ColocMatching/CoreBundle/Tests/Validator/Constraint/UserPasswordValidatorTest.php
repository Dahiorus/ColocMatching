<?php

namespace ColocMatching\CoreBundle\Tests\Validator\Constraint;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Security\User\EditPassword;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Validator\Constraint\UserPassword;
use ColocMatching\CoreBundle\Validator\Constraint\UserPasswordValidator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class UserPasswordValidatorTest extends AbstractValidatorTest {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordEncoder;


    protected function setUp() {
        $this->passwordEncoder = $this->createPartialMock(UserPasswordEncoder::class,
            array ("isPasswordValid"));
    }


    protected function getValidatorInstance() : ConstraintValidatorInterface {
        return new UserPasswordValidator($this->passwordEncoder);
    }


    public function testValidationOK() {
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $editPassword = new EditPassword($user);

        $editPassword->setOldPassword($user->getPlainPassword());
        $editPassword->setNewPassword("new_password");

        $this->passwordEncoder->expects(self::once())->method("isPasswordValid")
            ->with($user, $editPassword->getOldPassword())
            ->willReturn(true);

        $userPasswordConstraint = new UserPassword();
        $userPasswordValidator = $this->initValidator(null);
        $userPasswordValidator->validate($editPassword, $userPasswordConstraint);
    }


    public function testValidationKO() {
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);
        $editPassword = new EditPassword($user);

        $editPassword->setOldPassword("other password");
        $editPassword->setNewPassword("new_password");

        $this->passwordEncoder->expects(self::once())->method("isPasswordValid")
            ->with($user, $editPassword->getOldPassword())
            ->willReturn(false);

        $userPasswordConstraint = new UserPassword();
        $userPasswordValidator = $this->initValidator($userPasswordConstraint->message);
        $userPasswordValidator->validate($editPassword, $userPasswordConstraint);
    }
}