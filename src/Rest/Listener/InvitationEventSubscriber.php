<?php

namespace App\Rest\Listener;

use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\Notification\InvitationNotifier;
use App\Rest\Event\Events;
use App\Rest\Event\InvitationCreatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvitationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InvitationNotifier
     */
    private $notifier;


    public function __construct(LoggerInterface $logger, InvitationNotifier $notifier)
    {
        $this->logger = $logger;
        $this->notifier = $notifier;
    }


    public static function getSubscribedEvents()
    {
        return array (Events::INVITATION_CREATED_EVENT => "notifyRecipient");
    }


    public function notifyRecipient(InvitationCreatedEvent $event)
    {
        $this->logger->debug("Notifying the invitation recipient from the event [{event}]", array ("event" => $event));

        try
        {
            $this->notifier->sendInvitationMail($event->getInvitation());
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->error("Unable to notify the invitation recipient from the event [{event}] -> {exception}",
                array ("event" => $event, "exception" => $e));
        }
    }

}