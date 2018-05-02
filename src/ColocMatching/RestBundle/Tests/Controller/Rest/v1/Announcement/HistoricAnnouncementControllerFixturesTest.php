<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class HistoricAnnouncementControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDto */
    private static $user;

    /** @var UserDtoManagerInterface */
    private static $userManager;


    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$userManager = self::getService("coloc_matching.core.user_dto_manager");
        self::initHistoricAnnouncements();
        self::$user = self::$userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret123&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
    }


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        self::$client = static::createAuthenticatedClient(self::$user);
    }


    protected function baseEndpoint() : string
    {
        return "/rest/history/announcements";
    }


    protected function searchFilter() : array
    {
        return array (
            "types" => array ("rent"),
            "size" => 5
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "types" => array ("unknown", "rent")
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $announcement) {
            $type = $announcement["type"];
            self::assertTrue($type == "rent");
        };
    }


    private static function initHistoricAnnouncements()
    {
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");

        $filter = new PageRequest(1, 15);
        $announcements = $announcementManager->list($filter);

        array_walk($announcements, function (AnnouncementDto $announcement) {
            /** @var UserDto $creator */
            $creator = self::$userManager->read($announcement->getCreatorId());
            self::$client = self::createAuthenticatedClient($creator);

            self::$client->request("DELETE", "/rest/announcements/" . $announcement->getId());
        });
    }


    /**
     * @test
     */
    public function getAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        static::$client->request("GET", "/rest/history/announcements", array ("size" => 500));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function searchAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        static::$client->request("POST", "/rest/history/announcements/searches", array ());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}