<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Invitation\AnnouncementInvitation;

class AnnouncementInvitationDtoManager extends InvitationDtoManager
{
    protected function getDomainClass() : string
    {
        return AnnouncementInvitation::class;
    }


    protected function getInvitableClass() : string
    {
        return Announcement::class;
    }

}