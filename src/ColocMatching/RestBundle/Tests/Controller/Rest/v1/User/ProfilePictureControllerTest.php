<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProfilePictureControllerTest extends AbstractControllerTest
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

        /** @var UserDtoManagerInterface $userManager */
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
        $this->userManager->deleteAll();
        parent::tearDown();
    }


    /**
     * @test
     */
    public function uploadProfilePictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
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
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/users/0/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function uploadInvalidFileAsProfilePictureShouldReturn422()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, null, true);

        self::$client->request("POST", "/rest/users/" . $this->testUser->getId() . "/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteProfilePictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        $this->userManager->uploadProfilePicture($this->testUser, $file);

        self::$client->request("DELETE", "/rest/users/" . $this->testUser->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function deleteNonExistingProfilePictureShouldReturn200()
    {
        self::$client->request("DELETE", "/rest/users/" . $this->testUser->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_OK);
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