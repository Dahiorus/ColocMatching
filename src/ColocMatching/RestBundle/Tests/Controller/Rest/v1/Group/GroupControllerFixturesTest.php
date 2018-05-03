<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Order;
use ColocMatching\RestBundle\Tests\DataFixturesControllerTest;
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
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
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
            "status" => "unknown_value",
            "pageable" => array (
                "sorts" => array (
                    array ("property" => "budget   ", "direction" => "other")
                )
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
