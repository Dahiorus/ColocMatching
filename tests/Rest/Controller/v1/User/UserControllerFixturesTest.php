<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\UserFilter;
use App\Tests\Rest\DataFixturesControllerTest;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function setUp()
    {
        parent::setUp();
        static::$client = static::initClient();
    }


    protected function initServices() : void
    {
        parent::initServices();
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function baseEndpoint() : string
    {
        return "/rest/users";
    }


    protected function searchQueryFilter() : string
    {
        $filter = new UserFilter();
        $filter->setHasAnnouncement(true)
            ->setStatus(array ("enabled", "vacation"));

        return $this->stringConverter->toString($filter);
    }


    protected function invalidSearchQueryFilter() : string
    {
        return "status=lqfldjsf, hasAnnouncement=true";
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $user) {
            $status = $user["status"];
            self::assertTrue($status == "enabled" || $status == "vacation");
            self::assertNotEmpty($user["_links"]["announcements"], "Expected the user to have announcements");
        };
    }


    /**
     * @test
     * @throws Exception
     */
    public function searchUsersHavingGroup()
    {
        $user = $this->userManager->list(new PageRequest(1, 1))
            ->getContent()[0];
        self::$client = self::createAuthenticatedClient($user);

        static::$client->request("GET", "/rest/users", ["q" => "hasGroup:true"]);

        static::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        array_walk($content["content"], function ($user) {
            self::assertNotEmpty($user["_links"]["groups"], "Expected the user to have groups");
        });
    }


    /**
     * @test
     * @throws Exception
     */
    public function searchUserHavingTags()
    {
        /** @var Page<UserDto> $users */
        $users = $this->userManager->list(new PageRequest(1, 5));

        foreach ($users as $user)
        {
            $this->userManager->update($user, array ("tags" => ["tag1", "tag2", "tag3"]), false);
        }

        static::$client->request("GET", "/rest/users", ["q" => "tags[]:tag1, tags[]:tag3"]);

        static::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        array_walk($content["content"], function ($user) {
            self::assertContains("tag1", $user["tags"]);
            self::assertContains("tag3", $user["tags"]);
        });
    }

}
