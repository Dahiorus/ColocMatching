<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;

class UserGroupInvitationControllerTest extends UserInvitationControllerTest {

    protected function setUp() {
        $this->type = "group";
        $this->managerId = "coloc_matching.core.group_invitation_manager";

        parent::setUp();

        $this->authenticatedUser = UserMock::createUser(20, "group-master@test.fr", "password", "Group-Creator", "Test",
            UserConstants::TYPE_SEARCH);
        $this->mockInvitable = GroupMock::createGroup(5, $this->authenticatedUser, "Group test", "Group description");
        $this->authenticatedUser->setGroup($this->mockInvitable);
        parent::initMocks();
    }

}