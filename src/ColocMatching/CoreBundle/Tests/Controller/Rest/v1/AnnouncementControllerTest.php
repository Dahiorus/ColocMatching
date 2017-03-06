<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Component\HttpFoundation\Response;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;

class AnnouncementControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementManager;


    protected function setUp() {
        parent::setUp();

        $this->announcementManager = self::createMock(AnnouncementManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.announcement_manager",
            $this->announcementManager);
    }


    public function testGetAnnouncementsActionWith200() {
        $this->logger->info("Test getting announcements with status code 200");

        $size = RequestConstants::DEFAULT_LIMIT;
        $announcements = $this->createAnnouncementList($size);
        $this->announcementManager->expects($this->once())->method("list")->with(new AnnouncementFilter())->willReturn(
            array_slice($announcements, 0, $size));
        $this->announcementManager->expects($this->once())->method("countAll")->willReturn($size);

        $this->client->request("GET", "/rest/announcements/");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);

        $restList = $response["content"];
        $this->assertNotNull($restList["data"]);
        $this->assertEquals($size, $restList["size"],
            sprintf("Expected to get an array of %d elements, but got %d", $size, $restList["size"]));
        $this->assertEquals($size, $restList["total"],
            sprintf("Expected total elements to equal to %d, but got %d", $size, $restList["total"]));
    }


    public function testGetAnnouncementsActionWith206() {
        $this->logger->info("Test getting announcements with status code 206");

        $size = RequestConstants::DEFAULT_LIMIT;
        $total = $size + 1;
        $announcements = $this->createAnnouncementList($total);
        $this->announcementManager->expects($this->once())->method("list")->with(new AnnouncementFilter())->willReturn(
            array_slice($announcements, 0, $size));
        $this->announcementManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/announcements/");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);

        $restList = $response["content"];
        $this->assertNotNull($restList["data"]);
        $this->assertEquals($size, $restList["size"],
            sprintf("Expected to get an array of %d elements, but got %d", $size, $restList["size"]));
        $this->assertEquals($total, $restList["total"],
            sprintf("Expected total elements to equal to %d, but got %d", $total, $restList["total"]));
    }


    public function testCreateAnnouncementActionWith201() {
        $this->logger->info("Test creating announcement with status code 201");

        $authUser = $this->createUser("auth-user@test.fr", "password", true);
        $authUser->setType(UserConstants::TYPE_SEARCH);
        $authToken = $this->mockAuthToken($authUser);

        $data = array (
            "title" => "Annonce test",
            "type" => Announcement::TYPE_SHARING,
            "minPrice" => 680,
            "startDate" => "08/03/2017",
            "location" => "3 avenue d'Italie Paris",
            "description" => "Colocation test");
        $announcement = $this->createAnnouncement($authUser, $data["title"], $data["type"], $data["minPrice"],
            $data["startDate"], $data["location"]);
        $announcement->setDescription($data["description"]);
        $this->announcementManager->expects($this->once())->method("create")->with($authUser, $data)->willReturn(
            $announcement);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("POST", "/rest/announcements/", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testCreateAnnouncementWith400() {
        $this->logger->info("Test creating announcement with status code 400");

        $authUser = $this->createUser("auth-user@test.fr", "password", true);
        $authUser->setType(UserConstants::TYPE_SEARCH);
        $authToken = $this->mockAuthToken($authUser);

        $form = $this->createFormType(AnnouncementType::class);
        $data = array ("minPrice" => 680);
        $this->announcementManager->expects($this->once())->method("create")->with($authUser, $data)->willThrowException(
            new InvalidFormDataException("Invalid data", $form->getErrors(true, true)));

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("POST", "/rest/announcements/", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testGetAnnouncementActionWith200() {
        $this->logger->info("Test getting an existing announcement with status code 200");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $announcement = $this->createAnnouncement($this->createUser("user@test.fr", "password", true), "Annonce test",
            Announcement::TYPE_RENT, 1200, "05/08/2017", "Taverny");
        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("GET", "/rest/announcements/$id");
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);

        $restData = $response["content"];
        $this->assertNotNull($restData["data"]);
    }


    public function testUpdateAnnouncementActionWith200() {
        $this->logger->info("Test updating an existing announcement with status code 200");

        $id = 1;
        $authToken = $this->mockAuthToken($this->createUser("auth-user@test.fr", "password", true));

        $creator = $this->createUser("user@test.fr", "password", true);
        $data = array (
            "title" => "Annonce test",
            "type" => Announcement::TYPE_SHARING,
            "minPrice" => 680,
            "startDate" => "08/05/2017",
            "location" => "3 avenue d'Italie Paris");
        $announcement = $this->createAnnouncement($creator, $data["title"], $data["type"], 780, "08/02/2017",
            $data["location"]);
        $updatedAnnouncement = $this->createAnnouncement($creator, $data["title"], $data["type"], $data["minPrice"],
            $data["startDate"], $data["location"]);
        $this->announcementManager->expects($this->once())->method("read")->with($id)->willReturn($announcement);
        $this->announcementManager->expects($this->once())->method("update")->with($announcement, $data)->willReturn(
            $updatedAnnouncement);

        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $authToken));
        $this->client->request("PUT", "/rest/announcements/$id", $data);
        $response = $this->getResponseData();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);

        $restData = $response["content"];
        $this->assertNotNull($restData["data"]);
        $this->assertEquals($updatedAnnouncement->getMinPrice(), $restData["data"]["minPrice"],
            sprintf("Expected announcement min price to be equal to %d, but got %d",
                $updatedAnnouncement->getMinPrice(), $restData["data"]["minPrice"]));
    }


    private function createAnnouncementList(int $totalElements): array {
        $announcements = array ();

        for ($i = 1; $i <= $totalElements; $i++) {
            $creator = $this->createUser("user-$i@test.fr", "password", true);
            $announcement = $this->createAnnouncement($creator, "Annonce $i", Announcement::TYPE_RENT, 355,
                "05/03/2017", "Paris");
            $announcements[] = $announcement;
        }

        return $announcements;
    }


    private function createAnnouncement(User $creator, string $title, string $type, int $minPrice, string $startDate,
        string $location): Announcement {
        $announcement = new Announcement($creator);

        $announcement->setTitle($title);
        $announcement->setType($type);
        $announcement->setMinPrice($minPrice);
        $announcement->setStartDate(\DateTime::createFromFormat($this->dateFormat, $startDate));
        $announcement->setLocation($this->generateAddress($location));

        return $announcement;
    }


    private function generateAddress(string $formattedAddress): Address {
        $transformer = new AddressTypeToAddressTransformer();

        return $transformer->reverseTransform($formattedAddress);
    }

}