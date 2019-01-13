<?php

namespace App\Tests\Rest\Controller\v1;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient([], ["HTTPS" => true]);
    }


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        // empty method
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @param string $status
     * @return UserDto
     * @throws \Exception
     */
    private function createUser(string $status = UserStatus::ENABLED) : UserDto
    {
        return $this->createProposalUser($this->userManager, "user@test.fr", $status);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateUserShouldReturn201()
    {
        $user = $this->createUser();

        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => $user->getUsername(), "_password" => "Secret&1234"));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function badCredentialsShouldReturn401()
    {
        $user = $this->createUser();

        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => $user->getUsername(), "_password" => "password"));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function unknownUsernameShouldReturn401()
    {
        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => "unknown-user@test.fr", "_password" => "password"));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function missingDataShouldReturn400()
    {
        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => "", "_password" => "password"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateAsUserShouldReturn403()
    {
        $user = $this->createUser();
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => $user->getUsername(), "_password" => "Secret&1234"));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateDisabledUserShouldReturn201AndEnableUser()
    {
        $user = $this->createUser(UserStatus::DISABLED);
        $this->userManager->createDeleteEvent($user);

        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => $user->getUsername(), "_password" => "Secret&1234"));
        self::assertStatusCode(Response::HTTP_CREATED);

        $user = $this->userManager->findByUsername($user->getUsername());
        self::assertEquals(UserStatus::ENABLED, $user->getStatus(), "Expected the user to be enabled");
    }


    /**
     * @test
     */
    public function authenticateOAuthUserShouldReturn201()
    {
        static::$client->request("POST", "/rest/auth/tokens/dummy",
            array ("accessToken" => "qkfhsdjf55lkjqdsfj-j"));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function authenticateOAuthUserOnUnknownProviderShouldReturn404()
    {
        static::$client->request("POST", "/rest/auth/tokens/jgkljgmlk",
            array ("accessToken" => "jlkfdjg-j"));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateOauthUserWithKnownEmailShouldReturn201()
    {
        $this->createSearchUser($this->userManager, "user-test@social-yopmail.com");

        static::$client->request("POST", "/rest/auth/tokens/dummy", array (
            "accessToken" => "jlkfdjg-j",
            "userPassword" => "Secret&1234"
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateOauthUserWithKnownEmailAndBadPasswordShouldReturn401()
    {
        $this->createSearchUser($this->userManager, "user-test@social-yopmail.com");

        static::$client->request("POST", "/rest/auth/tokens/dummy", array (
            "accessToken" => "jlkfdjg-j",
            "userPassword" => "kdsfjqlsjlf"
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateOauthUserAsUserShouldReturn403()
    {
        $user = $this->createUser();
        self::$client = self::createAuthenticatedClient($user, array (), array ("HTTPS" => true));

        static::$client->request("POST", "/rest/auth/tokens/dummy",
            array ("accessToken" => "jlkfdjg-j"));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
