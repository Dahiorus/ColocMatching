<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementVisitControllerFixturesTest extends DataFixturesControllerTest
{
    private static $announcementId = 1;


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
        $announcement = $announcementManager->read(self::$announcementId);
        /** @var UserDto $creator */
        $creator = $userManager->read($announcement->getCreatorId());

        self::$client = self::createAuthenticatedClient($creator);
    }


    protected function baseEndpoint() : string
    {
        return "/rest/announcements/" . self::$announcementId . "/visits";
    }


    protected function searchFilter() : array
    {
        return array (
            "size" => 10
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "visitorId" => "test",
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $visit) {
            $visitedHref = $visit["_links"]["visited"]["href"];
            self::assertContains("announcements", $visitedHref, "Expected visited to be an announcement");
            self::assertContains(strval(self::$announcementId), $visitedHref,
                "Expected visited to have ID " . self::$announcementId);
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
        $announcement = $announcementManager->read(self::$announcementId);
        /** @var UserDto[] $users */
        $users = $userManager->list();

        for ($i = 0; $i < 100; $i++)
        {
            /** @var UserDto $visitor */
            $visitor = $users[ rand(0, count($users) - 1) ];
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
        $user = self::getService("coloc_matching.core.user_dto_manager")->read(2);
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
        $user = self::getService("coloc_matching.core.user_dto_manager")->read(2);
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
