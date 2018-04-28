<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class GroupInvitationControllerTest extends AbstractControllerTest
{
    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var integer */
    private $groupId;


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");

        $this->groupId = $this->createGroup()->getId();
        $user = $this->createUser("search@test.fr", UserConstants::TYPE_SEARCH);

        self::$client = self::createAuthenticatedClient($user);

        $this->invitationManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    protected function tearDown()
    {
        $this->invitationManager->deleteAll();
        $this->groupManager->deleteAll();
        $this->userManager->deleteAll();
        parent::tearDown();
    }


    /**
     * @return GroupDto
     * @throws \Exception
     */
    private function createGroup() : GroupDto
    {
        $creator = $this->createUser("group-creator@test.fr", UserConstants::TYPE_SEARCH);

        return $this->groupManager->create($creator, array (
            "name" => "Group test",
            "description" => "Description of the group",
            "budget" => 520
        ));
    }


    /**
     * @param string $email
     * @param string $type
     *
     * @return UserDto
     * @throws \Exception
     */
    private function createUser(string $email, string $type) : UserDto
    {
        $user = $this->userManager->create(array (
            "email" => $email,
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => $type
        ));

        return $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);
    }


    /**
     * @test
     */
    public function inviteAsSearchUserShouldReturn201()
    {
        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/invitations", array (
            "message" => "Hello! I want to postulate to your group."
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function inviteAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/invitations", array (
            "message" => "Hello! I want to postulate to your group."
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function inviteOnNonExistingInvitableShouldReturn404()
    {
        self::$client->request("POST", "/rest/groups/0/invitations", array (
            "message" => "Hello! I want to postulate to your group."
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteNonAvailableInvitableShouldReturn400()
    {
        /** @var GroupDto $group */
        $group = $this->groupManager->read($this->groupId);
        $this->groupManager->update($group, array ("status" => Group::STATUS_CLOSED), false);

        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/invitations", array (
            "message" => "Hello! I want to postulate to your group."
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getInvitationsShouldReturn200()
    {
        /** @var GroupDto $group */
        $group = $this->groupManager->read($this->groupId);
        /** @var UserDto $user */
        $user = $this->userManager->read($group->getCreatorId());

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("GET", "/rest/groups/" . $this->groupId . "/invitations");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getInvitationsAsNonCreatorShouldReturn403()
    {
        self::$client->request("GET", "/rest/groups/" . $this->groupId . "/invitations");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function getNonExistingInvitableInvitationsShouldReturn404()
    {
        self::$client->request("GET", "/rest/groups/0/invitations");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }

}
