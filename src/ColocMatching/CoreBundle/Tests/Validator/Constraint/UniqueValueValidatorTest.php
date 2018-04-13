<?php

namespace ColocMatching\CoreBundle\Tests\Validator\Constraint;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Validator\Constraint\UniqueValue;
use ColocMatching\CoreBundle\Validator\Constraint\UniqueValueValidator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueValueValidatorTest extends AbstractValidatorTest
{
    /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $classMetadata;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $repository;


    protected function setUp()
    {
        parent::setUp();
        $this->entityManager = $this->createPartialMock(EntityManager::class,
            array ("getRepository", "getClassMetadata"));
        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager->method("getRepository")->willReturn($this->repository);
        $this->entityManager->method("getClassMetadata")->willReturn($this->classMetadata);
    }


    protected function getValidatorInstance() : ConstraintValidatorInterface
    {
        return new UniqueValueValidator($this->entityManager);
    }


    /**
     * @test
     */
    public function validateUniqueValue()
    {
        $user = new UserDto();
        $user->setEmail("user@test.fr");

        $this->classMetadata->method("hasField")->with("email")->willReturn(true);
        $this->repository->method("findBy")->with(array ("email" => $user->getEmail()))->willReturn(null);

        $validator = $this->initValidator(null);
        $constraint = new UniqueValue(array ("properties" => array ("email")));

        $validator->validate($user, $constraint);
    }


    /**
     * @test
     */
    public function validateUniqueValueWithEmptyPropertyValue()
    {
        $user = new UserDto();

        $this->classMetadata->method("hasField")->with("email")->willReturn(true);

        $validator = $this->initValidator(null);
        $constraint = new UniqueValue(array ("properties" => array ("email")));

        $validator->validate($user, $constraint);
    }


    /**
     * @test
     */
    public function validateUniqueValueWithNonMappedPropertyShouldThrowConstraintDefinitionException()
    {
        $this->expectException(ConstraintDefinitionException::class);

        $user = new UserDto();

        $this->classMetadata->method("hasField")->with("unknown")->willReturn(false);

        $validator = $this->initValidator(null);
        $constraint = new UniqueValue(array ("properties" => array ("unknown")));

        $validator->validate($user, $constraint);
    }


    /**
     * @test
     */
    public function validateNonUniqueValue()
    {
        $user = new UserDto();
        $user->setEmail("user@test.fr");

        $this->classMetadata->method("hasField")->with("email")->willReturn(true);
        $this->repository->method("findBy")->with(array ("email" => $user->getEmail()))
            ->willReturn(new User($user->getEmail(), "password", "Test", "User"));

        $constraint = new UniqueValue(array ("properties" => array ("email")));
        $validator = $this->initValidator($constraint->message);

        $validator->validate($user, $constraint);
    }


    /**
     * @test
     */
    public function validateNonAbstractDtoShouldThrowConstraintDefinitionException()
    {
        $this->expectException(ConstraintDefinitionException::class);

        $validator = $this->initValidator(null);
        $constraint = new UniqueValue(array ("properties" => array ("email")));

        $validator->validate("value", $constraint);
    }


    /**
     * @test
     */
    public function validateEmptyPropertiesShouldThrowConstraintDefinitionException()
    {
        $this->expectException(ConstraintDefinitionException::class);

        $validator = $this->initValidator(null);
        $constraint = new UniqueValue(array ("properties" => array ()));

        $validator->validate(new UserDto(), $constraint);
    }


    /**
     * @test
     */
    public function validateWithOtherConstraintShouldThrowUnexpectedTypeException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $validator = $this->initValidator(null);

        $validator->validate(new UserDto(), new Blank());
    }

}