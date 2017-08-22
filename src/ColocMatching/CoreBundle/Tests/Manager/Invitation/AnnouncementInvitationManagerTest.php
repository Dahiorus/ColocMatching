<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

class AnnouncementInvitationManagerTest extends InvitationManagerTest {

    protected function setUp() {
        $this->invitableClass = Announcement::class;

        parent::setUp();
    }
}