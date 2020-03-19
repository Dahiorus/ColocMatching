<?php

namespace App\Core\Manager\Notification;

use App\Core\DTO\User\UserDto;
use App\Mail\Entity\Email;
use App\Mail\Entity\EmailType;
use App\Mail\Service\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailManager
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MailSenderInterface
     */
    private $mailSender;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $from;


    public function __construct(LoggerInterface $logger, MailSenderInterface $mailSender,
        TranslatorInterface $translator, Environment $twig, string $from)
    {
        $this->logger = $logger;
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->from = $from;
    }


    /**
     * Sends an e-mail to a recipient with the given subject and renders the body with the given parameters
     *
     * @param UserDto $recipient The mail recipient
     * @param string $subject The mail subject translation key
     * @param string $mailTemplate The mail template name
     * @param array $subjectParams [optional] The subject parameters
     * @param array $bodyParams [optional] The body template parameters
     */
    public function sendEmail(UserDto $recipient, string $subject, string $mailTemplate, array $subjectParams = [],
        array $bodyParams = [])
    {
        $translatedSubject = $this->translator->trans($subject, $subjectParams);

        try
        {
            $body = $this->twig->render($mailTemplate, $bodyParams);
        }
        catch (LoaderError | RuntimeError | SyntaxError $e)
        {
            $this->logger->error("Unexpected error while rendering the template '{template}'",
                ["template" => $mailTemplate, "exception" => $e]);

            return;
        }

        $email = new Email();
        $email
            ->setFrom($this->from)
            ->addTo($recipient->getEmail(), $recipient->getDisplayName())
            ->setSubject($translatedSubject)
            ->setContentType(EmailType::HTML)
            ->setBody($body);

        $this->mailSender->sendEmail($email);

        $this->logger->info("E-mail [{email}] sent to [{recipient}]",
            array ("email" => $email, "recipient" => $recipient));
    }
}