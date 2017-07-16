<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementPictureMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementPictureControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var User
     */
    private $authenticatedUser;

    /**
     * @var Announcement
     */
    private $announcement;


    protected function setUp() {
        parent::setUp();

        $this->logger = $this->client->getContainer()->get("logger");

        $this->announcementManager = self::createMock(AnnouncementManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.announcement_manager",
            $this->announcementManager);

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL);
        $this->setAuthenticatedRequest($this->authenticatedUser);

        $this->createAnnouncementMock();
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    private function createAnnouncementMock() {
        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $this->announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $this->announcement->addPicture(
            AnnouncementPictureMock::createAnnouncementPicture(1, $this->announcement, $file, "announcement-picture.jpg"));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($this->announcement);
    }


    public function testGetAnnouncementPicturesActionWith200() {
        $this->logger->info("Test getting pictures of an announcement with success");

        $id = $this->announcement->getId();

        $this->client->request("GET", "/rest/announcements/$id/pictures");
        $response = $this->getResponseContent();
        $pictures = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($this->announcement->getPictures()), $pictures);
    }


    public function testGetAnnouncementPicturesActionWith404() {
        $this->logger->info("Test getting pictures of a non existing announcement");

        $id = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->client->request("GET", "/rest/announcements/$id/location");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUploadAnnouncementPictureActionWith201() {
        $this->logger->info("Test uploading a picture for an announcement with success");

        $id = $this->announcement->getId();
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $expectedPictures = $this->announcement->getPictures();
        $expectedPictures->add(
            AnnouncementPictureMock::createAnnouncementPicture(2, $this->announcement, $file, "announcement-picture.jpg"));

        $this->announcementManager->expects($this->once())->method("uploadAnnouncementPicture")->with($this->announcement,
            $file)->willReturn($expectedPictures);

        $this->client->request("POST", "/rest/announcements/$id/pictures", array (), array ("file" => $file));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testUploadAnnouncementPictureActionWith404() {
        $this->logger->info("Test uploading a picture for a non existing announcement");

        $id = $this->announcement->getId();

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("uploadAnnouncementPicture");

        $this->client->request("POST", "/rest/announcements/$id/pictures");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetAnnouncementPictureActionWith200() {
        $this->logger->info("Test getting a picture of an announcement with success");

        $id = 1;
        $pictureId = 1;
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $expectedPicture = AnnouncementPictureMock::createAnnouncementPicture($pictureId, $this->announcement, $file,
            "announcement-picture.jpg");
        $this->announcement->addPicture($expectedPicture);

        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->with($this->announcement,
            $pictureId)->willReturn($expectedPicture);

        $this->client->request("GET", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();
        $picture = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals($pictureId, $picture["id"]);
    }


    public function testGetAnnouncementPictureActionWithAnnouncementNotFound() {
        $this->logger->info("Test getting a picture of a non existing announcement");

        $id = 1;
        $pictureId = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("readAnnouncementPicture");

        $this->client->request("GET", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetAnnouncementPictureActionWithAnnouncementPictureNotFound() {
        $this->logger->info("Test getting a non existing picture of an announcement");

        $id = 1;
        $pictureId = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($this->announcement);
        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->with($this->announcement,
            $pictureId)->willThrowException(new AnnouncementPictureNotFoundException("id", $pictureId));

        $this->client->request("GET", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteAnnouncementPictureActionWith200() {
        $this->logger->info("Test deleting a picture of an announcement with success");

        $id = 1;
        $pictureId = 1;
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $expectedPicture = AnnouncementPictureMock::createAnnouncementPicture($pictureId, $this->announcement, $file,
            "announcement-picture.jpg");
        $this->announcement->addPicture($expectedPicture);

        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->with($this->announcement,
            $pictureId)->willReturn($expectedPicture);
        $this->announcementManager->expects($this->once())->method("deleteAnnouncementPicture")->with($expectedPicture);

        $this->client->request("DELETE", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteAnnouncementPictureActionWith404() {
        $this->logger->info("Test deleting a picture of a non existing announcement");

        $id = 1;
        $pictureId = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", "$id"));
        $this->announcementManager->expects($this->never())->method("readAnnouncementPicture");
        $this->announcementManager->expects($this->never())->method("deleteAnnouncementPicture");

        $this->client->request("DELETE", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteAnnouncementPictureActionWithFailure() {
        $this->logger->info("Test deleting a non existing picture of an announcement");

        $id = 1;
        $pictureId = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($this->announcement);
        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->willThrowException(
            new AnnouncementPictureNotFoundException("id", $pictureId));
        $this->announcementManager->expects($this->never())->method("deleteAnnouncementPicture");

        $this->client->request("DELETE", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }

}