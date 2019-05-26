<?php

namespace App\Tests\Rest\Controller\v1\Administration\Visit;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Tests\Rest\DataFixturesControllerTest;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class AdminVisitControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $admin;


    /**
     * @throws Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::initVisits();
    }


    protected function initServices() : void
    {
        parent::initServices();
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->admin = $this->createAdmin($this->userManager);
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


    protected function searchQueryFilter() : string
    {
        $filter = new VisitFilter();
        $filter->setVisitedClass(Announcement::class);

        return $this->stringConverter->toString($filter);
    }


    protected function invalidSearchQueryFilter() : string
    {
        return "visitedAtSince=qslsjfsdqkfjqsdlkfjqsd";
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $visit) {
            $href = $visit["_links"]["visited"]["href"];
            self::assertContains("announcements", $href, "Expected visited to be an announcement");
        };
    }


    /**
     * @throws Exception
     */
    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");

        /** @var UserDto[] $users */
        $users = $userManager->list(new PageRequest())->getContent();
        /** @var AnnouncementDto[] $announcements */
        $announcements = $announcementManager->list(new PageRequest())->getContent();
        /** @var GroupDto[] $groups */
        $groups = $groupManager->list(new PageRequest())->getContent();

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
     * @throws Exception
     */
    public function getAsNonAdminUserShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->list(new PageRequest(1, 1))->getContent()[0];
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

}
