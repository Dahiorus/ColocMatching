<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\VisitableDto;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Security\User\JwtUserExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Visitor to dispatch an visit event on a visitable
 */
class EventDispatcherVisitor implements VisitorInterface
{
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
        LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->jwtUserExtractor = $jwtUserExtractor;
        $this->logger = $logger;
    }


    /**
     * @inheritdoc
     */
    public function visit(VisitableDto $visited) : void
    {
        try
        {
            $visitor = $this->jwtUserExtractor->getAuthenticatedUser();

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
        catch (JWTDecodeFailureException | EntityNotFoundException $e)
        {
            $this->logger->error("Unexpected error while dispatching a visit event", array ("exception" => $e));
        }
    }

}