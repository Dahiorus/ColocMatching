<?php

namespace ColocMatching\RestBundle\Listener;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserToken;
use ColocMatching\CoreBundle\Exception\RegistrationException;
use ColocMatching\CoreBundle\Manager\User\UserTokenDtoManagerInterface;
use ColocMatching\CoreBundle\Service\MailerService;
use ColocMatching\RestBundle\Event\RegistrationEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationEventSubscriber implements EventSubscriberInterface
{
    private const REGISTRATION_MAIL_TEMPLATE = "MailBundle:Registration:confirmation_mail.html.twig";

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
        return array (RegistrationEvent::REGISTERED_EVENT => "sendConfirmationEmail");
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
