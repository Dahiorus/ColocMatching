<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Announcement\HistoricAnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
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
        self::$client = null;
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    private function createAnnouncement() : AnnouncementDto
    {
        $this->creator = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL
        ));

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
        $user = $this->userManager->create(array (
            "email" => "non-creator@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User-2",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL));
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
        $user = $this->userManager->create(array (
            "email" => "non-creator@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User-2",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL));
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

        self::assertTrue($historicAnnouncementManager->countAll() == 0, "Expected to count 0 historic entry");

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
        $candidate = $this->userManager->create(array (
            "email" => "candidate@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Candidate",
            "lastName" => "Test",
            "type" => UserType::SEARCH));
        $this->announcementManager->addCandidate($this->announcementTest, $candidate);

        self::$client->request("DELETE", "/rest/announcements/" . $this->announcementTest->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);

        /** @var HistoricAnnouncementDtoManagerInterface $historicAnnouncementManager */
        $historicAnnouncementManager = self::getService("coloc_matching.core.historic_announcement_dto_manager");
        $historicAnnouncementManager->deleteAll();
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
        $user = $this->userManager->create(array (
            "email" => "candidate@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Candidate",
            "lastName" => "Test",
            "type" => UserType::SEARCH));
        $this->announcementManager->addCandidate($this->announcementTest, $user);

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
        $candidate = $this->userManager->create(array (
            "email" => "candidate@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Candidate",
            "lastName" => "Test",
            "type" => UserType::SEARCH));
        $this->announcementManager->addCandidate($this->announcementTest, $candidate);

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
        $candidate = $this->userManager->create(array (
            "email" => "candidate@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Candidate",
            "lastName" => "Test",
            "type" => UserType::SEARCH));
        $this->announcementManager->addCandidate($this->announcementTest, $candidate);

        $user = $this->userManager->create(array (
            "email" => "non-candidate@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "NonCandidate",
            "lastName" => "Test",
            "type" => UserType::SEARCH));

        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcementTest->getId() . "/candidates/" . $candidate->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
