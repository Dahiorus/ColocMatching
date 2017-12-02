<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisitableEventSubscriber implements EventSubscriberInterface {

    /**
     * @var VisitManagerInterface
     */
    private $announcementVisitManager;

    /**
     * @var VisitManagerInterface
     */
    private $groupVisitManager;

    /**
     * @var VisitManagerInterface
     */
    private $userVisitManager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(VisitManagerInterface $announcementVisitManager,
        VisitManagerInterface $groupVisitManager,
        VisitManagerInterface $userVisitManager, LoggerInterface $logger) {
        $this->announcementVisitManager = $announcementVisitManager;
        $this->groupVisitManager = $groupVisitManager;
        $this->userVisitManager = $userVisitManager;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents() {
        return array (
            VisitEvent::ANNOUNCEMENT_VISITED => "generateVisit",
            VisitEvent::GROUP_VISITED => "generateVisit",
            VisitEvent::USER_VISITED => "generateVisit");
    }


    /**
     * Creates a visit from the received event
     *
     * @param VisitEvent $event The event thrown on the read of an instance of Visitable
     *
     * @throws \Exception
     */
    public function generateVisit(VisitEvent $event) {
        $this->logger->debug("Registering a new visit for a user", array ("event" => $event));

        $visited = $event->getVisited();
        $manager = $this->getManager($visited);
        $filter = $this->createVisitFilter($visited, $event->getVisitor());

        $visitCount = $manager->countBy($filter);

        if ($visitCount > 0) {
            $this->logger->warning("The visitor is trying to spam visits", array ("event" => $event));

            return;
        }

        $visit = $manager->create($event->getVisited(), $event->getVisitor());

        $this->logger->debug("Visit registered", array ("visit" => $visit));
    }


    private function createVisitFilter(Visitable $visited, User $visitor) : VisitFilter {
        $filter = new VisitFilter();

        $filter->setVisitedId($visited->getId());
        $filter->setVisitorId($visitor->getId());
        $filter->setVisitedAtSince(new \DateTime("2 minutes ago"));

        return $filter;
    }


    /**
     * Gets the manager of the class of the visitable
     *
     * @param Visitable $visited
     *
     * @return VisitManagerInterface
     * @throws \Exception
     */
    private function getManager(Visitable $visited) : VisitManagerInterface {
        if ($visited instanceof Announcement) {
            return $this->announcementVisitManager;
        }

        if ($visited instanceof Group) {
            return $this->groupVisitManager;
        }

        if ($visited instanceof User) {
            return $this->userVisitManager;
        }

        throw new \Exception("Unknown visitable instance class");
    }

}