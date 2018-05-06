<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\MailBundle\Service\HtmlMailSender;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MailerService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HtmlMailSender
     */
    private $mailSender;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $from;


    /**
     * MailerService constructor.
     *
     * @param LoggerInterface $logger
     * @param MailSenderInterface $mailSender
     * @param TranslatorInterface $translator
     * @param string $from
     */
    public function __construct(LoggerInterface $logger, MailSenderInterface $mailSender,
        TranslatorInterface $translator, string $from)
    {
        $this->logger = $logger;
        $this->mailSender = $mailSender;
        $this->translator = $translator;
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
    public function sendMail($recipient, string $subjectTemplate, string $mailTemplate,
        array $subjectParameters = array (), array $templateParameters = array ())
    {
        $subject = $this->translator->trans($subjectTemplate, $subjectParameters);

        $this->mailSender->sendHtmlMail(
            $this->from, $recipient->getEmail(), $subject, $mailTemplate, $templateParameters);
    }
}