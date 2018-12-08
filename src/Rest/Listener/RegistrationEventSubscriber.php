<?php

namespace App\Rest\Listener;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\RegistrationException;
use App\Core\Manager\Notification\MailManager;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Rest\Event\Events;
use App\Rest\Event\RegistrationEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationEventSubscriber implements EventSubscriberInterface
{
    private const REGISTRATION_MAIL_TEMPLATE = "mail/Registration/registration_confirmation_mail.html.twig";
    private const REGISTRATION_MAIL_TEMPLATE_SUBJECT = "mail.subject.registration";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserTokenDtoManagerInterface
     */
    private $userTokenManager;

    /**
     * @var MailManager
     */
    private $mailManager;


    public function __construct(LoggerInterface $logger, UserTokenDtoManagerInterface $userTokenManager,
        MailManager $mailManager)
    {
        $this->logger = $logger;
        $this->userTokenManager = $userTokenManager;
        $this->mailManager = $mailManager;
    }


    public static function getSubscribedEvents()
    {
        return array (Events::USER_REGISTERED_EVENT => "sendConfirmationEmail");
    }


    /**
     * Sends an e-mail to confirm the registration of a new user
     *
     * @param RegistrationEvent $event The event linked to the registration of the user
     *
     * @throws RegistrationException
     */
    public function sendConfirmationEmail(RegistrationEvent $event)
    {
        $user = $event->getUser();

        $this->logger->debug("Sending registration confirmation email to a new user [{user}]", array ("user" => $user));

        $confirmationToken = $this->createConfirmationToken($user);
        $subjectParameters = array ("%name%" => $user->getDisplayName());

        $this->mailManager->sendEmail($user, self::REGISTRATION_MAIL_TEMPLATE_SUBJECT, self::REGISTRATION_MAIL_TEMPLATE,
            $subjectParameters, array ("recipient" => $user, "confirmationToken" => $confirmationToken));

        $this->logger->info("Registration e-mail sent to the user [{user}]", array ("user" => $user));
    }


    /**
     * Creates a registration confirmation token for the registered user
     *
     * @param UserDto $user The registered user
     *
     * @return string
     * @throws RegistrationException
     */
    private function createConfirmationToken(UserDto $user) : string
    {
        $this->logger->debug("Creating a confirmation token for the registered user [{user}]", array ("user" => $user));

        try
        {
            $userToken = $this->userTokenManager->createOrUpdate($user, UserToken::REGISTRATION_CONFIRMATION,
                new \DateTimeImmutable("+1 week"));

            $this->logger->debug("Confirmation token created [{token}] for the user [{user}]",
                array ("token" => $userToken, "user" => $user));

            return $userToken->getToken();
        }
        catch (\Exception $e)
        {
            $this->logger->error("Error while trying to create a token for the registered user",
                array ("user" => $user, "exception" => $e));

            throw new RegistrationException($user, $e);
        }
    }

}
