<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class UserVisitControllerFixturesTest extends DataFixturesControllerTest
{
    private static $userId = 1;


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
        /** @var UserDto $visited */
        $visited = $userManager->read(self::$userId);

        self::$client = self::createAuthenticatedClient($visited);
    }


    protected function baseEndpoint() : string
    {
        return "/rest/users/" . self::$userId . "/visits";
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
            self::assertContains("users", $visitedHref, "Expected visited to be a user");
            self::assertContains(strval(self::$userId), $visitedHref, "Expected visited to have ID " . self::$userId);
        };
    }


    /**
     * @throws \Exception
     */
    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");

        $visited = $userManager->read(self::$userId);
        /** @var UserDto[] $users */
        $users = $userManager->list();

        for ($i = 0; $i < 100; $i++)
        {
            /** @var UserDto $visitor */
            $visitor = $users[ rand(0, count($users) - 1) ];
            self::$client = self::createAuthenticatedClient($visitor);

            self::$client->request("GET", "/rest/users/" . $visited->getId());
        }

        self::$client = null;
    }


    /**
     * @test
     */
    public function getAsNonVisitedShouldReturn403()
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