<?php

namespace App\Tests\Rest\Controller\v1\Visit;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\VisitFilter;
use App\Tests\Rest\DataFixturesControllerTest;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class GroupVisitControllerFixturesTest extends DataFixturesControllerTest
{
    private $groupId;


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
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");

        /** @var GroupDto $group */
        $group = $groupManager->list(new PageRequest(1, 1))->getContent()[0];
        $this->groupId = $group->getId();
        /** @var UserDto $creator */
        $creator = $userManager->read($group->getCreatorId());

        self::$client = self::createAuthenticatedClient($creator);
    }


    protected function baseEndpoint() : string
    {
        return "/rest/groups/" . $this->groupId . "/visits";
    }


    protected function searchQueryFilter() : string
    {
        return $this->stringConverter->toString(new VisitFilter());
    }


    protected function invalidSearchQueryFilter() : string
    {
        return "visitedAtSince:qslsjfsdqkfjqsdlkfjqsd";
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $visit) {
            $visitedHref = $visit["_links"]["visited"]["href"];
            self::assertContains("groups", $visitedHref, "Expected visited to be an group");
            self::assertContains(strval($this->groupId), $visitedHref, "Expected visited to have ID " . $this->groupId);
        };
    }


    /**
     * @throws Exception
     */
    private static function initVisits()
    {
        /** @var UserDtoManagerInterface $userManager */
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");

        /** @var GroupDto $group */
        $group = $groupManager->list(new PageRequest(1, 1))->getContent()[0];
        /** @var UserDto[] $users */
        $users = $userManager->list(new PageRequest(1, 15))->getContent();

        foreach ($users as $visitor)
        {
            self::$client = self::createAuthenticatedClient($visitor);
            self::$client->request("GET", "/rest/groups/" . $group->getId());
        }

        self::$client = null;
    }


    /**
     * @test
     */
    public function getAsNonCreatorShouldReturn403()
    {
        /** @var UserDto $user */
        $user = self::getService("coloc_matching.core.user_dto_manager")->list(new PageRequest(5, 1))->getContent()[0];
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
