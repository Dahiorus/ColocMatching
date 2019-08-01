<?php

namespace App\Rest\Listener;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitableDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Security\User\TokenEncoderInterface;
use App\Core\Service\VisitorInterface;
use App\Rest\Event\Events;
use App\Rest\Event\VisitEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Visitor to dispatch an visit event on a visitable
 *
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

            $this->logger->debug("Dispatching a visit event on an entity", array ("visitable" => $visited));
            $this->eventDispatcher->dispatch(new VisitEvent($visited, $visitor), Events::ENTITY_VISITED_EVENT);
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