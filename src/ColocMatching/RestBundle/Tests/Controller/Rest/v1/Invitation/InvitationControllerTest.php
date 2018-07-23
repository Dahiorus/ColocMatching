<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
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
        $this->creator = $this->createUser("proposal@test.fr", UserConstants::TYPE_PROPOSAL);
        $this->recipient = $this->createUser("search@test.fr", UserConstants::TYPE_SEARCH);
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

        return $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);
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
        $user = $this->createUser("other@test.fr", UserConstants::TYPE_PROPOSAL);
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
