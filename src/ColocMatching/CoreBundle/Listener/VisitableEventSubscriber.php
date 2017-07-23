<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visit;
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


    public function __construct(VisitManagerInterface $announcementVisitManager, VisitManagerInterface $groupVisitManager,
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
            VisitEvent::ANNOUNCEMENT_VISITED => "onReadAnnouncement",
            VisitEvent::GROUP_VISITED => "onReadGroup",
            VisitEvent::USER_VISITED => "onReadUser");
    }


    public function onReadAnnouncement(VisitEvent $event) {
        $this->logger->debug("Creating a new visit for an announcement", array ("event" => $event));

        $filter = $this->createVisitFilter($event->getVisited(), $event->getVisitor());
        $visits = $this->announcementVisitManager->search($filter);

        if (!empty($visits)) {
            $this->logger->warning("The visitor is trying to spam visits", array ("event" => $event));

            return;
        }

        /** @var Visit */
        $visit = $this->announcementVisitManager->create($event->getVisited(), $event->getVisitor());

        $this->logger->debug("Visit created", array ("visit" => $visit));
    }


    public function onReadGroup(VisitEvent $event) {
        $this->logger->debug("Creating a new visit for a group", array ("event" => $event));

        $filter = $this->createVisitFilter($event->getVisited(), $event->getVisitor());
        $visits = $this->groupVisitManager->search($filter);

        if (!empty($visits)) {
            $this->logger->warning("The visitor is trying to spam visits", array ("event" => $event));

            return;
        }

        /** @var Visit */
        $visit = $this->groupVisitManager->create($event->getVisited(), $event->getVisitor());

        $this->logger->debug("Visit created", array ("visit" => $visit));
    }


    public function onReadUser(VisitEvent $event) {
        $this->logger->debug("Creating a new visit for a user", array ("event" => $event));

        $filter = $this->createVisitFilter($event->getVisited(), $event->getVisitor());
        $visits = $this->userVisitManager->search($filter);

        if (!empty($visits)) {
            $this->logger->warning("The visitor is trying to spam visits", array ("event" => $event));

            return;
        }

        /** @var Visit */
        $visit = $this->userVisitManager->create($event->getVisited(), $event->getVisitor());

        $this->logger->debug("Visit created", array ("visit" => $visit));
    }


    private function createVisitFilter(Visitable $visited, User $visitor) : VisitFilter {
        $filter = new VisitFilter();

        $filter->setVisitedId($visited->getId());
        $filter->setVisitorId($visitor->getId());
        $filter->setVisitedAtSince(new \DateTime("2 minutes ago"));

        return $filter;
    }

}