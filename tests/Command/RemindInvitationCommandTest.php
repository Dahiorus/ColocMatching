<?php

namespace App\Tests\Command;

use App\Command\RemindInvitationsCommand;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Manager\Notification\InvitationNotifier;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\CreateUserTrait;
use Symfony\Component\Console\Output\OutputInterface;

class RemindInvitationCommandTest extends AbstractCommandTest
{
    use CreateUserTrait;

    /** @var InvitationDtoManagerInterface */
    private $invitationManager;

    /** @var InvitationNotifier */
    private $invitationNotifier;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;


    protected function getCommandName() : string
    {
        return RemindInvitationsCommand::getDefaultName();
    }


    protected function initServices() : void
    {
        $this->invitationManager = $this->getService("coloc_matching.core.invitation_dto_manager");
        $this->invitationNotifier = $this->getService("coloc_matching.core.invitation_notifier");
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->announcementManager = $this->getService("coloc_matching.core.announcement_dto_manager");
    }


    protected function initTestData() : void
    {
        $recipient = $this->createSearchUser($this->userManager, "recipient@yopmail.com", UserStatus::ENABLED);
        $proposal = $this->createProposalUser($this->userManager, "proposal@yopmail.com", UserStatus::ENABLED);
        $announcement = $this->announcementManager->create($proposal, array (
            "title" => "My announcement",
            "location" => "Paris 75018",
            "rentPrice" => 1300,
            "startDate" => "2018-12-09",
            "type" => AnnouncementType::SHARING,
        ));

        $this->invitationManager->create($announcement, $recipient, Invitation::SOURCE_INVITABLE, array (
            "message" => "Hello!"
        ));
    }


    protected function destroyData() : void
    {
        $this->userManager->deleteAll();
    }


    /**
     * @test
     */
    public function execute()
    {
        $input = array ("until" => "now");
        $this->commandTester->execute($input);

        $output = $this->commandTester->getDisplay();
        self::assertContains("1 invitations created until", $output, "Expected invitation notified");
    }


    /**
     * @test
     */
    public function executeWithInvalidInputs()
    {
        $input = array ("until" => "kdsqfldsf");
        $statusCode = $this->commandTester->execute($input);

        self::assertEquals(1, $statusCode, "Expected the command to end on error");
    }


    /**
     * @test
     */
    public function executeWithDryRun()
    {
        $input = array (
            "until" => "now",
            "--dry-run" => true,
            "-vv" => true);
        $this->commandTester->execute($input, array ("verbosity" => OutputInterface::VERBOSITY_VERBOSE));

        $output = $this->commandTester->getDisplay();
        self::assertContains("1 invitations created until", $output, "Expected invitation notified");
    }


    /**
     * @test
     */
    public function interact()
    {
        $this->commandTester->setInputs(array ("now"));
        $this->commandTester->execute([], ["interactive" => true]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("Choose a date/string value in a valid format", $output, "Expected question on date");
    }


    /**
     * @test
     */
    public function interactWithInvalidInputs()
    {
        $this->commandTester->setInputs(array ("", "2018/12/01"));
        $this->commandTester->execute([], array (
            "interactive" => true,
            "capture_stderr_separately" => true
        ));

        $output = $this->commandTester->getErrorOutput();
        self::assertContains("The date is mandatory", $output, "Expected error message on date");
    }

}