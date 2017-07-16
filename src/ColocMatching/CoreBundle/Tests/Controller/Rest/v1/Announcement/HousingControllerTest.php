<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\HousingType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\HousingMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class HousingControllerTest extends RestTestCase {

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

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($this->announcement);
    }


    public function testGetHousingActionWith200() {
        $this->logger->info("Test get the housing of an announcement with success");

        $id = $this->announcement->getId();

        $this->client->request("GET", "/rest/announcements/$id/housing");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetHousingActionWith404() {
        $this->logger->info("Test getting the housing of a non existing announcement");

        $id = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));

        $this->client->request("GET", "/rest/announcements/$id/housing");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateHousingActionWith200() {
        $this->logger->info("Test putting the housing of an announcement with success");

        $id = $this->announcement->getId();
        $housing = $this->announcement->getHousing();
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

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($this->announcement);
        $this->announcementManager->expects($this->once())->method("updateHousing")->with($this->announcement, $data, true)
            ->willReturn($expectedHousing);

        $this->client->request("PUT", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testUpdateHousingActionWith404() {
        $this->logger->info("Test putting the housing of a non existing announcement");

        $id = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("updateHousing");

        $this->client->request("PUT", "/rest/announcements/$id/housing", array ());
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testUpdateHousingActionWith400() {
        $this->logger->info("Test putting the housing of an announcement with invalid data");

        $id = $this->announcement->getId();
        $housing = $this->announcement->getHousing();
        $housing->setId(1);
        $data = array ("type" => "toto", "roomCount" => 6, "bedroomCount" => 3);

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($this->announcement);
        $this->announcementManager->expects($this->once())->method("updateHousing")->with($this->announcement, $data, true)
            ->willThrowException(new InvalidFormDataException("Exception from testUpdateHousingActionWith400()",
                $this->getForm(HousingType::class)->getErrors()));

        $this->client->request("PUT", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testPatchHousingActionWith200() {
        $this->logger->info("Test patching the housing of an announcement with success");

        $id = $this->announcement->getId();
        $housing = $this->announcement->getHousing();
        $housing->setId(1);
        $data = array ("type" => Housing::TYPE_HOUSE);
        $expectedHousing = HousingMock::createHousing($housing->getId(), $data["type"], $housing->getRoomCount(),
            $housing->getBedroomCount(), $housing->getBathroomCount(), $housing->getSurfaceArea(),
            $housing->getRoomMateCount());

        $this->announcementManager->expects($this->once())->method("updateHousing")->with($this->announcement, $data, false)
            ->willReturn($expectedHousing);

        $this->client->request("PATCH", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testPatchHousingActionWith404() {
        $this->logger->info("Test patching the housing of a non existing announcement");

        $id = 1;

        $this->announcementManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects($this->never())->method("updateHousing");

        $this->client->request("PATCH", "/rest/announcements/$id/housing", array ());
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testPatchHousingActionWith400() {
        $this->logger->info("Test patching the housing of an announcement with invalid data");

        $id = $this->announcement->getId();
        $housing = $this->announcement->getHousing();
        $housing->setId(1);
        $data = array ("type" => "toto");

        $this->announcementManager->expects($this->once())->method("updateHousing")->with($this->announcement, $data, false)
            ->willThrowException(new InvalidFormDataException("Exception from testPatchHousingActionWith400()",
                $this->getForm(HousingType::class)->getErrors()));

        $this->client->request("PATCH", "/rest/announcements/$id/housing", $data);
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }
}