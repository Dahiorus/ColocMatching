<?php

namespace App\Tests\Rest\Controller\Rest\v1\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class GroupPictureControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var GroupDto */
    private $group;

    /** @var UserDto */
    private $creator;


    protected function initServices() : void
    {
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->group = $this->createGroup();
        self::$client = self::createAuthenticatedClient($this->creator);
    }


    protected function clearData() : void
    {
        $this->groupManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return GroupDto
     * @throws \Exception
     */
    private function createGroup() : GroupDto
    {
        $this->creator = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));

        return $this->groupManager->create($this->creator, array (
            "name" => "Group test",
            "description" => "Description of the group",
            "budget" => 520
        ));
    }


    /**
     * @test
     */
    public function uploadPictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/groups/" . $this->group->getId() . "/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function uploadNonExistingPictureShouldReturn404()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/groups/0/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function uploadInvalidFileAsPictureShouldReturn400()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, null, true);

        self::$client->request("POST", "/rest/groups/" . $this->group->getId() . "/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deletePictureShouldReturn204()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        $this->groupManager->uploadGroupPicture($this->group, $file);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingPictureShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingGroupPictureShouldReturn404()
    {
        self::$client->request("DELETE", "/rest/groups/0/picture");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }

}