<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class UserControllerCreateTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        self::$client = self::initClient();
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function createUserShouldReturn201()
    {
        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        );

        static::$client->request("POST", "/rest/users", $data);
        self::assertStatusCode(Response::HTTP_CREATED);
        self::assertHasLocation();
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createUserWithSameEmailShouldReturn422()
    {
        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        );
        $this->userManager->create($data);

        static::$client->request("POST", "/rest/users", $data);
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function createUserWithInvalidDataShouldReturn422()
    {
        $data = array (
            "email" => "",
            "plainPassword" => "Secret1234&",
            "firstName" => null,
            "lastName" => "Test",
            "type" => 5
        );

        static::$client->request("POST", "/rest/users", $data);
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}