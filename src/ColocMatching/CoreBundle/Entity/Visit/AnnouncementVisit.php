<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * AnnouncementVisit
 *
 * @ORM\Entity
 * @ORM\Table(name="announcement_visit")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="AnnouncementVisit")
 */
class AnnouncementVisit extends Visit {

    /**
     * @var Announcement
     *
     * @ORM\ManyToOne(targetEntity=Announcement::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="announcement_id", nullable=false, onDelete="CASCADE")
     * @JMS\Expose()
     * @SWG\Property(description="The announcement visited", ref="#/definitions/Announcement")
     */
    private $visited;


    public function __construct(Announcement $visited, User $visitor) {
        parent::__construct($visitor);

        $this->visited = $visited;
    }


    public function getVisited() : Visitable {
        return $this->visited;
    }


    public function setVisited(Visitable $visited = null) {
        $this->visited = $visited;

        return $this;
    }

}
