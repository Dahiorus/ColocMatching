<?php

namespace ColocMatching\RestBundle\Listener;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Event\RegistrationEvent;
use ColocMatching\CoreBundle\Exception\RegistrationException;
use ColocMatching\CoreBundle\Listener\MailerListener;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RegistrationEventSubscriber extends MailerListener implements EventSubscriberInterface
{
    private const REGISTRATION_MAIL_TEMPLATE = "MailBundle:Registration:confirmation_mail.html.twig";

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * RegistrationEventSubscriber constructor.
     *
     * @param MailSenderInterface $mailSender
     * @param TranslatorInterface $translator
     * @param string $from
     * @param JWTEncoderInterface $jwtEncoder
     * @param UrlGeneratorInterface $urlGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(MailSenderInterface $mailSender, TranslatorInterface $translator, string $from,
        JWTEncoderInterface $jwtEncoder, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        parent::__construct($mailSender, $translator, $from, $logger);

        $this->jwtEncoder = $jwtEncoder;
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

        $subject = $this->translator->trans("text.mail.registration.subject",
            array ("%name%" => $user->getDisplayName()));

        $this->sendMail($user, $subject, array ("user" => $user, "confirmationUrl" => $confirmationUrl));
    }


    protected function getMailTemplate() : string
    {
        return self::REGISTRATION_MAIL_TEMPLATE;
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
            // TODO create token

            return "TODO_TOKEN";
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
