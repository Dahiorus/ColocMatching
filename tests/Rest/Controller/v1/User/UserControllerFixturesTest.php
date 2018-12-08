<?php

namespace App\Tests\Rest\Controller\v1\User;

use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\DataFixturesControllerTest;
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
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function baseEndpoint() : string
    {
        return "/rest/users";
    }


    protected function searchFilter() : array
    {
        return array (
            "hasAnnouncement" => true,
            "status" => array ("enabled", "vacation"),
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "email", "direction" => Order::ASC)
                )
            )
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "status" => array ("unknown", "vacation"),
            "hasGroup" => "test",
            "pageable" => "sdfklsdkl"
        );
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $user) {
            $status = $user["status"];
            self::assertTrue($status == "enabled" || $status == "vacation");
            self::assertNotEmpty($user["_links"]["announcement"]);
        };
    }


    /**
     * @test
     * @throws \Exception
     */
    public function searchUsersHavingGroup()
    {
        $filter = array (
            "hasGroup" => true,
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "email", "direction" => Order::ASC)
                )
            )
        );
        $user = $this->userManager->list(new PageRequest(1, 1))
            ->getContent()[0];
        self::$client = self::createAuthenticatedClient($user);

        static::$client->request("POST", "/rest/users/searches", $filter);

        static::assertStatusCode(Response::HTTP_CREATED);

        $content = $this->getResponseContent();
        array_walk($content["content"], function ($user) {
            self::assertNotEmpty($user["_links"]["group"], "Expected the user to have a group");
        });
    }


    /**
     * @test
     * @throws \Exception
     */
    public function searchUserHavingTags()
    {
        $filter = array (
            "tags" => ["tag1", "tag3"],
            "pageable" => array (
                "size" => 5,
                "sorts" => array (
                    array ("property" => "email", "direction" => Order::ASC)
                )
            )
        );
        /** @var Page<UserDto> $users */
        $users = $this->userManager->list(new PageRequest(1, 5));

        foreach ($users as $user)
        {
            $this->userManager->update($user, array ("tags" => ["tag1", "tag2", "tag3"]), false);
        }

        static::$client->request("POST", "/rest/users/searches", $filter);

        static::assertStatusCode(Response::HTTP_CREATED);

        $content = $this->getResponseContent();
        array_walk($content["content"], function ($user) use ($filter) {
            self::assertContains("tag1", $user["tags"]);
            self::assertContains("tag3", $user["tags"]);
        });
    }

}
