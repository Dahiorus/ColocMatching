<?php

namespace ColocMatching\RestBundle\Event;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event for visitable entities to create a visit
 *
 * @author Dahiorus
 */
class VisitEvent extends Event
{
    const ENTITY_VISITED = "coloc_matching.entity_visited";

    /**
     * @var VisitableDto
     */
    private $visited;

    /**
     * @var UserDto
     */
    private $visitor;


    public function __construct(VisitableDto $visited, UserDto $visitor)
    {
        $this->visited = $visited;
        $this->visitor = $visitor;
    }


    public function __toString()
    {
        return "VisitEvent [visited = {" . $this->visited->getEntityClass() . ": " . $this->visited->getId()
            . "}, visitor = " . $this->visitor . "]";
    }


    public function getVisited()
    {
        return $this->visited;
    }


    public function getVisitor()
    {
        return $this->visitor;
    }

}