<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Administration\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDto */
    private $userTest;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->userTest = $this->createUser();
        $admin = $this->createAdmin();

        self::$client = self::createAuthenticatedClient($admin);
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @return UserDto
     * @throws \Exception
     */
    private function createUser() : UserDto
    {
        return $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
    }


    /**
     * @return UserDto
     * @throws \Exception
     */
    private function createAdmin() : UserDto
    {
        $admin = $this->userManager->create(array ("email" => "admin@test.fr",
            "plainPassword" => "admin1234",
            "firstName" => "Admin",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH));
        $admin = $this->userManager->addRole($admin, "ROLE_ADMIN");

        return $admin;
    }


    /**
     * @test
     */
    public function putUserShouldReturn200()
    {
        self::$client->request("PUT", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => $this->userTest->getEmail(),
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putUserWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => null,
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => 50
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function putUserAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("PUT", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => $this->userTest->getEmail(),
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => $this->userTest->getType()
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function putNonExistingUserShouldReturn404()
    {
        self::$client->request("PUT", "/rest/admin/users/0", array (
            "email" => $this->userTest->getEmail(),
            "firstName" => $this->userTest->getFirstName(),
            "lastName" => $this->userTest->getLastName(),
            "type" => $this->userTest->getType()
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchUserShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId(), array (
            "type" => UserConstants::TYPE_PROPOSAL
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchUserWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId(), array (
            "email" => null,
            "type" => 50
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function patchUserAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId(), array (
            "type" => $this->userTest->getType()
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function patchNonExistingUserShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/admin/users/0", array ());
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateUserStatusShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => UserConstants::STATUS_ENABLED
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateNonExistingUserStatusShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/admin/users/0/status", array (
            "value" => UserConstants::STATUS_ENABLED
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateUserStatusWithInvalidValueShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => "unknown"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     */
    public function updateUserStatusAsNonAPIUserShouldReturn403()
    {
        self::$client = self::createAuthenticatedClient($this->userTest);

        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => UserConstants::STATUS_ENABLED
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function updateUserStatusAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("PATCH", "/rest/admin/users/" . $this->userTest->getId() . "/status", array (
            "value" => UserConstants::STATUS_ENABLED
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteUserShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/admin/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteNonExistingUserShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/admin/users/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteUserAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("DELETE", "/rest/admin/users/" . $this->userTest->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }

}
