<?php

namespace App\Tests\Rest\Controller\Rest\v1\Administration\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class HistoricAnnouncementControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private static $userManager;

    /** @var UserDto */
    private $admin;


    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$userManager = self::getService("coloc_matching.core.user_dto_manager");
        self::initHistoricAnnouncements();
    }


    protected function initTestData() : void
    {
        $this->admin = self::$userManager->create(array (
            "email" => "apu-user@test.fr",
            "plainPassword" => "password",
            "firstName" => "Api",
            "lastName" => "User",
            "type" => UserConstants::TYPE_SEARCH
        ));
        $this->admin = self::$userManager->addRole($this->admin, "ROLE_ADMIN");

        self::$client = self::createAuthenticatedClient($this->admin);
    }


    protected function clearData() : void
    {
        if (!empty($this->admin))
        {
            self::$userManager->delete($this->admin);
            $this->admin = null;
        }
    }


    protected function baseEndpoint() : string
    {
        return "/rest/admin/history/announcements";
    }


    protected function searchFilter() : array
    {
        return array (
            "types" => array ("rent"),
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "title", "direction" => Order::ASC)
                )
            )
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

        self::$client = null;
    }


    /**
     * @test
     */
    public function getAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        static::$client->request("GET", $this->baseEndpoint(), array ("size" => 500));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function searchAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        static::$client->request("POST", $this->baseEndpoint() . "/searches", array ());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
