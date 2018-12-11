<?php

namespace App\Tests\Rest\Security\OAuth;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\IdentityProviderAccount;
use App\Core\Entity\User\User;
use App\Core\Exception\InvalidCredentialsException;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\User\IdentityProviderAccountRepository;
use App\Rest\Security\OAuth\OAuthConnect;
use App\Tests\AbstractServiceTest;
use App\Tests\CreateUserTrait;
use Doctrine\ORM\EntityManagerInterface;

class OAuthConnectTest extends AbstractServiceTest
{
    use CreateUserTrait;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var IdentityProviderAccountRepository */
    private $idpAccountRepository;

    /** @var OAuthConnect */
    private $oauthConnect;


    protected function setUp()
    {
        parent::setUp();

        $this->entityManager = $this->getService("doctrine.orm.entity_manager");
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->idpAccountRepository = $this->entityManager->getRepository(IdentityProviderAccount::class);

        $userDtoMapper = new UserDtoMapper($this->entityManager);
        $passwordEncoder = $this->getService("security.password_encoder");

        $this->oauthConnect = new DummyConnect($this->logger, $this->entityManager, $userDtoMapper, $passwordEncoder,
            __DIR__);

        $this->clearData();
    }


    protected function tearDown()
    {
        $this->clearData();
        $this->userManager = null;
        $this->oauthConnect = null;
        $this->idpAccountRepository = null;
        $this->entityManager = null;

        parent::tearDown();
    }


    /**
     * @throws \Exception
     */
    private function clearData()
    {
        $this->userManager->deleteAll();
    }


    /**
     * @param string $accountId
     * @throws \Exception
     */
    private function assertIdpAccount(string $accountId) : void
    {
        $idpAccount = $this->idpAccountRepository->findOneByProvider($this->oauthConnect->getProviderName(),
            $accountId);
        self::assertNotEmpty($idpAccount);
    }


    /**
     * @param UserDto $user
     * @param array $data
     * @throws \Exception
     */
    private function assertUserAndIdpAccount(UserDto $user, array $data)
    {
        self::assertEquals($user->getFirstName(), $data["givenName"]);
        self::assertEquals($user->getLastName(), $data["sn"]);
        self::assertEquals($user->getEmail(), $data["mail"]);

        $this->assertIdpAccount($data["id"]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function handleAccessTokenForNewUser()
    {
        $clientUser = array (
            "id" => "1236547",
            "givenName" => "User",
            "sn" => "Test",
            "mail" => "user-1454@yopmail.com",
            "photoUrl" => null,
        );

        $this->oauthConnect->createClient(["user" => $clientUser]);
        $user = $this->oauthConnect->handleAccessToken("kdfqkjhdfkqjhd-dfjqsdkfjqhsdf-134");

        $this->assertUserAndIdpAccount($user, $clientUser);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function handleAccessTokenForExistingUserWithNoIdpAccount()
    {
        $user = $this->createSearchUser($this->userManager, "user@yopmail.fr");
        $data = array (
            "id" => "651651121",
            "givenName" => "Other",
            "sn" => "Idp",
            "mail" => $user->getEmail(),
            "photoUrl" => null,
        );

        $this->oauthConnect->createClient(["user" => $data]);
        $idpUser = $this->oauthConnect->handleAccessToken("jkqhdfkdhf", "Secret&1234");

        self::assertEquals($user->getId(), $idpUser->getId());
        self::assertEquals($user->getUsername(), $idpUser->getUsername());
        $this->assertIdpAccount($data["id"]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function handleAccessTokenForExistingUserWithNoIdpAccountWithInvalidPasswordShouldThrowException()
    {
        $user = $this->createSearchUser($this->userManager, "user@yopmail.fr");
        $data = array (
            "id" => "651651121",
            "givenName" => "Other",
            "sn" => "Idp",
            "mail" => $user->getEmail(),
            "photoUrl" => null,
        );

        $this->expectException(InvalidCredentialsException::class);

        $this->oauthConnect->createClient(["user" => $data]);
        $this->oauthConnect->handleAccessToken("jkqhdfkdhf", "jkqsjhaAIIOU-*-*/-èç_è-");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function handleAccessTokenWithNoDataShouldThrowException()
    {
        $data = array (
            "id" => "651651121",
            "givenName" => null,
            "sn" => null,
            "mail" => null,
            "photoUrl" => null,
        );

        $this->expectException(InvalidCredentialsException::class);

        $this->oauthConnect->createClient(["user" => $data]);
        $this->oauthConnect->handleAccessToken("jkqhdfkdhf-54654");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function handleAccessTokenForExistingUserHavingIdpAccount()
    {
        $user = $this->createSearchUser($this->userManager, "user@yopmail.fr");
        $data = array (
            "id" => "651651121",
            "givenName" => "Other",
            "sn" => "Idp",
            "mail" => $user->getEmail(),
            "photoUrl" => null,
        );

        // create the user idp account
        $userEntity = $this->entityManager->find(User::class, $user->getId());
        $idpAccount = new IdentityProviderAccount($userEntity, $this->oauthConnect->getProviderName(), $data["id"]);
        $this->entityManager->persist($idpAccount);
        $this->entityManager->flush();

        $this->oauthConnect->createClient(["user" => $data]);
        $idpUser = $this->oauthConnect->handleAccessToken("jkqhdfkdhf-54654");

        self::assertEquals($user->getId(), $idpUser->getId());
    }

}
