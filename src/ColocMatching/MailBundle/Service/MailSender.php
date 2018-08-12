<?php

namespace ColocMatching\MailBundle\Service;

use Psr\Log\LoggerInterface;

/**
 * Mail sender service
 *
 * @author Dahiorus
 */
class MailSender implements MailSenderInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\MailBundle\Service\MailSenderInterface::sendMail()
     */
    public function sendMail(string $from, string $to, string $subject, string $body, string $contentType)
    {
        /** @var \Swift_Message */
        $mail = new \Swift_Message();

        $mail->setFrom($from);
        $mail->setTo($to);
        $mail->setSubject($subject);
        $mail->setBody($body);
        $mail->setContentType($contentType);

        $this->logger->debug(
            sprintf("Sending a mail to one recipient [from: '%s', to: '%s', mail: %s]", $from, $to, $mail),
            ["mail" => $mail]);

        $this->mailer->send($mail);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\MailBundle\Service\MailSenderInterface::sendMassMail()
     */
    public function sendMassMail(string $from, array $recipients, string $subject, string $body, string $contentType)
    {
        /** @var \Swift_Message */
        $mail = new \Swift_Message();

        $mail->setFrom($from);
        $mail->setTo($recipients);
        $mail->setSubject($subject);
        $mail->setBody($body);
        $mail->setContentType($contentType);

        $this->logger->debug(
            sprintf("Sending a mail to a list of recipients [from: '%s', recipients: [%s], mail: %s]", $from,
                implode(", ", $recipients), $mail), ["mail" => $mail]);

        $this->mailer->send($mail);
    }

}
