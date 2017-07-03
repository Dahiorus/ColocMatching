<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Visit\Visit;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisitableEventSubscriber implements EventSubscriberInterface {

    /**
     * @var VisitManagerInterface
     */
    private $visitManager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(VisitManagerInterface $visitManager, LoggerInterface $logger) {
        $this->visitManager = $visitManager;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents() {
        return array (VisitEvent::LOADED => "onReadVisitable");
    }


    public function onReadVisitable(VisitEvent $event) {
        $this->logger->debug("Creating a new visit for a visitable entity", array ("event" => $event));

        /** @var Visit */
        $visit = $this->visitManager->create($event->getVisited(), $event->getVisitor());

        $this->logger->debug("Visit created", array ("visit" => $visit));
    }

}