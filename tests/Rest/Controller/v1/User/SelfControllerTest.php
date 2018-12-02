<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class SelfControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initServices() : void
    {
        /** @var UserDtoManagerInterface $userManager */
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "user@test.fr");
        self::$client = self::createAuthenticatedClient($user);
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function getSelfShouldReturn200()
    {
        self::$client->request("GET", "/rest/me");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getSelfAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/me");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function updateSelfStatusShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me/status", array ("value" => UserStatus::ENABLED));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateSelfStatusWithInvalidValueShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/me/status", array ("value" => UserStatus::BANNED));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function updateSelfPasswordShouldReturn200()
    {
        self::$client->request("POST", "/rest/me/password", array (
            "oldPassword" => "Secret&1234",
            "newPassword" => "new_password"
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateSelfPasswordWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/me/password", array (
            "oldPassword" => "Secret",
            "newPassword" => "new_password"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putSelfActionShouldReturn200()
    {
        self::$client->request("PUT", "/rest/me", array (
            "email" => "user@test.fr",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putSelfActionWithInvalidDataShouldReturn200()
    {
        self::$client->request("PUT", "/rest/me", array (
            "email" => "user@test.fr",
            "firstName" => "User",
            "lastName" => "",
            "type" => UserType::PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchSelfShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me", array (
            "type" => UserType::PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchSelfWithInvalidShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/me", array (
            "type" => 51
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function uploadProfilePictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/me/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function uploadNonExistingUserProfilePictureShouldReturn404()
    {
        $path = dirname(__FILE__) . "/../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/users/0/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function uploadInvalidFileAsProfilePictureShouldReturn400()
    {
        $path = dirname(__FILE__) . "/../../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, true);

        self::$client->request("POST", "/rest/me/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteProfilePictureShouldReturn204()
    {
        $path = dirname(__FILE__) . "/../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");
        self::$client->request("POST", "/rest/me/picture", [], array ("file" => $file));

        self::$client->request("DELETE", "/rest/me/picture");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingProfilePictureShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/me/picture");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function getSelfVisitsShouldReturn200()
    {
        self::$client->request("GET", "/rest/me/visits");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getSelfHistoricAnnouncementsShouldReturn200()
    {
        self::$client->request("GET", "/rest/me/history/announcements");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getSelfConversationsShouldReturn200()
    {
        self::$client->request("GET", "/rest/me/conversations");
        self::assertStatusCode(Response::HTTP_OK);
    }

}
