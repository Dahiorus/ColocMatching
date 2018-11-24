<?php

namespace App\Tests\Core\Manager\Invitation;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\UnavailableInvitableException;
use App\Core\Manager\Group\GroupDtoManagerInterface;

class GroupInvitationDtoManagerTest extends InvitationDtoManagerTest
{
    /** @var GroupDtoManagerInterface */
    protected $invitableDtoManager;

    /** @var GroupDto */
    protected $invitableDto;


    /**
     * @inheritdoc
     */
    protected function getInvitableDtoManagerServiceId() : string
    {
        return "coloc_matching.core.group_dto_manager";
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

        $creator = $this->createSearchUser($this->userManager, "group-owner@yopmail.com");
        $creator = $this->userManager->updateStatus($creator, UserStatus::ENABLED);

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

        $this->manager->create($this->invitableDto, $this->recipientDto, Invitation::SOURCE_INVITABLE,
            $this->testData);
    }

}