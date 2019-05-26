<?php

namespace App\Tests\Rest\Controller\v1\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\DataFixturesControllerTest;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var UserDto */
    private $user;


    /**
     * @throws Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "passWord",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::SEARCH
        ));

        static::$client = static::createAuthenticatedClient($this->user);
    }


    protected function tearDown()
    {
        $this->userManager->delete($this->user);
        parent::tearDown();
    }


    protected function initServices() : void
    {
        parent::initServices();
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
    }


    protected function baseEndpoint() : string
    {
        return "/rest/groups";
    }


    protected function searchQueryFilter() : string
    {
        $filter = new GroupFilter();
        $filter->setWithDescription(true)
            ->setBudgetMin(200);

        return $this->stringConverter->toString($filter);
    }


    protected function invalidSearchQueryFilter() : string
    {
        return "budgetMin=NaN, status[]=test";
    }


    protected function searchResultAssertCallable() : callable
    {
        return function (array $group) {
            self::assertNotEmpty($group["description"], "Expected group to have a description");
            self::assertGreaterThanOrEqual(200, $group["budget"], "Expected group to have a budget of 200 min.");
        };
    }


    /**
     * @test
     */
    public function getAsAnonymousShouldReturn401()
    {
        static::$client = self::initClient();

        static::$client->request("GET", "/rest/groups");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws Exception
     */
    public function searchWithComplexFilter()
    {
        /** @var Page<GroupDto> $groups */
        $groups = $this->groupManager->list(new PageRequest(1, 5));

        foreach ($groups as $group)
        {
            $path = dirname(__FILE__) . "/../../../Resources/uploads/image.jpg";
            $file = $this->createTmpJpegFile($path, "user-img.jpg");

            $this->groupManager->uploadGroupPicture($group, $file);
        }

        self::$client->request("GET", "/rest/groups", array ("q" => "withPicture:true,countMembers:1"));

        self::assertStatusCode(Response::HTTP_OK);

        $response = $this->getResponseContent();
        $content = $response["content"];
        array_walk($content, function ($group) {
            self::assertNotEmpty($group["_embedded"]["picture"]);
        });
    }

}
