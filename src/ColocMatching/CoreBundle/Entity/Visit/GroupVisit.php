<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\Group\Group;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * GroupVisit
 *
 * @ORM\Entity
 * @ORM\Table(name="group_visit")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="GroupVisit")
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_group_visit", absolute=true,
 *     parameters={ "id" = "expr(object.getVisited().getId())", "visitId" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name= "visited",
 *   href= @Hateoas\Route(name="rest_get_group", absolute=true,
 *     parameters={ "id" = "expr(object.getVisited().getId())" })
 * )
 */
class GroupVisit extends Visit
{
    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity=Group::class, fetch="LAZY")
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     */
    protected $visited;

}