<?php

namespace App\Tests\Rest\Controller\v1\Visit;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementVisitControllerFixturesTest extends DataFixturesControllerTest
{
    private $announcementId;


    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::initVisits();
    }


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");

        /** @var AnnouncementDto $announcement */
        $announcement = $announcementManager->list(new PageRequest(1, 1))->getContent()[0];
        $this->announcementId = $announcement->getId();
        /** @var UserDto $creator */
        $creator = $userManager->read($announcement->getCreatorId());

        self::$client = self::createAuthenticatedClient($creator);
    }


    protected function baseEndpoint() : string
    {
        return "/rest/announcements/" . $this->announcementId . "/visits";
    }


    protected function searchFilter() : array
    {
        return array (
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "createdAt", "direction" => Order::ASC)
                )
            )
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "visitorId" => "test",
            "pageable" => array (
                "size" => 5,
                "sorts" => "test"
            )
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $visit) {
            $visitedHref = $visit["_links"]["visited"]["href"];
            self::assertContains("announcements", $visitedHref, "Expected visited to be an announcement");
            self::assertContains(strval($this->announcementId), $visitedHref,
                "Expected visited to have ID " . $this->announcementId);
        };
    }


    /**
     * @throws \Exception
     */
    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");

        /** @var AnnouncementDto $announcement */
        $announcement = $announcementManager->list(new PageRequest(1, 1))->getContent()[0];
        /** @var UserDto[] $users */
        $users = $userManager->list(new PageRequest(1, 15))->getContent();

        foreach ($users as $visitor)
        {
            self::$client = self::createAuthenticatedClient($visitor);
            self::$client->request("GET", "/rest/announcements/" . $announcement->getId());
        }

        self::$client = null;
    }


    /**
     * @test
     */
    public function getAsNonCreatorShouldReturn403()
    {
        /** @var UserDto $user */
        $user = self::getService("coloc_matching.core.user_dto_manager")->list(new PageRequest(4, 1))->getContent()[0];
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("GET", $this->baseEndpoint());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function getAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", $this->baseEndpoint());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function searchAsNonCreatorShouldReturn403()
    {
        /** @var UserDto $user */
        $user = self::getService("coloc_matching.core.user_dto_manager")->list(new PageRequest(6, 1))->getContent()[0];
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("POST", $this->baseEndpoint() . "/searches", array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function searchAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("POST", $this->baseEndpoint() . "/searches", array ());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
