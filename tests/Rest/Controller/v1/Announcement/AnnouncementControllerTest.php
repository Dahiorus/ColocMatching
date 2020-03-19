<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementControllerTest extends AbstractControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDto */
    private $announcementTest;

    /** @var UserDto */
    private $creator;


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->announcementTest = $this->createAnnouncement();
        self::$client = self::createAuthenticatedClient($this->creator);
    }


    protected function clearData() : void
    {
        $this->announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    private function createAnnouncement() : AnnouncementDto
    {
        $this->creator = $this->createProposalUser($this->userManager, "proposal@test.fr");

        return $this->announcementManager->create($this->creator, array (
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
    public function getAnnouncementShouldReturn200()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/announcements/" . $this->announcementTest->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingAnnouncementShouldReturn404()
    {
        self::$client = self::initClient();
        self::$client->request("GET", "/rest/announcements/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function updateAnnouncementShouldReturn200()
    {
        self::$client->request("PUT", "/rest/announcements/" . $this->announcementTest->getId(), array (
            "title" => "Announcement modified",
            "description" => "New description",
            "type" => AnnouncementType::RENT,
            "rentPrice" => 950,
            "startDate" => "2018-10-05",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateAnnouncementWithInvalidDataShouldReturn400()
    {
        self::$client->request("PUT", "/rest/announcements/" . $this->announcementTest->getId(), array (
            "title" => "",
            "type" => AnnouncementType::RENT,
            "rentPrice" => -954,
            "startDate" => "2018-10-05",
            "location" => "azerty-1235"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function updateAnnouncementAsNonCreatorShouldReturn403()
    {
        $user = $this->createProposalUser(self::getService("coloc_matching.core.user_dto_manager"),
            "non-creator@test.fr");
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PUT", "/rest/announcements/" . $this->announcementTest->getId(), array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function patchAnnouncementShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/announcements/" . $this->announcementTest->getId(), array (
            "description" => "New description",
        ));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function patchAnnouncementWithInvalidDataShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/announcements/" . $this->announcementTest->getId(), array (
            "location" => "azertyuiop"
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function patchAnnouncementAsNonCreatorShouldReturn403()
    {
        $user = $this->createProposalUser(self::getService("coloc_matching.core.user_dto_manager"),
            "non-creator@test.fr");
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("PATCH", "/rest/announcements/" . $this->announcementTest->getId(), array ());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementShouldReturn204AndCreateHistoricEntry()
    {
        /** @var HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager */
        $historicAnnouncementManager = self::getService("coloc_matching.core.historic_announcement_dto_manager");

        self::assertTrue($historicAnnouncementManager->list()->getCount() == 0, "Expected to count 0 historic entry");

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcementTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);

        $entries = $historicAnnouncementManager->list();
        self::assertNotEmpty($entries, "Expected to find historic entries");

        $historicAnnouncementManager->deleteAll();
    }


    /**
     * @test
     */
    public function deleteNonExistingAnnouncementShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/announcements/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementWithCandidatesShouldReturn204()
    {
        $candidate = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"),
            "candidate@test.fr");
        self::getService("coloc_matching.core.announcement_dto_manager")
            ->addCandidate($this->announcementTest, $candidate);

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcementTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);

        /** @var HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager */
        $historicAnnouncementManager = self::getService("coloc_matching.core.historic_announcement_dto_manager");
        $historicAnnouncementManager->deleteAll();
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementWithInvitationsShouldReturn204()
    {
        /** @var InvitationDtoManagerInterface $invitationManager */
        $invitationManager = self::getService("coloc_matching.core.invitation_dto_manager");
        $invitationManager->create($this->announcementTest,
            $this->createSearchUser($this->userManager, "invitee@yopmail.com", UserStatus::ENABLED),
            Invitation::SOURCE_SEARCH, array ("message" => "Invitation test"));

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcementTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteAnnouncementWithVisitsShouldReturn204()
    {
        /** @var VisitDtoManagerInterface $visitManager */
        $visitManager = self::getService("coloc_matching.core.visit_dto_manager");
        $visitManager->create(
            $this->createSearchUser($this->userManager, "visitor@yopmail.com", UserStatus::ENABLED),
            $this->announcementTest);

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcementTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function getCandidatesShouldReturn200()
    {
        self::$client->request("GET", "/rest/announcements/" . $this->announcementTest->getId() . "/candidates");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getCandidatesAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/announcements/" . $this->announcementTest->getId() . "/candidates");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeCandidateAsCreatorShouldReturn204()
    {
        /** @var UserDto $user */
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"),
            "candidate@test.fr");
        self::getService("coloc_matching.core.announcement_dto_manager")
            ->addCandidate($this->announcementTest, $user);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcementTest->getId() . "/candidates/" . $user->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeCandidateAsTheCandidateShouldReturn204()
    {
        /** @var UserDto $candidate */
        $candidate = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"),
            "candidate@test.fr");
        self::getService("coloc_matching.core.announcement_dto_manager")
            ->addCandidate($this->announcementTest, $candidate);

        self::$client = self::createAuthenticatedClient($candidate);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcementTest->getId() . "/candidates/" . $candidate->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeCandidateAsNonCreatorAndNonCandidateShouldReturn403()
    {
        /** @var UserDto $candidate */

        $candidate = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"),
            "candidate@test.fr");
        self::getService("coloc_matching.core.announcement_dto_manager")
            ->addCandidate($this->announcementTest, $candidate);

        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"),
            "non-candidate@test.fr");

        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcementTest->getId() . "/candidates/" . $candidate->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeNonExistingCandidateAsCreatorShouldReturn204()
    {
        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcementTest->getId() . "/candidates/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function removeCandidateAsOtherCandidateShouldReturn403()
    {
        $userManager = self::getService("coloc_matching.core.user_dto_manager");
        $candidate = $this->createSearchUser($userManager, "candidate@test.fr");
        $otherCandidate = $this->createSearchUser($userManager, "other-candidate@test.fr");

        $this->announcementManager->addCandidate($this->announcementTest, $candidate);
        $this->announcementManager->addCandidate($this->announcementTest, $otherCandidate);

        self::$client = self::createAuthenticatedClient($otherCandidate);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcementTest->getId() . "/candidates/" . $candidate->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
