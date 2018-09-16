<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
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
            "type" => UserType::PROPOSAL
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
        $path = dirname(__FILE__) . "/../../../Resources/uploads/appartement.jpg";
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
        $path = dirname(__FILE__) . "/../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "img.jpg");

        $user = $this->userManager->create(array (
            "email" => "visitor@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Visitor",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL
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
        $path = dirname(__FILE__) . "/../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "img.jpg");

        self::$client->request("POST", "/rest/announcements/0/pictures", array (), array ("file" => $file));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function uploadInvalidAnnouncementPictureShouldReturn400()
    {
        $path = dirname(__FILE__) . "/../../../Resources/file.txt";
        $file = new UploadedFile($path, "file.txt", "text/plain", null, true);

        self::$client->request("POST", "/rest/announcements/" . $this->announcement->getId() . "/pictures", array (),
            array ("file" => $file));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementPictureShouldReturn204()
    {
        $path = dirname(__FILE__) . "/../../../Resources/uploads/appartement.jpg";
        $file = $this->createTmpJpegFile($path, "img.jpg");

        $picture = $this->announcementManager->uploadAnnouncementPicture($this->announcement, $file);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/pictures/" . $picture->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingAnnouncementPictureShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/announcements/" . $this->announcement->getId() . "/pictures/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
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
            "type" => UserType::PROPOSAL
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcement->getId() . "/pictures/1");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }
}
