<?php

namespace App\Tests\Core\Manager\User;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManager;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Core\Mapper\User\UserTokenDtoMapper;
use App\Core\Service\UserTokenGenerator;
use App\Tests\AbstractServiceTest;
use App\Tests\CreateUserTrait;
use Doctrine\ORM\EntityManagerInterface;

class UserTokenDtoManagerTest extends AbstractServiceTest
{
    use CreateUserTrait;

    /** @var UserTokenDtoManagerInterface */
    protected $manager;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var UserDtoManagerInterface */
    protected $userManager;

    /** @var UserDto */
    private $user;

    /** @var UserTokenDto */
    private $userToken;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->manager = $this->initManager();

        $this->cleanData();
        $this->createAndAssertEntity();
    }


    /**
     * @throws \Exception
     */
    protected function tearDown()
    {
        $this->cleanData();
        $this->entityManager->close();
        
        parent::tearDown();
    }


    /**
     * Initiates the CRUD manager
     *
     * @return UserTokenDtoManagerInterface An instance of the manager
     */
    protected function initManager()
    {
        $this->entityManager = $this->getService("doctrine.orm.entity_manager");

        return new UserTokenDtoManager($this->logger, $this->entityManager, new UserTokenDtoMapper());
    }


    /**
     * Cleans all test data
     *
     * @throws \Exception
     */
    protected function cleanData() : void
    {
        $this->manager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    protected function createAndAssertEntity()
    {
        $this->user = $this->createSearchUser($this->userManager, "user@yopmail.com");
        $this->userToken = $this->manager->createOrUpdate($this->user, UserToken::REGISTRATION_CONFIRMATION,
            new \DateTimeImmutable("tomorrow"));
        $this->assertDto($this->userToken);
    }


    /**
     * Asserts the entity data (can be overrode to assert other properties)
     *
     * @param UserTokenDto $dto
     */
    protected function assertDto($dto) : void
    {
        self::assertNotNull($dto, "Expected DTO to be not null");
        self::assertNotEmpty($dto->getId(), "Expected DTO to have an identifier");
        self::assertNotEmpty($dto->getToken(), "Expected user token to have a token value");
        self::assertNotEmpty($dto->getReason(), "Expected user token to have a reason");
        self::assertNotEmpty($dto->getUsername(), "Expected user token to be linked to a username");
        self::assertNotEmpty($dto->getExpirationDate(), "Expected user token to have an expiration date");
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function createTokenWithInvalidReasonShouldThrowInvalidParameter()
    {
        $this->expectException(InvalidParameterException::class);

        $this->manager->createOrUpdate($this->user, "unknown", new \DateTimeImmutable("tomorrow"));
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function createDuplicateTokenShouldUpdateExpirationDate()
    {
        $userToken = $this->manager->createOrUpdate($this->user, $this->userToken->getReason(),
            new \DateTimeImmutable("+2 days"));

        self::assertEquals($userToken->getToken(), $this->userToken->getToken(),
            "Expected the created token to be the same as the existing one");
        self::assertEquals($userToken->getUsername(), $this->userToken->getUsername(),
            "Expected the created token username to be the same as the existing one");
        self::assertEquals($userToken->getReason(), $this->userToken->getReason(),
            "Expected the created token reason to be the same as the existing one");
        self::assertTrue($this->userToken->getExpirationDate() < $userToken->getExpirationDate(),
            "Expected the created user token expiration date after the existing one");
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function getToken()
    {
        $value = $this->userToken->getToken();
        $userToken = $this->manager->getByToken($value, $this->userToken->getReason());

        self::assertEquals($value, $userToken->getToken(),
            "Expected found user token to have the expected token value");
    }


    /**
     * @test
     * @expectedException \App\Core\Exception\EntityNotFoundException
     *
     * @throws \Exception
     */
    public function findUnknownTokenShouldThrowEntityNotFound()
    {
        $tokenGenerator = new UserTokenGenerator();
        $this->manager->getByToken($tokenGenerator->generateToken("user@test-2.fr", UserToken::LOST_PASSWORD,
            new \DateTimeImmutable("tomorrow")));
    }


    /**
     * @test
     * @expectedException \App\Core\Exception\EntityNotFoundException
     *
     * @throws \Exception
     */
    public function deleteToken()
    {
        $this->manager->delete($this->userToken);

        $value = $this->userToken->getToken();
        $this->manager->getByToken($value);
    }

}
