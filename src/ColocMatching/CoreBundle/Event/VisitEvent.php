<?php

namespace ColocMatching\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Entity\User\User;

/**
 * Event for visitable entities to create a visit
 *
 * @author Dahiorus
 */
class VisitEvent extends Event {

    const LOADED = "coloc_matching.visitable.loaded";

    /**
     * @var Visitable
     */
    private $visited;

    /**
     * @var User
     */
    private $visitor;


    public function __construct(Visitable $visited, User $visitor) {
        $this->visited = $visited;
        $this->visitor = $visitor;
    }


    public function getVisited() {
        return $this->visited;
    }


    public function getVisitor() {
        return $this->visitor;
    }

}