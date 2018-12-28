<?php

namespace App\Tests\Rest\Controller\v1\Group;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\Message\GroupConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
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


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->group = $this->createGroup();
        self::$client = self::createAuthenticatedClient($this->user);
    }


    protected function clearData() : void
    {
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $groupManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    private function createGroup()
    {
        $this->user = $this->createSearchUser($this->userManager, "user@test.fr", UserStatus::ENABLED);

        return $this->groupManager->create($this->user, array (
            "name" => "Group test",
            "description" => "Description of the group",
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
    public function putGroupWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/groups/" . $this->group->getId(), array (
            "name" => null,
            "description" => $this->group->getDescription(),
            "budget" => 800
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
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
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "other@test.fr");
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
    public function patchGroupWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/groups/" . $this->group->getId(), array (
            "name" => null,
            "budget" => 800
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
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
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "other@test.fr");
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PATCH", "/rest/groups/" . $this->group->getId(), array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function deleteGroupShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingGroupShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/groups/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteGroupAsNonCreatorShouldReturn403()
    {
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "other@test.fr");
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
    public function removeMemberAsCreatorShouldReturn204()
    {
        /** @var UserDto $member */
        $member = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "member@test.fr");
        $this->groupManager->addMember($this->group, $member);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/" . $member->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function removeNonExistingMemberAsCreatorShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeMemberAsMemberShouldReturn204()
    {
        /** @var UserDto $member */
        $member = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "member@test.fr",
            UserStatus::ENABLED);
        $this->groupManager->addMember($this->group, $member);

        self::$client = self::createAuthenticatedClient($member);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId() . "/members/" . $member->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
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
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "other@test.fr",
            UserStatus::ENABLED);
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE",
            "/rest/groups/" . $this->group->getId() . "/members/" . $this->group->getCreatorId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeMemberAsOtherMemberShouldReturn403()
    {
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        $member = $this->createSearchUser($userManager, "member@test.fr", UserStatus::ENABLED);
        $memberToRemove = $this->createSearchUser($userManager, "other-member@test.fr", UserStatus::ENABLED);

        $this->groupManager->addMember($this->group, $member);
        $this->groupManager->addMember($this->group, $memberToRemove);

        self::$client = self::createAuthenticatedClient($member);
        self::$client->request("DELETE",
            "/rest/groups/" . $this->group->getId() . "/members/" . $memberToRemove->getId());

        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteGroupWithConversationShouldReturn204()
    {
        /** @var GroupConversationDtoManagerInterface $conversationManager */
        $conversationManager = self::getService("coloc_matching.core.group_conversation_dto_manager");
        $conversationManager->createMessage($this->user, $this->group, array (
            "content" => "Hello!"
        ));

        $messages = $conversationManager->listMessages($this->group);
        self::assertNotEmpty($messages, "The group should have messages");

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteGroupWithInvitationsShouldReturn204()
    {
        /** @var InvitationDtoManagerInterface $invitationManager */
        $invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $invitationManager->create($this->group,
            $this->createSearchUser($this->userManager, "invitee@yopmail.com", UserStatus::ENABLED),
            Invitation::SOURCE_SEARCH, array ("message" => "Invitation test"));

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteGroupWithVisitsShouldReturn204()
    {
        /** @var VisitDtoManagerInterface $visitManager */
        $visitManager = self::getService("coloc_matching.core.visit_dto_manager");
        $visitManager->create(
            $this->createSearchUser($this->userManager, "visitor@yopmail.com", UserStatus::ENABLED), $this->group);

        self::$client->request("DELETE", "/rest/groups/" . $this->group->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }

}
