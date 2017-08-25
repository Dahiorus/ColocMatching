<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;

class UserAnnouncementInvitationControllerTest extends UserInvitationControllerTest {

    protected function setUp() {
        $this->type = "announcement";
        $this->managerId = "coloc_matching.core.announcement_invitation_manager";

        parent::setUp();

        $this->authenticatedUser = UserMock::createUser(20, "proposal@test.fr", "password", "Proposal", "Test",
            UserConstants::TYPE_PROPOSAL);
        $this->mockInvitable = AnnouncementMock::createAnnouncement(5, $this->authenticatedUser, "Paris 75008",
            "Announcement test", Announcement::TYPE_RENT, 1540, new \DateTime());
        $this->authenticatedUser->setAnnouncement($this->mockInvitable);

        parent::initMocks();
    }

}