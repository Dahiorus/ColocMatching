<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\AnnouncementInvitation;

class AnnouncementInvitationRepositoryTest extends InvitationRepositoryTest {

    protected function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(AnnouncementInvitation::class);
    }
}