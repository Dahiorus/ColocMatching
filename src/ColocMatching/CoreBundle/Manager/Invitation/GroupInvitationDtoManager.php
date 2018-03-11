<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\GroupInvitation;

class GroupInvitationDtoManager extends InvitationDtoManager
{
    protected function getDomainClass() : string
    {
        return GroupInvitation::class;
    }


    protected function getInvitableClass() : string
    {
        return Group::class;
    }

}