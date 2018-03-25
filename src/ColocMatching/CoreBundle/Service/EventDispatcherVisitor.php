<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\RestBundle\Event\VisitEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Visitor to dispatch an visit event on a visitable
 * @author Dahiorus
 */
class EventDispatcherVisitor implements VisitorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenEncoderInterface
     */
    private $tokenEncoder;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack, TokenEncoderInterface $tokenEncoder)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * @inheritdoc
     */
    public function visit(VisitableDto $visited) : void
    {
        try
        {
            /** @var UserDto $visitor */
            $visitor = $this->getVisitor();

            if (empty($visitor))
            {
                return;
            }

            $event = new VisitEvent($visited, $visitor);

            if ($visited instanceof AnnouncementDto)
            {
                $this->logger->debug("Dispatching a visit event on an announcement", array ("visitable" => $visited));
                $this->eventDispatcher->dispatch(VisitEvent::ANNOUNCEMENT_VISITED, $event);
            }
            else if ($visited instanceof GroupDto)
            {
                $this->logger->debug("Dispatching a visit event on a group", array ("visitable" => $visited));
                $this->eventDispatcher->dispatch(VisitEvent::GROUP_VISITED, $event);
            }
            else if ($visited instanceof UserDto)
            {
                $this->logger->debug("Dispatching a visit event on a user", array ("visitable" => $visited));
                $this->eventDispatcher->dispatch(VisitEvent::USER_VISITED, $event);
            }
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->error("Unexpected error while dispatching a visit event", array ("exception" => $e));
        }
    }


    /**
     * Gets the visitor from the current request
     *
     * @return UserDto|null
     * @throws EntityNotFoundException
     */
    private function getVisitor()
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->tokenEncoder->decode($request);
    }

}