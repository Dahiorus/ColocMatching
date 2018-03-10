<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Doctrine\ORM\Mapping as ORM;

/**
 * AnnouncementVisit
 *
 * @ORM\Entity
 * @ORM\Table(name="announcement_visit")
 */
class AnnouncementVisit extends Visit
{
    /**
     * @var Announcement
     *
     * @ORM\ManyToOne(targetEntity=Announcement::class, fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id", nullable=false)
     */
    protected $visited;

}
