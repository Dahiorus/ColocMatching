<?php

namespace App\Rest\Event;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitableDto;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for visitable entities to create a visit
 *
 * @author Dahiorus
 */
class VisitEvent extends Event
{
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
