<?php

namespace App\Tests\Core\Manager\Notification;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\User;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Notification\InvitationNotifier;
use App\Core\Manager\Notification\MailManager;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Tests\AbstractServiceTest;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\MockObject;

class InvitationNotifierTest extends AbstractServiceTest
{
    /** @var MockObject */
    private $mailManager;

    /** @var MockObject */
    private $userManager;

    /** @var MockObject */
    private $announcementManager;

    /** @var MockObject */
    private $groupManager;

    /** @var InvitationNotifier */
    private $notifier;


    protected function setUp()
    {
        parent::setUp();

        $this->mailManager = $this->createMock(MailManager::class);
        $this->userManager = $this->createMock(UserDtoManagerInterface::class);
        $this->announcementManager = $this->createMock(AnnouncementDtoManagerInterface::class);
        $this->groupManager = $this->createMock(GroupDtoManagerInterface::class);

        $this->notifier = new InvitationNotifier($this->logger, $this->mailManager, $this->userManager,
            $this->announcementManager, $this->groupManager);
    }


    private function createInvitation(string $status, string $source, string $invitableClass) : InvitationDto
    {
        $invitation = new InvitationDto();

        $invitation->setId(1)
            ->setStatus($status)
            ->setInvitableClass($invitableClass)
            ->setInvitableId(1)
            ->setRecipientId(1)
            ->setSourceType($source);

        return $invitation;
    }


    private function mockGetInvitable(InvitationDto $invitation, bool $invitableNotFound, bool $creatorNotFound) : void
    {
        $invitableClass = $invitation->getInvitableClass();
        $invitableId = $invitation->getInvitableId();

        if ($invitableClass == Announcement::class)
        {
            $invitableManager = $this->announcementManager;
            $invitable = new AnnouncementDto();
            $invitable->setId($invitableId)
                ->setCreatorId(10);
        }
        else if ($invitableClass == Group::class)
        {
            $invitableManager = $this->groupManager;
            $invitable = new GroupDto();
            $invitable->setId($invitableId)
                ->setCreatorId(10);
        }
        else
        {
            return;
        }

        if ($invitableNotFound)
        {
            $invitableManager->expects(self::once())
                ->method("read")
                ->with($invitableId)
                ->willThrowException(new EntityNotFoundException($invitableClass, "id", $invitableId));
        }
        else
        {
            $invitableManager->expects(self::once())
                ->method("read")
                ->with($invitable->getId())
                ->willReturn($invitable);
            $this->mockGetUser($invitable->getCreatorId(), self::at(1), $creatorNotFound);
        }
    }


    private function mockGetUser(int $id, Invocation $invocation, bool $userNotFound)
    {
        if ($userNotFound)
        {
            $this->userManager->expects($invocation)
                ->method("read")
                ->with($id)
                ->willThrowException(new EntityNotFoundException(User::class, "id", $id));
        }
        else
        {
            $user = new UserDto();
            $user->setId($id);

            $this->userManager->expects($invocation)
                ->method("read")
                ->with($id)
                ->willReturn($user);
        }
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailForSearchInvitation()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_SEARCH,
            Announcement::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, false);

        $this->mailManager->expects(self::once())->method("sendEmail");

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailForInvitableInvitation()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_INVITABLE,
            Group::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, false);

        $this->mailManager->expects(self::once())->method("sendEmail");

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailWithNonExistingInvitableShouldThrowException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_INVITABLE,
            Announcement::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, true, false);

        $this->expectException(EntityNotFoundException::class);

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailWithNonExistingInvitableCreatorShouldThrowException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_SEARCH,
            Announcement::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, true);

        $this->expectException(EntityNotFoundException::class);

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailToNonExistingRecipientShouldThrowException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_SEARCH,
            Announcement::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), true);

        $this->expectException(EntityNotFoundException::class);

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailForUnknownInvitableTypeShouldThrowRuntimeException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_SEARCH,
            "fsdlfqjskd");

        $this->expectException(\RuntimeException::class);

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendAnswerMailForUnknownSourceTypeShouldThrowRuntimeException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, "fdslfjqsdlfj",
            Group::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, false);

        $this->expectException(\RuntimeException::class);

        $this->notifier->sendAnswerMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendInvitationMailForSearchInvitation()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_WAITING, Invitation::SOURCE_SEARCH,
            Announcement::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, false);

        $this->mailManager->expects(self::once())->method("sendEmail");

        $this->notifier->sendInvitationMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendInvitationMailForInvitableInvitation()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_WAITING, Invitation::SOURCE_INVITABLE,
            Announcement::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, false);

        $this->mailManager->expects(self::once())->method("sendEmail");

        $this->notifier->sendInvitationMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendInvitationMailForUnknownInvitableTypeShouldThrowRuntimeException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, Invitation::SOURCE_SEARCH,
            "fsdlfqjskd");

        $this->expectException(\RuntimeException::class);

        $this->notifier->sendInvitationMail($invitation);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendInvitationMailForUnknownSourceTypeShouldThrowRuntimeException()
    {
        $invitation = $this->createInvitation(Invitation::STATUS_ACCEPTED, "fdslfjqsdlfj",
            Group::class);
        $this->mockGetUser($invitation->getRecipientId(), self::at(0), false);
        $this->mockGetInvitable($invitation, false, false);

        $this->expectException(\RuntimeException::class);

        $this->notifier->sendInvitationMail($invitation);
    }

}
