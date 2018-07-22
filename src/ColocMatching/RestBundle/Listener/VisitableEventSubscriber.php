<?php

namespace ColocMatching\RestBundle\Listener;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\Visit\VisitableDto;
use ColocMatching\CoreBundle\Manager\Visit\VisitDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Service\RoleService;
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
     * @var VisitDtoManagerInterface
     */
    private $visitManager;

    /**
     * @var RoleService
     */
    private $roleService;


    public function __construct(LoggerInterface $logger, VisitDtoManagerInterface $visitManager,
        RoleService $roleService)
    {
        $this->logger = $logger;
        $this->visitManager = $visitManager;
        $this->roleService = $roleService;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        return array (VisitEvent::ENTITY_VISITED => "generateVisit");
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
        $filter = $this->createVisitFilter($visited, $visitor);

        if ($this->skipVisit($visited, $visitor))
        {
            $this->logger->debug("Skipping the visit registration", array ("visitor" => $visitor));

            return;
        }

        $visitCount = $this->visitManager->countBy($filter);

        if ($visitCount > 0)
        {
            $this->logger->warning("The visitor is trying to spam visits", array ("event" => $event));

            return;
        }

        $visit = $this->visitManager->create($visitor, $visited);

        $this->logger->info("Visit registered", array ("visit" => $visit));
    }


    private function createVisitFilter(VisitableDto $visited, UserDto $visitor) : VisitFilter
    {
        $filter = new VisitFilter();

        $filter->setVisitedId($visited->getId());
        $filter->setVisitedClass($visited->getEntityClass());
        $filter->setVisitorId($visitor->getId());
        $filter->setVisitedAtSince(new \DateTime("1 minute ago"));

        return $filter;
    }


    private function skipVisit(VisitableDto $visited, UserDto $visitor)
    {
        if ($this->roleService->isGranted("ROLE_ADMIN", $visitor))
        {
            return true;
        }

        if ($visited instanceof UserDto)
        {
            return $visited->getId() == $visitor->getId();
        }

        if ($visited instanceof AnnouncementDto || $visited instanceof GroupDto)
        {
            return $visited->getCreatorId() == $visitor->getId();
        }

        return false;
    }

}