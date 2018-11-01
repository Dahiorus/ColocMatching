<?php

namespace App\Tests\Rest\Controller\v1\Message;

use App\Core\DTO\Group\GroupDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Message\GroupConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class GroupConversationControllerTest extends AbstractControllerTest
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var int */
    private $groupId;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
    }


    protected function initTestData() : void
    {
        $group = $this->createGroup();
        $user = $this->createSearchUser($this->userManager, "member@test.fr", UserStatus::ENABLED);
        $this->groupManager->addMember($group, $user);

        $this->groupId = $group->getId();

        self::$client = self::createAuthenticatedClient($user);
    }


    protected function clearData() : void
    {
        /** @var GroupConversationDtoManagerInterface $conversationManager */
        $conversationManager = self::getService("coloc_matching.core.group_conversation_dto_manager");
        $conversationManager->deleteAll();

        $this->groupManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * Creates a group
     *
     * @return GroupDto
     * @throws \Exception
     */
    private function createGroup()
    {
        $creator = $this->createSearchUser($this->userManager, "creator@test.fr", UserStatus::ENABLED);
        $group = $this->groupManager->create($creator, array (
            "name" => "Group test",
            "budget" => "1520"
        ));

        return $group;
    }


    /**
     * @test
     */
    public function listMessagesAsGroupMemberShouldReturn200()
    {
        self::$client->request("GET", "/rest/groups/" . $this->groupId . "/messages");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function listMessagesAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/groups/" . $this->groupId . "/messages");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function listMessagesAsNonGroupMemberShouldReturn403()
    {
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "non-member@test.fr",
            UserStatus::ENABLED);
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("GET", "/rest/groups/" . $this->groupId . "/messages");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function listNonExistingGroupMessagesShouldReturn404()
    {
        self::$client->request("GET", "/rest/groups/0/messages");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function postMessageAsGroupMemberShouldReturn201()
    {
        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/messages",
            array ("content" => "}^◄ûboR0■ã😀 😾fqsfsdfqsd"));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function postMessageAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/messages", array ("content" => "test"));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function postMessageAsNonGroupMemberShouldReturn403()
    {
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"), "non-member@test.fr",
            UserStatus::ENABLED);
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/messages", array ("content" => "test"));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function postMessageToNonExistingGroupShouldReturn404()
    {
        self::$client->request("POST", "/rest/groups/0/messages", array ("content" => "test"));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function postMessageWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/groups/" . $this->groupId . "/messages",
            array ("unknown" => "test"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }

}
