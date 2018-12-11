<?php

namespace App\Tests\Command;

use App\Command\NotifyAlertsCommand;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\Announcement\HousingType;
use App\Core\Manager\Alert\AlertDtoManagerInterface;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\UserFilter;
use App\Tests\CreateUserTrait;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyAlertsCommandTest extends AbstractCommandTest
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
        return NotifyAlertsCommand::getDefaultName();
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
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    private function createAnnouncements()
    {
        for ($i = 0; $i < 5; $i++)
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
        // announcement alert
        $this->alertManager->create(
            $this->createSearchUser($this->userManager, "user-search-announcement@test.fr"),
            AnnouncementFilter::class,
            array (
                "name" => "alert test on announcement",
                "notificationType" => NotificationType::EMAIL,
                "searchPeriod" => "P0M1D",
                "filter" => array (
                    "withDescription" => true,
                    "rentPriceEnd" => 1000
                ),
                "resultSize" => 3
            ));

        // group alert
        $this->alertManager->create(
            $this->createSearchUser($this->userManager, "user-search-group@test.fr"),
            GroupFilter::class,
            array (
                "name" => "alert test on group",
                "notificationType" => NotificationType::SMS,
                "searchPeriod" => "P0M1D",
                "filter" => array (
                    "withDescription" => true,
                    "budgetMax" => 1000
                ),
                "resultSize" => 4
            ));

        // user alert
        $this->alertManager->create(
            $this->createSearchUser($this->userManager, "user-search-user@test.fr"),
            UserFilter::class,
            array (
                "name" => "alert test on user",
                "notificationType" => NotificationType::PUSH,
                "searchPeriod" => "P0M1D",
                "filter" => array (
                    "type" => "search"
                ),
                "resultSize" => 3
            ));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function execute()
    {
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("alerts notified", $output, "Expected success message to be displayed");
    }


    /**
     * @test
     */
    public function executeWithDryRun()
    {
        $this->commandTester->execute(
            array ("-vv" => true, "--dry-run" => true),
            array ("verbosity" => OutputInterface::VERBOSITY_VERBOSE));

        $output = $this->commandTester->getDisplay();
        self::assertRegExp("/Alert \[.+\] should be notified/", $output,
            "Expected dry-run message to be displayed");
    }

}