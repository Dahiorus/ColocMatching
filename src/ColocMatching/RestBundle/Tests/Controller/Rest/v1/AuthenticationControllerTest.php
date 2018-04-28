<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient();
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
     * @return UserDto
     * @throws \Exception
     */
    private function createUser() : UserDto
    {
        return $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret&1234",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function authenticateUserShouldReturn200()
    {
        $user = $this->createUser();

        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => $user->getUsername(), "_password" => "Secret&1234"));
        self::assertStatusCode(Response::HTTP_OK);
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
    public function missingDataShouldReturn422()
    {
        static::$client->request("POST", "/rest/auth/tokens",
            array ("_username" => "", "_password" => "password"));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
