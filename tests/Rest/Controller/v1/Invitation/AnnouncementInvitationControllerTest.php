<?php

namespace App\Tests\Rest\Controller\v1\Invitation;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
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
        $user = $this->createSearchUser($this->userManager, "search@test.fr", UserStatus::ENABLED);

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
        $creator = $this->createProposalUser($this->userManager, "proposal@test.fr", UserStatus::ENABLED);

        return $this->announcementManager->create($creator, array (
            "title" => "Announcement test",
            "type" => AnnouncementType::RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
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
        $announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        /** @var AnnouncementDto $announcement */
        $announcement = $announcementManager->read($this->announcementId);
        $announcementManager->update($announcement, array ("status" => Announcement::STATUS_DISABLED), false);

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
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        $user = $this->createProposalUser($userManager, "other@test.fr", UserStatus::ENABLED);

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
