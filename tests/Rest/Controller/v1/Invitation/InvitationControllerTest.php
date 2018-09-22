<?php

namespace App\Tests\Rest\Controller\v1\Invitation;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
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

    /** @var UserDto */
    private $creator;

    /** @var UserDto */
    private $recipient;


    protected function initServices() : void
    {
        $this->invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->creator = $this->createUser("proposal@test.fr", UserType::PROPOSAL);
        $this->recipient = $this->createUser("search@test.fr", UserType::SEARCH);
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
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
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

        return $this->userManager->updateStatus($user, UserStatus::ENABLED);
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
        $user = $this->createUser("other@test.fr", UserType::PROPOSAL);
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
