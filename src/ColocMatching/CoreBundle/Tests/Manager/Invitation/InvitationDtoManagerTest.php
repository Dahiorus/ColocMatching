<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Invitation;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitableDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManager;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Tests\Manager\AbstractManagerTest;

abstract class InvitationDtoManagerTest extends AbstractManagerTest
{
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
        $data = array ("email" => "user@yopmail.com",
            "firstName" => "John",
            "lastName" => "Smith",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_SEARCH);
        $recipient = $this->userManager->create($data);
        $recipient = $this->userManager->updateStatus($recipient, UserConstants::STATUS_ENABLED);

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
     * @return AbstractDto
     * @throws \Exception
     */
    protected abstract function createInvitable() : AbstractDto;


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
        $this->recipientDto = $this->userManager->updateStatus($this->recipientDto, UserConstants::STATUS_BANNED);

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
}