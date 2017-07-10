<?php

namespace ColocMatching\MailBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * HTML mail sender service
 *
 * @author Dahiorus
 */
class HtmlMailSender extends MailSender {

    /**
     * @var EngineInterface
     */
    private $templateEngine;


    public function __construct(EngineInterface $templateEngine, \Swift_Mailer $mailer, LoggerInterface $logger) {
        parent::__construct($mailer, $logger);

        $this->templateEngine = $templateEngine;
    }


    /**
     * Sends an e-mail in HTML format to a unique recipient.
     *
     * @param string $from         The e-mail address of the sender
     * @param string $to           The e-mail address of the recipient
     * @param string $subject      The subject of the e-mail
     * @param string $templateName The name of the template to render
     * @param array $parameters    The parameters of the template
     */
    public function sendHtmlMail(string $from, string $to, string $subject, string $templateName, array $parameters = array ()) {
        $body = $this->templateEngine->render($templateName, $parameters);

        $this->logger->debug("Sending a HTML mail to one recipient",
            array ("from" => $from, "to" => $to, "subject" => $subject, "template name" => $templateName,
                "parameters" => $parameters));

        parent::sendMail($from, $to, $subject, $body, "text/html");
    }


    /**
     * Sends an e-mail in HTML format to a list of recipients.
     *
     * @param string $from         The e-mail address of the sender
     * @param array $recipients    The recipient e-mail address list
     * @param string $subject      The subject of the e-mail
     * @param string $templateName The name of the template to render
     * @param array $parameters    The parameters of the template
     */
    public function sendHtmlMassMail(string $from, array $recipients, string $subject, string $templateName,
        array $parameters = array ()) {
        $body = $this->templateEngine->render($templateName, $parameters);

        $this->logger->debug("Sending a HTML mail to a list of recipients",
            array ("from" => $from, "recipients" => $recipients, "subject" => $subject, "template name" => $templateName,
                "parameters" => $parameters));

        parent::sendMassMail($from, $recipients, $subject, $body, "text/html");
    }

}