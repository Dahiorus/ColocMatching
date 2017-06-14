<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementPictureMock;

class AnnouncementControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementManager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        parent::setUp();

        $this->announcementManager = self::createMock(AnnouncementManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.announcement_manager",
            $this->announcementManager);
        $this->logger = $this->client->getContainer()->get("logger");
    }


    public function testGetAnnouncementsActionWith200() {
        $this->logger->info("Test getting announcements with status code 200");

        $total = 50;
        $filter = new PageableFilter();
        $filter->setPage(3);
        $announcements = AnnouncementMock::createAnnouncementPage($filter, $total);

        $this->announcementManager->expects($this->once())->method("list")->with($filter)->willReturn($announcements);
        $this->announcementManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/announcements", array ("page" => 3));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($announcements), $response["rest"]["content"]);
        $this->assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetAnnouncementsActionWith206() {
        $this->logger->info("Test getting announcements with status code 206");

        $total = 50;
        $filter = new PageableFilter();
        $announcements = AnnouncementMock::createAnnouncementPage($filter, $total);

        $this->announcementManager->expects($this->once())->method("list")->with($filter)->willReturn($announcements);
        $this->announcementManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/announcements");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertCount(count($announcements), $response["rest"]["content"]);
    }


    public function testCreateAnnouncementActionWith201() {
        $this->logger->info("Test creating announcement with status code 201");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "title" => "Annonce test",
            "type" => Announcement::TYPE_SHARING,
            "rentPrice" => 680,
            "startDate" => "08/03/2017",
            "location" => "3 avenue d'Italie Paris",
            "description" => "Colocation test");
        $expectedAnnouncement = AnnouncementMock::createAnnouncement(1, $user, $data["location"], $data["title"],
            $data["type"], $data["rentPrice"], \DateTime::createFromFormat($this->dateFormat, $data["startDate"]));
        $expectedAnnouncement->setDescription($data["description"]);

        $this->announcementManager->expects($this->once())->method("create")->with($user, $data)->willReturn(
            $expectedAnnouncement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/announcements", $data);
        $response = $this->getResponseContent();
        $announcement = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);
        $this->assertNotNull($announcement);
        $this->assertEquals($expectedAnnouncement->getId(), $announcement["id"]);
        $this->assertEquals($expectedAnnouncement->getTitle(), $announcement["title"]);
    }


    public function testCreateAnnouncementActionWith400() {
        $this->logger->info("Test creating announcement with status code 400");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("rentPrice" => 680);

        $this->announcementManager->expects($this->once())->method("create")->with($user, $data)->willThrowException(
            new InvalidFormDataException("Invalid data", $this->getForm(AnnouncementType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/announcements", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testCreateAnnouncementActionWith403() {
        $this->logger->info("Test creating announcement with status code 400");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_SEARCH);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/announcements",
            array (
                "title" => "Annonce test",
                "type" => Announcement::TYPE_SHARING,
                "rentPrice" => 680,
                "startDate" => "08/03/2017",
                "location" => "3 avenue d'Italie Paris",
                "description" => "Colocation test"));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    public function testCreateAnnouncementActionWith422() {
        $this->logger->info("Test creating an announcement with status code 422");

        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $user->setAnnouncement(
            AnnouncementMock::createAnnouncement(5, $user, "Paris 75003", "Announcement test",
                Announcement::TYPE_SHARING, 783, new \DateTime()));
        $data = array (
            "title" => "Annonce test",
            "type" => Announcement::TYPE_SHARING,
            "rentPrice" => 680,
            "startDate" => "08/03/2017",
            "location" => "3 avenue d'Italie Paris",
            "description" => "Colocation test");

        $this->announcementManager->expects($this->once())->method("create")->with($user, $data)->willThrowException(
            new UnprocessableEntityHttpException());

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/announcements", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }


    public function testGetAnnouncementActionWith200() {
        $this->logger->info("Test getting an existing announcement with status code 200");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $expectedAnnouncement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75003", "Announcement test",
            Announcement::TYPE_SHARING, 783, new \DateTime());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($expectedAnnouncement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id");
        $response = $this->getResponseContent();
        $announcement = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($announcement);
        $this->assertEquals($expectedAnnouncement->getId(), $announcement["id"]);
    }


    public function testGetAnnouncementActionWith404() {
        $this->logger->info("Test getting an non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateAnnouncementActionWith200() {
        $this->logger->info("Test updating an existing announcement with status code 200");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $data = array (
            "title" => "New title",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => $announcement->getRentPrice(),
            "startDate" => $announcement->getStartDate()->format($this->dateFormat),
            "endDate" => (new \DateTime("+6 months"))->format($this->dateFormat),
            "location" => "Paris 75008");
        $expectedAnnouncement = AnnouncementMock::createAnnouncement($announcement->getId(),
            $announcement->getCreator(), $data["location"], $data["title"], $data["type"], $data["rentPrice"],
            \DateTime::createFromFormat($this->dateFormat, $data["startDate"]));
        $expectedAnnouncement->setEndDate(\DateTime::createFromFormat($this->dateFormat, $data["endDate"]));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("update")->with($announcement, $data)->willReturn(
            $expectedAnnouncement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/announcements/$id", $data);
        $response = $this->getResponseContent();
        $updatedAnnouncement = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($updatedAnnouncement);
        $this->assertEquals($expectedAnnouncement->getId(), $updatedAnnouncement["id"]);
        $this->assertEquals($expectedAnnouncement->getEndDate()->format($this->dateFormat),
            $updatedAnnouncement["endDate"]);
    }


    public function testUpdateAnnouncementActionWith404() {
        $this->logger->info("Test updating an announcement with status code 404");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "title" => "New title",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 300,
            "startDate" => (new \DateTime())->format($this->dateFormat),
            "endDate" => (new \DateTime("+6 months"))->format($this->dateFormat),
            "location" => "Paris 75008");

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("update");

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/announcements/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateAnnouncementActionWith400() {
        $this->logger->info("Test updating an announcement with status code 400");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $data = array (
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => $announcement->getRentPrice(),
            "startDate" => $announcement->getStartDate()->format($this->dateFormat),
            "endDate" => (new \DateTime("+6 months"))->format($this->dateFormat),
            "location" => "Paris 75008");

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("update")->with($announcement, $data)->willThrowException(
            new InvalidFormDataException("Exception from testUpdateAnnouncementActionWith400()",
                $this->getForm(AnnouncementType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/announcements/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testPatchAnnouncementActionWith200() {
        $this->logger->info("Test patching an existing announcement with status code 200");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $data = array ("title" => "New title", "endDate" => (new \DateTime("+6 months"))->format($this->dateFormat));
        $expectedAnnouncement = AnnouncementMock::createAnnouncement($announcement->getId(),
            $announcement->getCreator(), "Paris 75008", $data["title"], $announcement->getType(),
            $announcement->getRentPrice(), $announcement->getStartDate());
        $expectedAnnouncement->setEndDate(\DateTime::createFromFormat($this->dateFormat, $data["endDate"]));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("update")->with($announcement, $data)->willReturn(
            $expectedAnnouncement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/announcements/$id", $data);
        $response = $this->getResponseContent();
        $updatedAnnouncement = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($updatedAnnouncement);
        $this->assertEquals($expectedAnnouncement->getId(), $updatedAnnouncement["id"]);
        $this->assertEquals($expectedAnnouncement->getEndDate()->format($this->dateFormat),
            $updatedAnnouncement["endDate"]);
    }


    public function testPatchAnnouncementActionWith404() {
        $this->logger->info("Test patching an announcement with status code 404");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $data = array ("title" => "New title");

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("update");

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/announcements/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPatchAnnouncementActionWith400() {
        $this->logger->info("Test patching an announcement with status code 400");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $data = array ("title" => null);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("update")->with($announcement, $data)->willThrowException(
            new InvalidFormDataException("Exception from testPatchAnnouncementActionWith400()",
                $this->getForm(AnnouncementType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/announcements/$id", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testDeleteAnnouncementActionWithSuccess() {
        $this->logger->info("Test deleting an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("delete")->with($announcement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteAnnouncementActionWithFailure() {
        $this->logger->info("Test deleting an announcement with failure");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("delete");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testSearchAnnouncementsActionWith200() {
        $this->logger->info("Test searching announcements with status code 200");

        $total = 56;
        $filter = new AnnouncementFilter();
        $filter->setPage(3);
        $announcements = AnnouncementMock::createAnnouncementPage($filter, $total);

        $this->announcementManager->expects($this->once())->method("search")->willReturn($announcements);
        $this->announcementManager->expects($this->once())->method("countBy")->willReturn($total);

        $this->client->request("POST", "/rest/announcements/searches", array ("page" => 3));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($announcements), $response["rest"]["content"]);
        $this->assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testSearchAnnouncementsActionWith206() {
        $this->logger->info("Test searching announcements with status code 206");

        $total = 50;
        $filter = new AnnouncementFilter();
        $announcements = AnnouncementMock::createAnnouncementPage($filter, $total);

        $this->announcementManager->expects($this->once())->method("search")->willReturn($announcements);
        $this->announcementManager->expects($this->once())->method("countBy")->willReturn($total);

        $this->client->request("POST", "/rest/announcements/searches");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertCount(count($announcements), $response["rest"]["content"]);
    }


    public function testGetAnnouncementLocationActionWith200() {
        $this->logger->info("Test getting the location of an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/location");
        $response = $this->getResponseContent();
        $location = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertEquals($announcement->getLocation()->getFormattedAddress(), $location["formattedAddress"]);
    }


    public function testGetAnnouncementLocationActionWith404() {
        $this->logger->info("Test getting the location of a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/location");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetAnnouncementPicturesActionWith200() {
        $this->logger->info("Test getting pictures of an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $announcement->addPicture(
            AnnouncementPictureMock::createAnnouncementPicture(1, $announcement, $file, "announcement-picture.jpg"));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/pictures");
        $response = $this->getResponseContent();
        $pictures = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($announcement->getPictures()), $pictures);
    }


    public function testGetAnnouncementPicturesActionWith404() {
        $this->logger->info("Test getting pictures of a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/location");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUploadAnnouncementPictureActionWith201() {
        $this->logger->info("Test uploading a picture for an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $expectedPictures = $announcement->getPictures();
        $expectedPictures->add(
            AnnouncementPictureMock::createAnnouncementPicture(1, $announcement, $file, "announcement-picture.jpg"));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("uploadAnnouncementPicture")->with($announcement,
            $file)->willReturn($expectedPictures);

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/announcements/$id/pictures", array (), array ("file" => $file));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);
    }

}