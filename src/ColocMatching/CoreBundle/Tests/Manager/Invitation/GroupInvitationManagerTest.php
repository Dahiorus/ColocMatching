<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Group\Group;

class GroupInvitationManagerTest extends InvitationManagerTest {

    protected function setUp() {
        $this->invitableClass = Group::class;

        parent::setUp();
    }
}