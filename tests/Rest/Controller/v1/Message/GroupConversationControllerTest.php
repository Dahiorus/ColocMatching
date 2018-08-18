<?php

namespace App\Tests\Rest\Controller\v1\Message;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
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
        $user = $this->createUser("member@test.fr");
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
     * Creates a user with the specified email
     *
     * @param string $email The user email
     *
     * @return UserDto
     * @throws \Exception
     */
    private function createUser(string $email) : UserDto
    {
        $user = $this->userManager->create(array (
            "email" => $email,
            "plainPassword" => "secret1234",
            "firstName" => "User-" . rand(),
            "lastName" => "Test",
            "type" => "search"
        ));
        $user = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);

        return $user;
    }


    /**
     * Creates a group
     *
     * @return GroupDto
     * @throws \Exception
     */
    private function createGroup()
    {
        $creator = $this->createUser("creator@test.fr");
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
        $user = $this->createUser("non-member@test.fr");
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
        $user = $this->createUser("non-member@test.fr");
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
