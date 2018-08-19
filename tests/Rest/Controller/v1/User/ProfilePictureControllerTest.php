<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProfilePictureControllerTest extends AbstractControllerTest
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
    public function uploadProfilePictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/users/" . $this->testUser->getId() . "/picture", array (),
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

        self::$client->request("POST", "/rest/users/" . $this->testUser->getId() . "/picture", array (),
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

        $this->userManager->uploadProfilePicture($this->testUser, $file);

        self::$client->request("DELETE", "/rest/users/" . $this->testUser->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingProfilePictureShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/users/" . $this->testUser->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingUserProfilePictureShouldReturn404()
    {
        self::$client->request("DELETE", "/rest/users/0/picture");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }

}