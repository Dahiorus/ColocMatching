<?php

namespace App\Rest\Listener;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\RegistrationException;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Core\Service\MailerService;
use App\Rest\Event\Events;
use App\Rest\Event\RegistrationEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationEventSubscriber implements EventSubscriberInterface
{
    private const REGISTRATION_MAIL_TEMPLATE = "mail/Registration/confirmation_mail.html.twig";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserTokenDtoManagerInterface
     */
    private $userTokenManager;

    /**
     * @var MailerService
     */
    private $mailer;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    public function __construct(LoggerInterface $logger, UserTokenDtoManagerInterface $userTokenManager,
        MailerService $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->logger = $logger;
        $this->userTokenManager = $userTokenManager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
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

        $this->logger->info("Sending registration confirmation email to a new user", array ("user" => $user));

        $confirmationUrl = $this->buildConfirmationUrl($this->createConfirmationToken($user));

        $subject = "text.mail.registration.subject";
        $subjectParameters = array ("%name%" => $user->getDisplayName());

        $this->mailer->sendMail($user, $subject, self::REGISTRATION_MAIL_TEMPLATE, $subjectParameters,
            array ("user" => $user, "confirmationUrl" => $confirmationUrl));

        $this->logger->info("Registration e-mail sent to the user", array ("user" => $user));
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
        $this->logger->debug("Creating a confirmation token for the registered user", array ("user" => $user));

        try
        {
            $userToken = $this->userTokenManager->create($user, UserToken::REGISTRATION_CONFIRMATION);

            $this->logger->debug("Confirmation token created", array ("token" => $userToken, "user" => $user));

            return $userToken->getToken();
        }
        catch (\Exception $e)
        {
            $this->logger->error("Error while trying to create a token for the registered user",
                array ("user" => $user, "exception" => $e));

            throw new RegistrationException($user, $e);
        }
    }


    /**
     * Builds an URL containing the confirmation token
     *
     * @param string $confirmationToken The registration confirmation token
     *
     * @return string
     */
    private function buildConfirmationUrl(string $confirmationToken) : string
    {
        return $this->urlGenerator->generate("coloc_matching.confirmation_url", array ("token" => $confirmationToken),
            $this->urlGenerator::ABSOLUTE_URL);
    }

}
