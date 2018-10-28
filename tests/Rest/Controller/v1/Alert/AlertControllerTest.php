<?php

namespace App\Tests\Rest\Controller\v1\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Alert\AlertStatus;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Tests\Rest\AbstractControllerTest;
use App\Tests\Rest\Controller\v1\CreateUserTrait;
use Symfony\Component\HttpFoundation\Response;

class AlertControllerTest extends AbstractControllerTest
{
    use CreateUserTrait;

    /** @var AlertDtoManagerInterface */
    private $alertManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AlertDto */
    private $alert;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->alertManager = self::getService("coloc_matching.core.alert_dto_manager");
    }


    protected function initTestData() : void
    {
        $user = $this->createSearchUser($this->userManager);
        $this->alert = $this->createAlert($user);
        self::$client = self::createAuthenticatedClient($user);
    }


    protected function clearData() : void
    {
        $this->alertManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @param UserDto $user
     *
     * @return AlertDto
     * @throws \Exception
     */
    private function createAlert(UserDto $user) : AlertDto
    {
        $data = array (
            "name" => "alert test",
            "notificationType" => NotificationType::EMAIL,
            "searchPeriod" => "P0Y0M2D",
            "filter" => array (
                "pageable" => array (
                    "page" => 2,
                    "size" => 10,
                    "sorts" => array (
                        array ("property" => "createdAt", "direction" => "desc"),
                        array ("property" => "title", "direction" => "asc")
                    )
                ),
                "withDescription" => true,
                "status" => Announcement::STATUS_ENABLED,
                "types" => [AnnouncementType::RENT],
            ),
        );

        return $this->alertManager->create($user, AnnouncementFilter::class, $data);
    }


    private function createOtherUser() : void
    {
        $user = self::getService("coloc_matching.core.user_dto_manager")->create(array (
            "email" => "other-user@test.fr",
            "plainPassword" => array (
                "password" => "Secret&1234",
                "confirmPassword" => "Secret&1234"
            ),
            "firstName" => "Other",
            "lastName" => "Test",
            "type" => "proposal"
        ));
        self::$client = self::createAuthenticatedClient($user);
    }


    /**
     * @test
     */
    public function getAllAlertsShouldReturn200()
    {
        self::$client->request("GET", "/rest/alerts");
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getAllAlertsAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/alerts");
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function getAlertShouldReturn200()
    {
        self::$client->request("GET", "/rest/alerts/" . $this->alert->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingAlertShouldReturn404()
    {
        self::$client->request("GET", "/rest/alerts/0");
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function getAlertAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("GET", "/rest/alerts/" . $this->alert->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getAlertAsOtherUserShouldReturn403()
    {
        $this->createOtherUser();

        self::$client->request("GET", "/rest/alerts/" . $this->alert->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function deleteAlertShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/alerts/" . $this->alert->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteNonExistingAlertShouldReturn204()
    {
        self::$client->request("DELETE", "/rest/alerts/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     */
    public function deleteAlertAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("DELETE", "/rest/alerts/" . $this->alert->getId());
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function deleteAlertAsOtherUserShouldReturn403()
    {
        $this->createOtherUser();

        self::$client->request("DELETE", "/rest/alerts/" . $this->alert->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function updateAlertStatusShouldReturn200()
    {
        self::$client->request("PATCH", "/rest/alerts/" . $this->alert->getId() . "/status",
            array ("value" => AlertStatus::DISABLED));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function updateNonExistingAlertStatusShouldReturn404()
    {
        self::$client->request("PATCH", "/rest/alerts/0/status",
            array ("value" => AlertStatus::DISABLED));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     */
    public function updateAlertStatusAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();

        self::$client->request("PATCH", "/rest/alerts/" . $this->alert->getId() . "/status",
            array ("value" => AlertStatus::DISABLED));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function updateAlertStatusAsOtherUserShouldReturn403()
    {
        $this->createOtherUser();

        self::$client->request("PATCH", "/rest/alerts/" . $this->alert->getId() . "/status",
            array ("value" => AlertStatus::DISABLED));
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function updateAlertStatusWithInvalidValueShouldReturn400()
    {
        self::$client->request("PATCH", "/rest/alerts/" . $this->alert->getId() . "/status",
            array ("value" => "hkjsdhjfsdg"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }

}