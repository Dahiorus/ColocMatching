<?php

namespace App\Rest\Listener;

use App\Core\DTO\User\UserDto;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Rest\Event\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserDtoManagerInterface
     */
    private $userManager;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
    }


    public static function getSubscribedEvents()
    {
        return array (
            Events::USER_AUTHENTICATED_EVENT => "onUserLogin",
        );
    }


    /**
     * Updates the user last login date from the event
     *
     * @param InteractiveLoginEvent $event The login event on the user
     */
    public function onUserLogin(InteractiveLoginEvent $event) : void
    {
        $this->logger->debug("Updates the user last login date time from the event [{event}]",
            array ("event" => $event));

        /** @var UserDto $user */
        $user = $event->getAuthenticationToken()->getUser();

        try
        {
            $user->setLastLogin(new \DateTime());
            $loggedUser = $this->userManager->update($user, [], false);

            $this->logger->debug("User [{user}] has logged in at [{time}]",
                array ("user" => $loggedUser, "time" => $loggedUser->getLastLogin()));
        }
        catch (\Exception $e)
        {
            $this->logger->error("Cannot set the last login date time to [{user}]",
                array ("user" => $user, "exception" => $e));
        }
    }

}
