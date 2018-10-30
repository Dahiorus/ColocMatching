<?php

namespace App\Tests\Rest\Controller\v1\Alert;

use App\Core\Entity\Alert\NotificationType;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\CreateUserTrait;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AlertControllerCreateTest extends AbstractControllerTest
{
    use CreateUserTrait;

    /** @var UserDtoManagerInterface */
    private $userManager;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $user = $this->createSearchUser($this->userManager, "user@test.fr");
        self::$client = self::createAuthenticatedClient($user);
    }


    protected function clearData() : void
    {
        self::getService("coloc_matching.core.alert_dto_manager")->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function createAnnouncementAlertShouldReturn201()
    {
        self::$client->request("POST", "/rest/alerts/announcements", array (
            "name" => "alert test",
            "notificationType" => NotificationType::EMAIL,
            "searchPeriod" => "P0M2D",
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
        ));
        self::assertStatusCode(Response::HTTP_CREATED);
        self::assertHasLocation();
    }


    /**
     * @test
     */
    public function createAnnouncementAlertAsAnonymousShouldReturn401()
    {
        self::$client = self::initClient();
        self::$client->request("POST", "/rest/alerts/announcements", array (
            "name" => "alert test",
            "notificationType" => NotificationType::EMAIL,
            "searchPeriod" => "P0M2D",
            "filter" => array (
                "withDescription" => true,
                "status" => Announcement::STATUS_ENABLED,
                "types" => [AnnouncementType::RENT],
            ),
        ));
        self::assertStatusCode(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function createAnnouncementAlertWithInvalidDataShouldReturn400()
    {
        self::$client->request("POST", "/rest/alerts/announcements", array (
            "notificationType" => NotificationType::EMAIL,
            "filter" => array (
                "withDescription" => true,
                "status" => Announcement::STATUS_ENABLED,
                "types" => [AnnouncementType::RENT],
            ),
        ));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }

}