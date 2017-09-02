<?php

namespace ColocMatching\CoreBundle\Tests\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\GroupInvitation;

class GroupInvitationRepositoryTest extends InvitationRepositoryTest {

    protected function setUp() {
        parent::setUp();
        $this->repository = self::getRepository(GroupInvitation::class);
    }
}