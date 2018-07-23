<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementInvitationControllerTest extends AbstractControllerTest
{
    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var integer */
    private $announcementId;


    protected function initServices() : void
    {
        $this->invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->announcementId = $this->createAnnouncement()->getId();
        $user = $this->createUser("search@test.fr", UserConstants::TYPE_SEARCH);

        self::$client = self::createAuthenticatedClient($user);
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
        $creator = $this->createUser("proposal@test.fr", UserConstants::TYPE_PROPOSAL);

        return $this->announcementManager->create($creator, array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
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
        self::$client->request("POST", "/rest/announcements/" . $this->announcementId . "/invitations", array (
            "message" => "Hello! I want to postulate to your announcement."
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function inviteAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/announcements/" . $this->announcementId . "/invitations", array (
            "message" => "Hello! I want to postulate to your announcement."
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function inviteOnNonExistingInvitableShouldReturn404()
    {
        self::$client->request("POST", "/rest/announcements/0/invitations", array (
            "message" => "Hello! I want to postulate to your announcement."
        ));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteNonAvailableInvitableShouldReturn400()
    {
        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($this->announcementId);
        $this->announcementManager->update($announcement, array ("status" => Announcement::STATUS_DISABLED), false);

        self::$client->request("POST", "/rest/announcements/" . $this->announcementId . "/invitations", array (
            "message" => "Hello! I want to postulate to your announcement."
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteAsProposalShouldReturn403()
    {
        $user = $this->userManager->findByUsername("search@test.fr");
        $user = $this->userManager->update($user, array ("type" => UserConstants::TYPE_PROPOSAL), false);

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("POST", "/rest/announcements/" . $this->announcementId . "/invitations", array (
            "message" => "Hello! I want to postulate to your announcement."
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getInvitationsShouldReturn200()
    {
        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($this->announcementId);
        /** @var UserDto $user */
        $user = $this->userManager->read($announcement->getCreatorId());

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("GET", "/rest/announcements/" . $this->announcementId . "/invitations");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getInvitationsAsNonCreatorShouldReturn403()
    {
        self::$client->request("GET", "/rest/announcements/" . $this->announcementId . "/invitations");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function getNonExistingInvitableInvitationsShouldReturn404()
    {
        self::$client->request("GET", "/rest/announcements/0/invitations");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }
}
