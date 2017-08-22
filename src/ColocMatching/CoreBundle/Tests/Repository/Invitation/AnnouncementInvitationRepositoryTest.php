<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\AnnouncementInvitation;
use ColocMatching\CoreBundle\Tests\Repository\Visit\InvitationRepositoryTest;

class AnnouncementInvitationRepositoryTest extends InvitationRepositoryTest {

    protected function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(AnnouncementInvitation::class);
    }
}