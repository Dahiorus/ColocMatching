<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\AnnouncementPictureNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Form\Type\Announcement\HousingType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementPictureMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\HousingMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AnnouncementControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        parent::setUp();

        $this->announcementManager = self::createMock(AnnouncementManager::class);
        $this->eventDispatcher = self::createMock(EventDispatcher::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.announcement_manager",
            $this->announcementManager);
        $this->client->getKernel()->getContainer()->set("event_dispatcher", $this->eventDispatcher);
        $this->logger = $this->client->getContainer()->get("logger");
    }


    protected function tearDown() {
        $this->logger->info("End test");
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
        $this->eventDispatcher->expects($this->once())->method("dispatch")->with(VisitEvent::LOADED, new VisitEvent($expectedAnnouncement, $user));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id");
        $response = $this->getResponseContent();
        $announcement = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($announcement);
        $this->assertEquals($expectedAnnouncement->getId(), $announcement["id"]);
    }


    public function testGetAnnouncementActionWith404() {
        $this->logger->info("Test getting a non existing announcement");

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


    public function testUploadAnnouncementPictureActionWith404() {
        $this->logger->info("Test uploading a picture for a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("uploadAnnouncementPicture");

        $this->setAuthenticatedRequest($user);
        $this->client->request("POST", "/rest/announcements/$id/pictures");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetAnnouncementPictureActionWith200() {
        $this->logger->info("Test getting a picture of an announcement with success");

        $id = 1;
        $pictureId = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $expectedPicture = AnnouncementPictureMock::createAnnouncementPicture($pictureId, $announcement, $file,
            "announcement-picture.jpg");
        $announcement->addPicture($expectedPicture);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->with($announcement,
            $pictureId)->willReturn($expectedPicture);

        $this->setAuthenticatedRequest($user);
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
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("readAnnouncementPicture");

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetAnnouncementPictureActionWithAnnouncementPictureNotFound() {
        $this->logger->info("Test getting a non existing picture of an announcement");

        $id = 1;
        $pictureId = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->with($announcement,
            $pictureId)->willThrowException(new AnnouncementPictureNotFoundException("id", $pictureId));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteAnnouncementPictureActionWith200() {
        $this->logger->info("Test deleting a picture of an announcement with success");

        $id = 1;
        $pictureId = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $file = $this->createTempFile(dirname(__FILE__) . "/../../../Resources/uploads/image.jpg",
            "announcement-img.jpg");
        $expectedPicture = AnnouncementPictureMock::createAnnouncementPicture($pictureId, $announcement, $file,
            "announcement-picture.jpg");
        $announcement->addPicture($expectedPicture);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->with($announcement,
            $pictureId)->willReturn($expectedPicture);
        $this->announcementManager->expects($this->once())->method("deleteAnnouncementPicture")->with($expectedPicture);

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testDeleteAnnouncementPictureActionWith404() {
        $this->logger->info("Test deleting a picture of a non existing announcement");

        $id = 1;
        $pictureId = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", "$id"));
        $this->announcementManager->expects($this->never())->method("readAnnouncementPicture");
        $this->announcementManager->expects($this->never())->method("deleteAnnouncementPicture");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testDeleteAnnouncementPictureActionWithFailure() {
        $this->logger->info("Test deleting a non existing picture of an announcement");

        $id = 1;
        $pictureId = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("readAnnouncementPicture")->willThrowException(
            new AnnouncementPictureNotFoundException("id", $pictureId));
        $this->announcementManager->expects($this->never())->method("deleteAnnouncementPicture");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id/pictures/$pictureId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetCandidatesActionWith200() {
        $this->logger->info("Test getting all candidates of an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $announcement->addCandidate(
            UserMock::createUser(2, "user-2@test.fr", "password2", "Toto", "Toto", UserConstants::TYPE_SEARCH));
        $announcement->addCandidate(
            UserMock::createUser(3, "user-3@test.fr", "password3", "Titi", "Titi", UserConstants::TYPE_SEARCH));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/candidates");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($announcement->getCandidates()), $response["rest"]["content"]);
    }


    public function testGetCandidatesActionWith404() {
        $this->logger->info("Test getting all candidates of a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/candidates");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testRemoveCandidateActionWith200() {
        $this->logger->info("Test removing a candidate from an announcement with success");

        $id = 1;
        $candidateId = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $announcement->addCandidate(
            UserMock::createUser($candidateId, "user-2@test.fr", "password2", "Toto", "Toto",
                UserConstants::TYPE_SEARCH));

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("removeCandidate")->with($announcement, $candidateId);

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id/candidates/$candidateId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testRemoveCandidateActionWith404() {
        $this->logger->info("Test removing a candidate from a non existing announcement");

        $id = 1;
        $candidateId = 2;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("removeCandidate");

        $this->setAuthenticatedRequest($user);
        $this->client->request("DELETE", "/rest/announcements/$id/candidates/$candidateId");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetHousingActionWith200() {
        $this->logger->info("Test get the housing of an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/housing");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetHousingActionWith404() {
        $this->logger->info("Test getting the housing of a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->setAuthenticatedRequest($user);
        $this->client->request("GET", "/rest/announcements/$id/housing");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateHousingActionWith200() {
        $this->logger->info("Test putting the housing of an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $housing = $announcement->getHousing();
        $housing->setId(1);
        $data = array (
            "type" => Housing::TYPE_HOUSE,
            "roomCount" => 6,
            "bedroomCount" => 3,
            "bathroomCount" => 1,
            "surfaceArea" => 40,
            "roomMateCount" => 2);
        $expectedHousing = HousingMock::createHousing($housing->getId(), $data["type"], $data["roomCount"],
            $data["bedroomCount"], $data["bathroomCount"], $data["surfaceArea"], $data["roomMateCount"]);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("updateHousing")->with($announcement, $data, true)->willReturn(
            $expectedHousing);

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUpdateHousingActionWith404() {
        $this->logger->info("Test putting the housing of a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("updateHousing");

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/announcements/$id/housing", array ());
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateHousingActionWith400() {
        $this->logger->info("Test putting the housing of an announcement with invalid data");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $housing = $announcement->getHousing();
        $housing->setId(1);
        $data = array ("type" => "toto", "roomCount" => 6, "bedroomCount" => 3);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("updateHousing")->with($announcement, $data, true)->willThrowException(
            new InvalidFormDataException("Exception from testUpdateHousingActionWith400()",
                $this->getForm(HousingType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("PUT", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testPatchHousingActionWith200() {
        $this->logger->info("Test patching the housing of an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $housing = $announcement->getHousing();
        $housing->setId(1);
        $data = array ("type" => Housing::TYPE_HOUSE);
        $expectedHousing = HousingMock::createHousing($housing->getId(), $data["type"], $housing->getRoomCount(),
            $housing->getBedroomCount(), $housing->getBathroomCount(), $housing->getSurfaceArea(),
            $housing->getRoomMateCount());

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("updateHousing")->with($announcement, $data, false)->willReturn(
            $expectedHousing);

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testPatchHousingActionWith404() {
        $this->logger->info("Test patching the housing of a non existing announcement");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("updateHousing");

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/announcements/$id/housing", array ());
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPatchHousingActionWith400() {
        $this->logger->info("Test patching the housing of an announcement with invalid data");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());
        $housing = $announcement->getHousing();
        $housing->setId(1);
        $data = array ("type" => "toto");

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("updateHousing")->with($announcement, $data, false)->willThrowException(
            new InvalidFormDataException("Exception from testPatchHousingActionWith400()",
                $this->getForm(HousingType::class)->getErrors()));

        $this->setAuthenticatedRequest($user);
        $this->client->request("PATCH", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }

}