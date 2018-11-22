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
        $this->invitationManager->deleteAll();
        $this->announcementManager->deleteAll();
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
    public function executeInInteractionMode()
    {
        $input = array ("until" => "now");

        $this->commandTester->execute($input, ["interactive"]);

        $output = $this->commandTester->getDisplay();
        self::assertContains("1 invitations created until", $output, "Expected invitation notified");
    }

}