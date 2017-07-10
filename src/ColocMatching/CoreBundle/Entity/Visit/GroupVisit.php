<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * GroupVisit
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Visit\GroupVisitRepository")
 * @ORM\Table(name="group_visit")
 * @JMS\ExclusionPolicy("ALL")
 */
class GroupVisit extends Visit {

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity=Group::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", nullable=false)
     */
    private $visited;


    public function __construct(Group $visited, User $visitor) {
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