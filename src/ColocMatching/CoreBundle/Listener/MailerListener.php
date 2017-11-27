<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\MailBundle\Service\HtmlMailSender;
use ColocMatching\MailBundle\Service\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Base class to extends to use the html mailer service
 *
 * @author Dahiorus
 */
abstract class MailerListener {

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HtmlMailSender
     */
    protected $mailSender;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $from;


    public function __construct(MailSenderInterface $mailSender, TranslatorInterface $translator, string $from,
        LoggerInterface $logger) {
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->from = $from;
        $this->logger = $logger;
    }


    /**
     * Sends an e-mail to a recipient with the given subject and renders the body with the given parameters
     *
     * @param User $recipient           The e-mail recipient
     * @param string $subject           The e-mail subject
     * @param array $templateParameters [Optional] The parameters of the template which serves as the e-mail body
     */
    protected function sendMail(User $recipient, string $subject, array $templateParameters = array ()) {
        $this->mailSender->sendHtmlMail($this->from, $recipient->getEmail(), $subject, $this->getMailTemplate(),
            $templateParameters);
    }


    /**
     * Gets the name of the template used for the e-mail
     * @return string
     */
    protected abstract function getMailTemplate() : string;
}