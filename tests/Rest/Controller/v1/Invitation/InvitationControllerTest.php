<?php

namespace App\Tests\Rest\Controller\v1\Invitation;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class InvitationControllerTest extends AbstractControllerTest
{
    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var UserDto */
    private $creator;

    /** @var UserDto */
    private $recipient;


    protected function initServices() : void
    {
        $this->invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->groupManager = self::getService("coloc_matching.core.group_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->creator = $this->createProposalUser($this->userManager, "proposal@test.fr", UserStatus::ENABLED);
        $this->recipient = $this->createSearchUser($this->userManager, "search@test.fr", UserStatus::ENABLED);
    }


    protected function clearData() : void
    {
        $this->invitationManager->deleteAll();
        $this->announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    private function createAnnouncement() : AnnouncementDto
    {
        return $this->announcementManager->create($this->creator, array (
            "title" => "Announcement test",
            "type" => AnnouncementType::RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
    }


    /**
     * @return GroupDto
     * @throws \Exception
     */
    private function createGroup() : GroupDto
    {
        return $this->groupManager->create($this->recipient, array (
            "name" => "Group test",
            "budget" => 1200
        ));
    }


    /**
     * @param UserDto $recipient
     * @param string $sourceType
     *
     * @return InvitationDto
     * @throws \Exception
     */
    private function createInvitation(UserDto $recipient, string $sourceType) : InvitationDto
    {
        $invitable = $this->createAnnouncement();

        return $this->invitationManager->create($invitable, $recipient,
            $sourceType, array ("message" => "Invitation test '$sourceType'"));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function answerInvitableSourceInvitationAsRecipientShouldReturn200()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::createAuthenticatedClient($this->recipient);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function acceptInvitableSourceInvitationAsRecipientShouldReturn200()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::createAuthenticatedClient($this->recipient);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer", array ("accepted" => true));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function acceptInvitationForRecipientHavingGroupShouldInviteOtherMembers()
    {
        // create group and members
        $group = $this->createGroup();
        $member1 = $this->createSearchUser($this->userManager, "member-1@yopmail.com", UserStatus::ENABLED);
        $member2 = $this->createSearchUser($this->userManager, "member-2@yopmail.com", UserStatus::ENABLED);
        $this->groupManager->addMember($group, $member1);
        $this->groupManager->addMember($group, $member2);

        // invite the group creator
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();

        // accept the invitation
        self::$client = self::createAuthenticatedClient($this->recipient);
        self::$client->request("POST", "/rest/invitations/$invitationId/answer", array ("accepted" => true));
        self::assertStatusCode(Response::HTTP_OK);

        // assert the group members received an invitation
        $member1Invitations = $this->invitationManager->listByRecipient($member1);
        self::assertNotEmpty($member1Invitations, "Expected the group member 1 receiving invitations");

        $member2Invitations = $this->invitationManager->listByRecipient($member2);
        self::assertNotEmpty($member2Invitations, "Expected the group member 2 receiving invitations");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function acceptInvitationForUserHavingManyInvitationsShouldPurgeOthers()
    {
        // create invitations for the recipient
        $otherUser = $this->createProposalUser($this->userManager, "other-proposal@yopmail.fr", UserStatus::ENABLED);
        $announcement = $this->announcementManager->create($otherUser, array (
            "title" => "Announcement test",
            "type" => AnnouncementType::RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
        $secondInvitationId = $this->invitationManager->create($announcement, $this->recipient,
            Invitation::SOURCE_SEARCH, array ("message" => "2nd invitation"))->getId();
        $invitationToAcceptId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();

        // accept the invitation
        self::$client = self::createAuthenticatedClient($this->recipient);
        self::$client->request("POST", "/rest/invitations/$invitationToAcceptId/answer", array ("accepted" => true));
        self::assertStatusCode(Response::HTTP_OK);

        // assert other invitations are refused
        /** @var InvitationDto $invitation */
        $invitation = $this->invitationManager->read($secondInvitationId);
        self::assertEquals(Invitation::STATUS_REFUSED, $invitation->getStatus(),
            "Expected the invitation to be refused");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function answerInvitableSourceInvitationAsInvitableCreatorShouldReturn403()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::createAuthenticatedClient($this->creator);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function answerInvitationAsOtherUserShouldReturn403()
    {
        $user = $this->createProposalUser($this->userManager, "other@test.fr", UserStatus::ENABLED);
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function answerInvitationAsAnonymousShouldReturn401()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::initClient();

        self::$client->request("POST", "/rest/invitations/$invitationId/answer");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function answerNonExistingInvitationShouldReturn404()
    {
        self::$client = self::createAuthenticatedClient($this->recipient);

        self::$client->request("POST", "/rest/invitations/0/answer");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function answerSearchSourceInvitationAsInvitableCreatorShouldReturn200()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_SEARCH)->getId();
        self::$client = self::createAuthenticatedClient($this->creator);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function acceptSearchSourceInvitationAsInvitableCreatorShouldReturn200()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_SEARCH)->getId();
        self::$client = self::createAuthenticatedClient($this->creator);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer", array ("accepted" => true));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function answerSearchSourceInvitationAsRecipientShouldReturn403()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_SEARCH)->getId();
        self::$client = self::createAuthenticatedClient($this->recipient);

        self::$client->request("POST", "/rest/invitations/$invitationId/answer");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteInvitationAsRecipientShouldReturn204()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::createAuthenticatedClient($this->recipient);

        self::$client->request("DELETE", "/rest/invitations/$invitationId");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteInvitationAsInvitableCreatorShouldReturn204()
    {
        $invitationId = $this->createInvitation($this->recipient, Invitation::SOURCE_INVITABLE)->getId();
        self::$client = self::createAuthenticatedClient($this->creator);

        self::$client->request("DELETE", "/rest/invitations/$invitationId");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingInvitationShouldReturn204()
    {
        self::$client = self::createAuthenticatedClient($this->creator);

        self::$client->request("DELETE", "/rest/invitations/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }

}
