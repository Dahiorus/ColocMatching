<?php

namespace App\Tests\Core\Manager\Invitation;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Exception\UnavailableInvitableException;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;

class AnnouncementInvitationDtoManagerTest extends InvitationDtoManagerTest
{
    /** @var AnnouncementDtoManagerInterface */
    protected $invitableDtoManager;

    /** @var AnnouncementDto */
    protected $invitableDto;


    /**
     * @inheritdoc
     */
    protected function getInvitableDtoManagerServiceId() : string
    {
        return "coloc_matching.core.announcement_dto_manager";
    }


    /**
     * @inheritdoc
     */
    protected function createInvitable() : AbstractDto
    {
        $data = array (
            "title" => "Test announcement",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 1200,
            "location" => "Paris 75020",
            "startDate" => (new \DateTime())->format("Y-m-d")
        );

        $creator = $this->userManager->create(array ("email" => "proposal@yopmail.com",
            "firstName" => "John",
            "lastName" => "Doe",
            "plainPassword" => "secret1234",
            "type" => UserType::PROPOSAL));
        $creator = $this->userManager->updateStatus($creator, UserStatus::ENABLED);

        return $this->invitableDtoManager->create($creator, $data);
    }


    /**
     * @throws \Exception
     */
    public function testCreateWithUnavailableInvitableShouldThrowInvalidParameter() : void
    {
        $this->invitableDto = $this->invitableDtoManager->update($this->invitableDto,
            array ("status" => Announcement::STATUS_FILLED), false);

        $this->expectException(UnavailableInvitableException::class);

        $this->manager->create($this->invitableDto, $this->recipientDto, Invitation::SOURCE_INVITABLE,
            $this->testData);
    }

}