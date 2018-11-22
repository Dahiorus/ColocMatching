<?php

namespace App\Tests\Command;

use App\Command\AlertNotifyCommand;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\Announcement\HousingType;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Tests\CreateUserTrait;

class AlertNotifyCommandTest extends AbstractCommandTest
{
    use CreateUserTrait;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AlertDtoManagerInterface */
    private $alertManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;


    protected function getCommandName() : string
    {
        return AlertNotifyCommand::getDefaultName();
    }


    protected function initServices() : void
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->alertManager = $this->getService("coloc_matching.core.alert_dto_manager");
        $this->announcementManager = $this->getService("coloc_matching.core.announcement_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->createAnnouncements();
        $this->createAlerts();
    }


    protected function destroyData() : void
    {
        $this->alertManager->deleteAll();
        $this->announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    private function createAnnouncements()
    {
        for ($i = 0; $i < 10; $i++)
        {
            $creator = $this->createProposalUser($this->userManager, "proposal-$i@test.com");
            $data = array (
                "title" => "Announcement $i",
                "rentPrice" => 800,
                "startDate" => (new \DateTime())->format("Y-m-d"),
                "description" => "This is a description",
                "location" => "Paris, France",
                "type" => AnnouncementType::RENT,
                "housingType" => HousingType::APARTMENT
            );
            $this->announcementManager->create($creator, $data);
        }
    }


    /**
     * @throws \Exception
     */
    private function createAlerts()
    {
        for ($i = 0; $i < 4; $i++)
        {
            $user = $this->createSearchUser($this->userManager, "user-$i@test.fr");
            $data = array (
                "name" => "alert test $i",
                "notificationType" => NotificationType::EMAIL,
                "searchPeriod" => "P0M1D",
                "filter" => array (
                    "withDescription" => true,
                    "rentPriceEnd" => 1000
                ),
                "resultSize" => 3
            );
            $this->alertManager->create($user, AnnouncementFilter::class, $data);
        }
    }


    /**
     * @test
     * @throws \Exception
     */
    public function execute()
    {
        $this->commandTester->execute(array ("command" => $this->command->getName()));

        $output = $this->commandTester->getDisplay();
        self::assertContains("alerts notified", $output, "Expected success message to be displayed");
    }

}