<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Invitation;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\UnavailableInvitableException;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;

class AnnouncementInvitationDtoManagerTest extends InvitationDtoManagerTest
{
    /** @var AnnouncementDtoManagerInterface */
    protected $invitableDtoManager;

    /** @var AnnouncementDto */
    protected $invitableDto;


    protected function setUp()
    {
        $this->invitableDtoManager = $this->getService("coloc_matching.core.announcement_dto_manager");
        parent::setUp();
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
            "type" => UserConstants::TYPE_PROPOSAL));
        $creator = $this->userManager->updateStatus($creator, UserConstants::STATUS_ENABLED);

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