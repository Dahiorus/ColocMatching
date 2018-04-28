<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $testUser;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->testUser = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        self::$client = self::createAuthenticatedClient($this->testUser);
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function getUserProfileShouldReturn200()
    {
        self::$client->request("GET", "/rest/users/" . $this->testUser->getId() . "/profile");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingUserProfileShouldReturn404()
    {
        self::$client->request("GET", "/rest/users/0/profile");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getUserProfileAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/users/" . $this->testUser->getId() . "/profile");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function putUserProfileShouldReturn200()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/profile", array (
            "gender" => ProfileConstants::GENDER_FEMALE,
            "birthDate" => "1980-10-25",
            "description" => "This is a description",
            "smoker" => true,
            "diet" => ProfileConstants::DIET_VEGETARIAN
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserProfileWithInvalidDataShouldReturn422()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/profile", array (
            "gender" => "wrong_value",
            "birthDate" => "1980-10-25",
            "description" => "This is a description",
            "smoker" => 5,
            "diet" => ProfileConstants::DIET_VEGETARIAN,
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function putNonExistingUserProfileShouldReturn404()
    {
        self::$client->request("PUT", "/rest/users/0/profile", array (
            "birthDate" => "1980-10-25",
            "description" => "This is a description",
            "diet" => ProfileConstants::DIET_VEGETARIAN,
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchUserProfileShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/profile", array (
            "birthDate" => "1980-10-25"
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserProfileWithInvalidDataShouldReturn422()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/profile", array (
            "gender" => "wrong_value",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function patchNonExistingUserProfileShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/users/0/profile", array (
            "diet" => ProfileConstants::DIET_VEGETARIAN,
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }

}
