<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * AnnouncementVisit
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Visit\UserVisitRepository")
 * @ORM\Table(name="user_visit")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="UserVisit")
 */
class UserVisit extends Visit {

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @SWG\Property(description="The user visited", ref="#/definitions/User")
     */
    private $visited;


    public function __construct(User $visited, User $visitor) {
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
