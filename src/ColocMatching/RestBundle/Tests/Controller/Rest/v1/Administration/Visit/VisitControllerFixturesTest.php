<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Administration\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Order;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class VisitControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $admin;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::initVisits();
    }


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->admin = $this->userManager->create(array (
            "email" => "apu-user@test.fr",
            "plainPassword" => "password",
            "firstName" => "Api",
            "lastName" => "User",
            "type" => UserConstants::TYPE_SEARCH
        ));
        $this->admin = $this->userManager->addRole($this->admin, "ROLE_ADMIN");

        self::$client = self::createAuthenticatedClient($this->admin);
    }


    protected function clearData() : void
    {
        if (!empty($this->admin))
        {
            $this->userManager->delete($this->admin);
            $this->admin = null;
        }
    }


    protected function baseEndpoint() : string
    {
        return "/rest/admin/visits";
    }


    protected function searchFilter() : array
    {
        return array (
            "visitedClass" => Announcement::class,
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "visitedClass", "direction" => Order::ASC)
                )
            )
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "visitedId" => "test"
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $visit) {
            $href = $visit["_links"]["visited"]["href"];
            self::assertContains("announcements", $href, "Expected visited to be an announcement");
        };
    }


    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");

        $users = $userManager->list(new PageRequest());
        $announcements = $announcementManager->list(new PageRequest());
        $groups = $groupManager->list(new PageRequest());

        foreach ($users as $user)
        {
            /** @var UserDto $visitor */
            $visitor = $users[ rand(0, count($users) - 1) ];
            self::$client = self::createAuthenticatedClient($visitor);
            self::$client->request("GET", "/rest/users/" . $user->getId());
        }

        foreach ($announcements as $announcement)
        {
            /** @var UserDto $visitor */
            $visitor = $users[ rand(0, count($users) - 1) ];
            self::$client = self::createAuthenticatedClient($visitor);
            self::$client->request("GET", "/rest/announcements/" . $announcement->getId());
        }

        foreach ($groups as $group)
        {
            /** @var UserDto $visitor */
            $visitor = $users[ rand(0, count($users) - 1) ];
            self::$client = self::createAuthenticatedClient($visitor);
            self::$client->request("GET", "/rest/groups/" . $group->getId());
        }

        self::$client = null;
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getAsNonAdminUserShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read(1);
        self::$client = self::createAuthenticatedClient($user);

        static::$client->request("GET", $this->baseEndpoint());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function getAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        static::$client->request("GET", $this->baseEndpoint());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function searchAsNonAdminUserShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read(1);
        self::$client = self::createAuthenticatedClient($user);

        static::$client->request("POST", $this->baseEndpoint() . "/searches", array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function searchAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        static::$client->request("POST", $this->baseEndpoint() . "/searches");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
