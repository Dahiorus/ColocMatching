<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class GroupControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var GroupDto */
    private $group;

    /** @var UserDto */
    private $user;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");

        $this->group = $this->createGroup();

        self::$client = self::createAuthenticatedClient($this->user);
    }


    protected function tearDown()
    {
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $groupManager->deleteAll(false);
        $this->userManager->deleteAll();

        parent::tearDown();
    }


    /**
     * @throws \Exception
     */
    private function createGroup()
    {
        $this->user = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));

        return $this->groupManager->create($this->user, array (
            "name" => "Group test",
            "description" => "Decription of the group",
            "budget" => 520
        ));
    }


    /**
     * @test
     */
    public function getGroupShouldReturn200()
    {
        self::$client->request("GET", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingGroupShouldReturn404()
    {
        self::$client->request("GET", "/rest/groups/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getGroupAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function putGroupShouldReturn200()
    {
        self::$client->request("PUT", "/rest/groups/" . $this->group->getId(), array (
            "name" => "Modified name",
            "description" => $this->group->getDescription(),
            "budget" => 800
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function putGroupWithInvalidDataShouldReturn422()
    {
        self::$client->request("PUT", "/rest/groups/" . $this->group->getId(), array (
            "name" => null,
            "description" => $this->group->getDescription(),
            "budget" => 800
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function putNonExistingGroupShouldReturn404()
    {
        self::$client->request("PUT", "/rest/groups/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function putGroupAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("PUT", "/rest/groups/" . $this->group->getId(), array ());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function putGroupAsNonCreatorShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "other-user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Other user",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PUT", "/rest/groups/" . $this->group->getId(), array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function patchGroupShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/groups/" . $this->group->getId(), array (
            "name" => "Modified name",
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchGroupWithInvalidDataShouldReturn422()
    {
        self::$client->request("PATCH", "/rest/groups/" . $this->group->getId(), array (
            "name" => null,
            "budget" => 800
        ));
        self::assertStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @test
     */
    public function patchNonExistingGroupShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/groups/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function patchGroupAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("PATCH", "/rest/groups/" . $this->group->getId(), array ());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function patchGroupAsNonCreatorShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "other-user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Other user",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PATCH", "/rest/groups/" . $this->group->getId(), array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function deleteGroupShouldReturn200()
    {
        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function deleteNonExistingGroupShouldReturn200()
    {
        self::$client->request("DELETE", "/rest/groups/0");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteGroupAsNonCreatorShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "other-user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Other user",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function deleteGroupAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function getMembersShouldReturn200()
    {
        self::$client->request("GET", "/rest/groups/" . $this->group->getId() . "/members");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingGroupMembersShouldReturn404()
    {
        self::$client->request("GET", "/rest/groups/0/members");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getGroupMembersAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/groups/" . $this->group->getId() . "/members");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeMemberAsCreatorShouldReturn200()
    {
        $member = $this->userManager->create(array (
            "email" => "member@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Other user",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        $this->groupManager->addMember($this->group, $member);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/" . $member->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function removeNonExistingMemberAsCreatorShouldReturn200()
    {
        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/0");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeMemberAsMemberShouldReturn200()
    {
        $member = $this->userManager->create(array (
            "email" => "member@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Other user",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        $this->groupManager->addMember($this->group, $member);

        self::$client = self::createAuthenticatedClient($member);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/" . $member->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function removeMemberAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/1");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeMemberAsOtherUserShouldReturn403()
    {
        $user = $this->userManager->create(array (
            "email" => "member@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Other user",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));

        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE",
            "/rest/groups/" . $this->group->getId() . "/members/" . $this->group->getCreatorId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
