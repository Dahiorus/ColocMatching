<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\MailBundle\Service\HtmlMailSender;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Doctrine\ORM\Mapping\PostPersist;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Event listener for user registration.
 *
 * @author Dahiorus
 */
class UserListener {

    private const REGISTRATION_MAIL_TEMPLATE = "MailBundle:Registration:confirmation_mail.html.twig";

    /**
     * @var HtmlMailSender
     */
    private $mailSender;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(MailSenderInterface $mailSender, TranslatorInterface $translator,
        LoggerInterface $logger) {
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->logger = $logger;
    }


    /**
     * Callback event after the user is persisted.
     * Sends an e-mail to the user to confirm the registration and enables the user.
     *
     * @PostPersist()
     *
     * @param User $user The created user who will receive the e-mail
     */
    public function sendConfirmationEmail(User $user) {
        $this->logger->debug(
            sprintf("Sending registration confirmation email to a new user [username: '%s']", $user->getUsername()),
            [ "user" => $user]);

        $this->mailSender->sendHtmlMail("no-reply@coloc-matching.fr", $user->getEmail(),
            $this->translator->trans("text.mail.registration.subject",
                [ "%name%" => $user->getFirstname() . " " . $user->getLastname()]), self::REGISTRATION_MAIL_TEMPLATE,
            array ("user" => $user));
    }

}