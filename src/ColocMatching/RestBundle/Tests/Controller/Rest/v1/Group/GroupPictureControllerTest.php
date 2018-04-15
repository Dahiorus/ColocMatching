<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
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


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");

        $this->group = $this->createGroup();

        self::$client = self::createAuthenticatedClient($this->creator);
    }


    protected function tearDown()
    {
        $this->groupManager->deleteAll(false);
        $this->userManager->deleteAll();
        parent::tearDown();
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
    public function uploadInvalidFileAsPictureShouldReturn422()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, null, true);

        self::$client->request("POST", "/rest/groups/" . $this->group->getId() . "/picture", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deletePictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        $this->groupManager->uploadGroupPicture($this->group, $file);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function deleteNonExistingPictureShouldReturn200()
    {
        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/picture");
        self::assertStatusCode(Response::HTTP_OK);
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