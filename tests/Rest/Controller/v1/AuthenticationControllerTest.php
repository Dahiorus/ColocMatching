<?php

namespace App\Tests\Rest\Controller\v1;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserStatus;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControllerTest extends AbstractControllerTest
{
    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient(array (), array ("HTTPS" => true));
    }


    protected function initServices() : void
    {
        // empty method
    }


    protected function initTestData() : void
    {
        // empty method
    }


    protected function clearData() : void
    {
        self::getService("coloc_matching.core.user_dto_manager")->deleteAll();
    }


    /**
     * @return UserDto
     * @throws \Exception
     */
    private function createUser() : UserDto
    {
        return $this->createProposalUser(self::getService("coloc_matching.core.user_dto_manager"), "user@test.fr",
            UserStatus::ENABLED);
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

}
