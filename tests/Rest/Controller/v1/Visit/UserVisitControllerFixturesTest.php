<?php

namespace App\Tests\Rest\Controller\v1\Visit;

use App\Core\DTO\User\UserDto;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Tests\Rest\DataFixturesControllerTest;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserVisitControllerFixturesTest extends DataFixturesControllerTest
{
    private $userId;


    /**
     * @throws Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::initVisits();
    }


    /**
     * @throws Exception
     */
    protected function setUp()
    {
        parent::setUp();
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var UserDto $visited */
        $visited = $userManager->list(new PageRequest(1, 1))->getContent()[0];
        $this->userId = $visited->getId();

        self::$client = self::createAuthenticatedClient($visited);
    }


    protected function baseEndpoint() : string
    {
        return "/rest/users/" . $this->userId . "/visits";
    }


    protected function searchQueryFilter() : string
    {
        return $this->stringConverter->toString(new VisitFilter());
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $visit) {
            $visitedHref = $visit["_links"]["visited"]["href"];
            self::assertContains("users", $visitedHref, "Expected visited to be a user");
            self::assertContains(strval($this->userId), $visitedHref, "Expected visited to have ID " . $this->userId);
        };
    }


    /**
     * @throws Exception
     */
    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");

        /** @var UserDto $visited */
        $visited = $userManager->list(new PageRequest(1, 1))->getContent()[0];
        /** @var UserDto[] $users */
        $users = $userManager->list(new PageRequest(1, 15))->getContent();

        foreach ($users as $visitor)
        {
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
        $user = self::getService("coloc_matching.core.user_dto_manager")->list(new PageRequest(1, 2))->getContent()[1];
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

}
