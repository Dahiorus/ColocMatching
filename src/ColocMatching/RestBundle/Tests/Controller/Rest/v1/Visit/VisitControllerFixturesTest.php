<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class VisitControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $apiUser;


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
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");

        $this->apiUser = $this->userManager->create(array (
            "email" => "apu-user@test.fr",
            "plainPassword" => "password",
            "firstName" => "Api",
            "lastName" => "User",
            "type" => UserConstants::TYPE_SEARCH
        ));
        $this->apiUser = $this->userManager->addRole($this->apiUser, "ROLE_API");

        self::$client = self::createAuthenticatedClient($this->apiUser);
    }


    protected function tearDown()
    {
        $this->userManager->delete($this->apiUser);
        parent::tearDown();
    }


    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var AnnouncementDtoManagerInterface $announcementManager */
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");

        $users = $userManager->list(new PageableFilter());
        $announcements = $announcementManager->list(new PageableFilter());
        $groups = $groupManager->list(new PageableFilter());

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
     */
    public function getOnePageShouldReturn206()
    {
        static::$client->request("GET", "/rest/visits");
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllShouldReturn200()
    {
        static::$client->request("GET", "/rest/visits", array ("size" => 100));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getAsNonApiUserShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read(1);
        self::$client = self::createAuthenticatedClient($user);

        static::$client->request("GET", "/rest/visits");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function getAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        static::$client->request("GET", "/rest/visits");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function searchOnePageShouldReturn206()
    {
        static::$client->request("POST", "/rest/visits/searches", array (
            "visitedClass" => Announcement::class,
            "size" => 5
        ));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
        $content = $this->getResponseContent();
        self::assertNotNull($content);

        array_walk($content["content"], function ($visit) {
            $href = $visit["_links"]["visited"]["href"];
            self::assertContains("announcements", $href, "Expected visited to be an announcement");
        });
    }


    /**
     * @test
     */
    public function searchAllShouldReturn200()
    {
        static::$client->request("POST", "/rest/visits/searches", array (
            "visitedClass" => Announcement::class,
            "size" => 100
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function searchAsNonApiUserShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read(1);
        self::$client = self::createAuthenticatedClient($user);

        static::$client->request("POST", "/rest/visits/searches", array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function searchAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        static::$client->request("POST", "/rest/visits/searches");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}