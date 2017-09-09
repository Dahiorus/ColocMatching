<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * AnnouncementVisit
 *
 * @ORM\Entity
 * @ORM\Table(name="user_visit")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="UserVisit")
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_user_visit", absolute=true,
 *     parameters={ "id" = "expr(object.getVisited().getId())", "visitId" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name= "visited",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getVisited().getId())" })
 * )
 */
class UserVisit extends Visit {

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, cascade={ "persist" }, fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
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
