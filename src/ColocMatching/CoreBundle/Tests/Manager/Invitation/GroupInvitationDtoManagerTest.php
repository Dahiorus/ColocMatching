<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Invitation;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\UnavailableInvitableException;
use ColocMatching\CoreBundle\Manager\Group\GroupDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\GroupInvitationDtoManager;

class GroupInvitationDtoManagerTest extends InvitationDtoManagerTest
{
    /** @var GroupDtoManagerInterface */
    protected $invitableDtoManager;

    /** @var GroupDto */
    protected $invitableDto;


    protected function initManager()
    {
        $this->userManager = $this->getService("coloc_matching.core.user_dto_manager");
        $this->invitableDtoManager = $this->getService("coloc_matching.core.group_dto_manager");

        $this->dtoMapper = $this->getService("coloc_matching.core.invitation_dto_mapper");
        $entityValidator = $this->getService("coloc_matching.core.form_validator");
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");

        return new GroupInvitationDtoManager($this->logger, $this->em, $this->dtoMapper, $entityValidator,
            $userDtoMapper);
    }


    /**
     * @inheritdoc
     */
    protected function createInvitable() : AbstractDto
    {
        $data = array (
            "name" => "Group test",
            "description" => "Group test description",
            "budget" => 850
        );

        $creator = $this->userManager->create(array ("email" => "group-owner@yopmail.com",
            "firstName" => "John",
            "lastName" => "Doe",
            "plainPassword" => "secret1234",
            "type" => UserConstants::TYPE_SEARCH));
        $creator = $this->userManager->updateStatus($creator, UserConstants::STATUS_ENABLED);

        return $this->invitableDtoManager->create($creator, $data);
    }


    /**
     * @throws \Exception
     */
    public function testCreateWithUnavailableInvitableShouldThrowInvalidParameter() : void
    {
        $this->invitableDto = $this->invitableDtoManager->update($this->invitableDto,
            array ("status" => Group::STATUS_CLOSED), false);

        $this->expectException(UnavailableInvitableException::class);

        $this->manager->create($this->invitableDto->getId(), $this->recipientDto, Invitation::SOURCE_INVITABLE,
            $this->testData);
    }

}