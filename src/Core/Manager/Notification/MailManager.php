<?php

namespace App\Core\Manager\Notification;

use App\Core\DTO\User\UserDto;
use App\Mail\Entity\Email;
use App\Mail\Entity\EmailType;
use App\Mail\Service\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var EngineInterface
     */
    private $templateEngine;

    /**
     * @var string
     */
    private $from;


    public function __construct(LoggerInterface $logger, MailSenderInterface $mailSender,
        TranslatorInterface $translator, EngineInterface $templateEngine, string $from)
    {
        $this->logger = $logger;
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->templateEngine = $templateEngine;
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
        $body = $this->templateEngine->render($mailTemplate, $bodyParams);

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