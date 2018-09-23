<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\User\UserGender;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class PreferenceControllerTest extends AbstractControllerTest
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
            "type" => UserType::SEARCH
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
    public function getUserProfilePreferenceShouldReturn200()
    {
        self::$client->request("GET", "/rest/users/" . $this->testUser->getId() . "/preferences/user");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingUserProfilePreferenceShouldReturn404()
    {
        self::$client->request("GET", "/rest/users/0/preferences/user");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getUserProfilePreferenceAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/users/" . $this->testUser->getId() . "/preferences/user");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function putUserProfilePreferenceShouldReturn200()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "type" => UserType::PROPOSAL,
            "gender" => UserGender::FEMALE,
            "ageStart" => 20,
            "withDescription" => false
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserProfilePreferenceWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "gender" => "wrong_value",
            "ageStart" => "not_number",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putNonExistingUserProfilePreferenceShouldReturn404()
    {
        self::$client->request("PUT", "/rest/users/0/preferences/user", array (
            "gender" => UserGender::FEMALE,
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchUserProfilePreferenceShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "withDescription" => false
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserProfilePreferenceWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "gender" => "wrong_value",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchNonExistingUserProfilePreferenceShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/users/0/preference/user", array (
            "gender" => UserGender::FEMALE,
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getUserAnnouncementPreferenceShouldReturn200()
    {
        self::$client->request("GET", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingUserAnnouncementPreferenceShouldReturn404()
    {
        self::$client->request("GET", "/rest/users/0/preferences/announcement");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getUserAnnouncementPreferenceAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function putUserAnnouncementPreferenceShouldReturn200()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement", array (
            "address" => "Paris 75010",
            "rentPriceStart" => 500,
            "withPictures" => false,
            "types" => array (AnnouncementType::RENT, AnnouncementType::SUBLEASE),
            "endDateBefore" => "2018-05-18"
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserAnnouncementPreferenceWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement", array (
            "address" => "Unknown city",
            "rentPriceStart" => 500,
            "withPictures" => false,
            "types" => array (AnnouncementType::RENT, AnnouncementType::SUBLEASE),
            "endDateBefore" => "2018-05-151",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putNonExistingUserAnnouncementPreferenceShouldReturn404()
    {
        self::$client->request("PUT", "/rest/users/0/preferences/announcement", array (
            "address" => "Paris 75010",
            "rentPriceStart" => 500,
            "withPictures" => false,
            "types" => array (AnnouncementType::RENT, AnnouncementType::SUBLEASE),
            "endDateBefore" => "2018-05-18"
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchUserAnnouncementPreferenceShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement", array (
            "types" => array (AnnouncementType::RENT, AnnouncementType::SUBLEASE)
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserAnnouncementPreferenceWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement", array (
            "types" => "wrong_value",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchNonExistingUserAnnouncementPreferenceShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/users/0/preference/announcement", array (
            "types" => array (AnnouncementType::RENT, AnnouncementType::SUBLEASE)
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }
}