<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Security\User\JwtUserExtractor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Visitor to dispatch an visit event on a visitable
 */
class EventDispatcherVisitor implements VisitorInterface {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var JwtUserExtractor
     */
    private $jwtUserExtractor;


    public function __construct(EventDispatcherInterface $eventDispatcher, JwtUserExtractor $jwtUserExtractor,
        LoggerInterface $logger) {

        $this->eventDispatcher = $eventDispatcher;
        $this->jwtUserExtractor = $jwtUserExtractor;
        $this->logger = $logger;
    }


    public function visit(Visitable $visited) {
        $visitor = $this->jwtUserExtractor->getAuthenticatedUser();

        if (empty($visitor)) {
            return;
        }

        $event = new VisitEvent($visited, $visitor);

        if ($visited instanceof Announcement) {
            $this->logger->debug("Dispatching a visit event on an announcement", array ("visitable" => $visited));

            $this->eventDispatcher->dispatch(VisitEvent::ANNOUNCEMENT_VISITED, $event);
        }
        else if ($visited instanceof Group) {
            $this->logger->debug("Dispatching a visit event on a group", array ("visitable" => $visited));

            $this->eventDispatcher->dispatch(VisitEvent::GROUP_VISITED, $event);
        }
        else if ($visited instanceof User) {
            $this->logger->debug("Dispatching a visit event on a user", array ("visitable" => $visited));

            $this->eventDispatcher->dispatch(VisitEvent::USER_VISITED, $event);
        }
    }

}