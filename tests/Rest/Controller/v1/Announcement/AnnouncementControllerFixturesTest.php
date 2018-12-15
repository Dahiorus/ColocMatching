<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Page;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\HousingType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;


    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient();
    }


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
    }


    protected function baseEndpoint() : string
    {
        return "/rest/announcements";
    }


    protected function searchFilter() : array
    {
        return array (
            "withDescription" => true,
            "address" => "Paris",
            "status" => Announcement::STATUS_ENABLED,
            "housingTypes" => [HousingType::APARTMENT, HousingType::STUDIO],
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "rentPrice", "direction" => Order::ASC)
                )
            )
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "status" => "unknown",
            "address" => "azertyui"
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $announcement) {
            self::assertEquals(Announcement::STATUS_ENABLED, $announcement["status"]);
            self::assertEquals(HousingType::APARTMENT, $announcement["housingType"]);
            self::assertNotEmpty($announcement["description"]);
        };
    }


    /**
     * @test
     * @throws \Exception
     */
    public function searchAnnouncementsWithPictures()
    {
        /** @var Page<AnnouncementDto> $announcements */
        $announcements = $this->announcementManager->list(new PageRequest(1, 5));

        foreach ($announcements as $announcement)
        {
            $path = dirname(__FILE__) . "/../../../Resources/uploads/appartement.jpg";
            $file = $this->createTmpJpegFile($path, "user-img.jpg");

            $this->announcementManager->uploadAnnouncementPicture($announcement, $file);
        }

        self::$client->request("POST", "/rest/announcements/searches", array (
            "withPictures" => true
        ));

        self::assertStatusCode(Response::HTTP_CREATED);
        $response = $this->getResponseContent();

        array ($response["content"], function ($announcement) {
            self::assertNotEmpty($announcement["_embedded"]["pictures"], "Expected the announcement to have pictures");
        });
    }

}
