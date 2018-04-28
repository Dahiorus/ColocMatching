<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementPictureControllerTest extends AbstractControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDto */
    private $announcement;

    /** @var UserDto */
    private $creator;


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->announcement = $this->createAnnouncement();
        self::$client = self::createAuthenticatedClient($this->creator);
    }


    protected function clearData() : void
    {
        $this->announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    private function createAnnouncement() : AnnouncementDto
    {
        $this->creator = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));

        return $this->announcementManager->create($this->creator, array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
    }


    /**
     * @test
     */
    public function uploadAnnouncementPictureShouldReturn201()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "user-img.jpg");

        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/pictures", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function uploadAnnouncementPictureAsNonCreatorShouldReturn403()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "img.jpg");

        $user = $this->userManager->create(array (
            "email" => "visitor@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Visitor",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/pictures", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function uploadNonExistingAnnouncementPictureShouldReturn404()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "img.jpg");

        self::$client->request("POST", "/rest/announcements/0/pictures", array (), array ("file" => $file));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function uploadInvalidAnnouncementPictureShouldReturn422()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, null, true);

        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/pictures", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementPictureShouldReturn200()
    {
        $path = dirname(__FILE__) . "/../../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "img.jpg");

        $picture = $this->announcementManager->uploadAnnouncementPicture($this->announcement, $file);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/pictures/" . $picture->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function deleteNonExistingAnnouncementPictureShouldReturn200()
    {
        self::$client->request("DELETE", "/rest/announcements/" . $this->announcement->getId() . "/pictures/0");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function deleteNonExistingAnnouncementOnePictureShouldReturn404()
    {
        self::$client->request("DELETE", "/rest/announcements/0/pictures/1");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementPictureAsNonCreatorShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "visitor@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Visitor",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcement->getId() . "/pictures/1");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }
}
