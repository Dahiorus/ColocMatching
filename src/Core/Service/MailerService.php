<?php

namespace App\Core\Service;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\User;
use App\Mail\Entity\Email;
use App\Mail\Entity\EmailType;
use App\Mail\Service\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MailerService
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
     * @param UserDto|User $recipient The e-mail recipient
     * @param string $subjectTemplate The e-mail subject template name
     * @param string $mailTemplate The mail template name
     * @param array $subjectParameters [Optional] The parameters of the template which serves as the e-mail subject
     * @param array $templateParameters [Optional] The parameters of the template which serves as the e-mail body
     */
    public function sendEmail($recipient, string $subjectTemplate, string $mailTemplate,
        array $subjectParameters = array (), array $templateParameters = array ())
    {
        $subject = $this->translator->trans($subjectTemplate, $subjectParameters);
        $body = $body = $this->templateEngine->render($mailTemplate, $templateParameters);

        $email = new Email();
        $email
            ->setFrom($this->from)
            ->addTo($recipient->getEmail(), $recipient->getFirstName() . " " . $recipient->getLastName())
            ->setSubject($subject)
            ->setContentType(EmailType::HTML)
            ->setBody($body);

        $this->mailSender->sendEmail($email);

        $this->logger->debug("E-mail sent to the recipient", array ("email" => $email, "recipient" => $recipient));
    }

}
