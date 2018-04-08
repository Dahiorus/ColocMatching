<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class PreferenceControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $testUser;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->testUser = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));

        self::$client = self::createAuthenticatedClient($this->testUser);
    }


    protected function tearDown()
    {
        $this->userManager->delete($this->testUser);
        parent::tearDown();
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
            "type" => UserConstants::TYPE_PROPOSAL,
            "gender" => ProfileConstants::GENDER_FEMALE,
            "ageStart" => 20,
            "withDescription" => false,
            "smoker" => true,
            "diet" => ProfileConstants::DIET_VEGETARIAN
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserProfilePreferenceWithInvalidDataShouldReturn422()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "gender" => "wrong_value",
            "ageStart" => "not_number",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function putNonExistingUserProfilePreferenceShouldReturn404()
    {
        self::$client->request("PUT", "/rest/users/0/preferences/user", array (
            "diet" => ProfileConstants::DIET_VEGETARIAN,
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchUserProfilePreferenceShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "smoker" => false
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserProfilePreferenceWithInvalidDataShouldReturn422()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/user", array (
            "gender" => "wrong_value",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function patchNonExistingUserProfilePreferenceShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/users/0/preference/user", array (
            "diet" => ProfileConstants::DIET_VEGETARIAN,
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
            "types" => array (Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE),
            "endDateBefore" => "2018-05-18"
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserAnnouncementPreferenceWithInvalidDataShouldReturn422()
    {
        self::$client->request("PUT", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement", array (
            "address" => "Unknown city",
            "rentPriceStart" => 500,
            "withPictures" => false,
            "types" => array (Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE),
            "endDateBefore" => "2018-05-151",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
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
            "types" => array (Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE),
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
            "types" => array (Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE)
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserAnnouncementPreferenceWithInvalidDataShouldReturn422()
    {
        self::$client->request("PATCH", "/rest/users/" . $this->testUser->getId() . "/preferences/announcement", array (
            "types" => "wrong_value",
            "unknown" => "test"
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function patchNonExistingUserAnnouncementPreferenceShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/users/0/preference/announcement", array (
            "types" => array (Announcement::TYPE_RENT, Announcement::TYPE_SUBLEASE)
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }
}