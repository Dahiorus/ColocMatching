<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
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


    /**
     * @test
     */
    public function getOnePageShouldReturn206()
    {
        static::$client->request("GET", "/rest/groups", array ("page" => 2, "size" => 10, "order" => "asc"));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllShouldReturn200()
    {
        static::$client->request("GET", "/rest/groups", array ("size" => 100));
        self::assertStatusCode(Response::HTTP_OK);
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
    public function searchOnePageShouldReturn206AndFilteredResult()
    {
        static::$client->request("POST", "/rest/groups/searches", array (
            "withDescription" => true,
            "budgetMin" => 200,
            "size" => 5
        ));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);

        $content = $this->getResponseContent();
        self::assertNotNull($content);
        $announcements = $content["content"];

        array_walk($announcements, function (array $announcement) {
            self::assertTrue($announcement["budget"] >= 200);
            self::assertNotEmpty($announcement["description"]);
        });
    }


    /**
     * @test
     */
    public function searchLastPageShouldReturn200AndFilteredResult()
    {
        static::$client->request("POST", "/rest/groups/searches", array (
            "withDescription" => true,
            "size" => 5,
            "page" => 5
        ));
        self::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        self::assertNotNull($content);
        $announcements = $content["content"];

        array_walk($announcements, function (array $announcement) {
            self::assertNotEmpty($announcement["description"]);
        });
    }


    /**
     * @test
     */
    public function searchWithInvalidFilterShouldReturn422()
    {
        static::$client->request("POST", "/rest/groups/searches", array (
            "unknownProperty" => "unknown",
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
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
