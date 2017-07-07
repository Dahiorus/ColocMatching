<?php

namespace ColocMatching\CoreBundle\Event;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event for visitable entities to create a visit
 *
 * @author Dahiorus
 */
class VisitEvent extends Event {

    const ANNOUNCEMENT_VISITED = "coloc_matching.announcement.visited";
    const GROUP_VISITED = "coloc_matching.group.visited";
    const USER_VISITED = "coloc_matching.user.visited";

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