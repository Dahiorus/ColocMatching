<?php

namespace ColocMatching\RestBundle\Listener;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Manager\Visit\AnnouncementVisitDtoManager;
use ColocMatching\CoreBundle\Manager\Visit\GroupVisitDtoManager;
use ColocMatching\CoreBundle\Manager\Visit\UserVisitDtoManager;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\RestBundle\Event\VisitEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisitableEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AnnouncementVisitDtoManager
     */
    private $announcementVisitManager;

    /**
     * @var GroupVisitDtoManager
     */
    private $groupVisitManager;

    /**
     * @var UserVisitDtoManager
     */
    private $userVisitManager;


    public function __construct(LoggerInterface $logger, AnnouncementVisitDtoManager $announcementVisitManager,
        GroupVisitDtoManager $groupVisitManager, UserVisitDtoManager $userVisitManager)
    {
        $this->logger = $logger;
        $this->announcementVisitManager = $announcementVisitManager;
        $this->groupVisitManager = $groupVisitManager;
        $this->userVisitManager = $userVisitManager;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
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
    public function generateVisit(VisitEvent $event)
    {
        $this->logger->debug("Registering a new visit for a user", array ("event" => $event));

        $visited = $event->getVisited();
        $visitor = $event->getVisitor();
        $manager = $this->getManager($visited);
        $filter = $this->createVisitFilter($visited, $visitor);

        $visitCount = $manager->countBy($filter);

        if ($visitCount > 0)
        {
            $this->logger->warning("The visitor is trying to spam visits", array ("event" => $event));

            return;
        }

        $visit = $manager->create($visited->getId(), $visitor);

        $this->logger->debug("Visit registered", array ("visit" => $visit));
    }


    private function createVisitFilter(VisitableDto $visited, UserDto $visitor) : VisitFilter
    {
        $filter = new VisitFilter();

        $filter->setVisitedId($visited->getId());
        $filter->setVisitorId($visitor->getId());
        $filter->setVisitedAtSince(new \DateTime("2 minutes ago"));

        return $filter;
    }


    /**
     * Gets the manager of the class of the visitable
     *
     * @param VisitableDto $visited
     *
     * @return VisitDtoManagerInterface
     */
    private function getManager(VisitableDto $visited) : VisitDtoManagerInterface
    {
        if ($visited instanceof AnnouncementDto)
        {
            return $this->announcementVisitManager;
        }

        if ($visited instanceof GroupDto)
        {
            return $this->groupVisitManager;
        }

        if ($visited instanceof UserDto)
        {
            return $this->userVisitManager;
        }

        throw new \InvalidArgumentException("Unknown visitable instance class");
    }

}