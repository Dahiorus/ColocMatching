<?php

namespace App\Tests\Rest\Controller\v1\Group;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Order;
use App\Tests\Rest\DataFixturesControllerTest;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerFixturesTest extends DataFixturesControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $user;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
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


    protected function baseEndpoint() : string
    {
        return "/rest/groups";
    }


    protected function searchFilter() : array
    {
        return array (
            "withDescription" => true,
            "budgetMin" => 200,
            "pageable" => array (
                "sorts" => array (
                    array ("property" => "name", "direction" => Order::ASC)
                )
            )
        );
    }


    protected function invalidSearchFilter() : array
    {
        return array (
            "budgetMin" => "NaN",
            "status" => ["test"],
            "pageable" => array (
                "unknown" => null
            )
        );
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
     */
    public function searchAsAnonymousShouldReturn401()
    {
        static::$client = self::initClient();

        static::$client->request("POST", "/rest/groups/searches", array (
            "withDescription" => true,
            "budgetMin" => 200,
            "size" => 5
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
