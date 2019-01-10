<?php

namespace App\Tests\Core\Manager\Invitation;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Invitation\Invitable;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Exception\InvalidRecipientException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManager;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\InvitationFilter;
use App\Tests\Core\Manager\AbstractManagerTest;
use App\Tests\CreateUserTrait;

abstract class InvitationDtoManagerTest extends AbstractManagerTest
{
    use CreateUserTrait;

    /** @var InvitationDtoManager */
    protected $manager;

    /** @var InvitationDto */
    protected $testDto;

    /** @var UserDtoManagerInterface */
    protected $userManager;

    /** @var DtoManagerInterface */
    protected $invitableDtoManager;

    /** @var UserDto */
    protected $recipientDto;

    /** @var InvitableDto */
    protected $invitableDto;


    protected function initManager()
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->invitableDtoManager = $this->getService($this->getInvitableDtoManagerServiceId());

        $this->dtoMapper = $this->getService("coloc_matching.core.invitation_dto_mapper");
        $entityValidator = $this->getService("coloc_matching.core.form_validator");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");

        return new InvitationDtoManager($this->logger, $this->em, $this->dtoMapper, $entityValidator,
            $userDtoMapper);
    }


    protected function initTestData() : array
    {
        return array ("message" => "This is an invitation");
    }


    protected function cleanData() : void
    {
        $this->manager->deleteAll();
        $this->invitableDtoManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @throws \Exception
     */
    protected function createRecipient() : UserDto
    {
        $recipient = $this->createSearchUser($this->userManager, "user@yopmail.com");
        $recipient = $this->userManager->updateStatus($recipient, UserStatus::ENABLED);

        return $recipient;
    }


    /**
     * @inheritdoc
     */
    protected function createAndAssertEntity()
    {
        $this->recipientDto = $this->createRecipient();
        $this->invitableDto = $this->createInvitable();

        $invitation = $this->manager->create($this->invitableDto, $this->recipientDto,
            Invitation::SOURCE_INVITABLE, $this->testData);

        $this->assertDto($invitation);

        return $invitation;
    }


    /**
     * @param InvitationDto $dto
     */
    protected function assertDto($dto) : void
    {
        parent::assertDto($dto);
        self::assertNotEmpty($dto->getInvitableId(), "Expected invitation to be linked to an invitable");
        self::assertNotEmpty($dto->getRecipientId(), "Expected invitation to be linked to a recipient");
        self::assertNotEmpty($dto->getSourceType(), "Expected invitation to have a source type");
        self::assertNotEmpty($dto->getStatus(), "Expected invitation to have a status");
    }


    /**
     * Creates an Invitable
     *
     * @return AbstractDto
     * @throws \Exception
     */
    protected abstract function createInvitable() : AbstractDto;


    /**
     * Gets the InvitableDtoManagerInterface service ID
     *
     * @return string
     */
    protected abstract function getInvitableDtoManagerServiceId() : string;


    public abstract function testCreateWithUnavailableInvitableShouldThrowInvalidParameter() : void;


    /**
     * @throws \Exception
     */
    public function testCreateWithUnknownInvitableShouldThrowEntityNotFound()
    {
        $this->expectException(EntityNotFoundException::class);

        $dto = new AnnouncementDto();
        $dto->setId(0);

        $this->manager->create($dto, $this->recipientDto, Invitation::SOURCE_INVITABLE, $this->testData);
    }


    /**
     * @throws \Exception
     */
    public function testCreateWithDisableUserShouldThrowInvalidRecipient()
    {
        $this->recipientDto = $this->userManager->updateStatus($this->recipientDto, UserStatus::BANNED);

        $this->expectException(InvalidRecipientException::class);

        $this->manager->create($this->invitableDto, $this->recipientDto, Invitation::SOURCE_INVITABLE, $this->testData);
    }


    /**
     * @throws \Exception
     */
    public function testAcceptInvitation()
    {
        $answeredInvitation = $this->manager->answer($this->testDto, true);

        $this->assertDto($answeredInvitation);
        self::assertEquals(Invitation::STATUS_ACCEPTED, $answeredInvitation->getStatus());

        // asserting the invitable has the recipient as invitee
        /** @var Invitable $invitable */
        $invitable = $this->em->find($answeredInvitation->getInvitableClass(),
            $answeredInvitation->getInvitableId());
        /** @var User $invitee */
        $invitee = $this->em->find(User::class, $answeredInvitation->getRecipientId());
        self::assertContains($invitee, $invitable->getInvitees());
    }


    /**
     * @throws \Exception
     */
    public function testRefuseInvitation()
    {
        $answeredInvitation = $this->manager->answer($this->testDto, false);

        $this->assertDto($answeredInvitation);
        self::assertEquals(Invitation::STATUS_REFUSED, $answeredInvitation->getStatus());

        // asserting the invitable has not the recipient as invitee
        /** @var Invitable $invitable */
        $invitable = $this->em->find($answeredInvitation->getInvitableClass(), $answeredInvitation->getInvitableId());
        /** @var User $user */
        $user = $this->em->find(User::class, $answeredInvitation->getRecipientId());
        self::assertFalse($invitable->getInvitees()->contains($user));
    }


    /**
     * @throws \Exception
     */
    public function testAnswerTwiceInvitationShouldThrowInvalidParameter()
    {
        $answeredInvitation = $this->manager->answer($this->testDto, true);

        $this->expectException(InvalidParameterException::class);

        $this->manager->answer($answeredInvitation, false);
    }


    /**
     * @throws \Exception
     */
    public function testAcceptInvitationAsGroupCreatorShouldInviteMembers()
    {
        /** @var GroupDtoManagerInterface $groupManager */
        $groupManager = $this->getService("coloc_matching.core.group_dto_manager");

        foreach ([1, 2, 3] as $i)
        {
            $group = $groupManager->create($this->recipientDto, array (
                "name" => "group $i",
                "budget" => $i * 260,
                "description" => "group $i description"
            ));

            $member = $this->createSearchUser($this->userManager, "group-$i-member@yopmail.com", UserStatus::ENABLED);
            $groupManager->addMember($group, $member);
        }

        $this->manager->answer($this->testDto, true);

        $filter = new InvitationFilter();
        $filter
            ->setInvitableId($this->testDto->getInvitableId())
            ->setInvitableClass($this->testDto->getInvitableClass())
            ->setStatus(Invitation::STATUS_WAITING)
            ->setSourceTypes([Invitation::SOURCE_INVITABLE]);
        $invitations = $this->manager->search($filter);

        self::assertCount(3, $invitations->getContent(), "Expected to have 3 new invitations");

        $groupManager->deleteAll();
    }
}
