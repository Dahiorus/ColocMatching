<?php

namespace App\Tests\Rest\Controller\v1\Invitation;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class UserInvitationControllerTest extends AbstractControllerTest
{
    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var integer */
    private $userId;


    protected function initServices() : void
    {
        $this->invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->userId = $this->createUser("search@test.fr", UserType::SEARCH)->getId();
        $announcement = $this->createAnnouncement();
        /** @var UserDto $user */
        $user = $this->userManager->read($announcement->getCreatorId());

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
        $creator = $this->createUser("proposal@test.fr", UserType::PROPOSAL);

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

        return $this->userManager->updateStatus($user, UserStatus::ENABLED);
    }


    /**
     * @test
     */
    public function inviteAsProposalUserShouldReturn201()
    {
        self::$client->request("POST", "/rest/users/" . $this->userId . "/invitations", array (
            "message" => "Hello! I want to invite you to join my announcement."
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
    }


    /**
     * @test
     */
    public function inviteAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/users/" . $this->userId . "/invitations", array (
            "message" => null
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function inviteNonExistingUserShouldReturn404()
    {
        self::$client->request("POST", "/rest/users/0/invitations", array ());
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteBannedUserShouldReturn400()
    {
        /** @var UserDto $userDto */
        $userDto = $this->userManager->read($this->userId);
        $this->userManager->updateStatus($userDto, UserStatus::BANNED);

        self::$client->request("POST", "/rest/users/" . $this->userId . "/invitations", array (
            "message" => "Hello!"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteSelfShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($this->userId);

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("POST", "/rest/users/" . $this->userId . "/invitations", array (
            "message" => "Hello!"
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteAsSimpleSearchUserShouldReturn403()
    {
        $user = $this->createUser("simple-search@test.fr", UserType::SEARCH);

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("POST", "/rest/users/" . $this->userId . "/invitations", array (
            "message" => "Hello!"
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function inviteAnnouncementCandidateShouldReturn403()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($this->userId);
        $creator = $this->userManager->findByUsername("proposal@test.fr");
        /** @var AnnouncementDto $announcement */
        $announcement = $this->announcementManager->read($creator->getAnnouncementId());

        $this->announcementManager->addCandidate($announcement, $user);

        self::$client->request("POST", "/rest/users/" . $this->userId . "/invitations", array (
            "message" => "Hello!"
        ));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getInvitationsShouldReturn200()
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($this->userId);

        self::$client = self::createAuthenticatedClient($user);
        self::$client->request("GET", "/rest/users/" . $this->userId . "/invitations");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getInvitationsAsOtherUserShouldReturn403()
    {
        self::$client->request("GET", "/rest/users/" . $this->userId . "/invitations");
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function getNonExistingUserInvitationsShouldReturn404()
    {
        self::$client->request("GET", "/rest/users/0/invitations");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }
}