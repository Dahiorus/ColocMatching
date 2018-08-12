<?php

namespace ColocMatching\MailBundle\Service;

/**
 * Mail sender service interface
 *
 * @author Dahiorus
 */
interface MailSenderInterface
{
    /**
     * Sends an e-mail to a unique recipient.
     *
     * @param string $from The e-mail address of the sender
     * @param string $to The e-mail address of the recipient
     * @param string $subject The subject of the e-mail
     * @param string $body The content of the e-mail
     * @param string $contentType The content type of the e-mail
     */
    public function sendMail(string $from, string $to, string $subject, string $body, string $contentType);


    /**
     * Sends an e-mail to a list of recipients.
     *
     * @param string $from The e-mail address of the sender
     * @param array $recipients The recipient e-mail address list
     * @param string $subject The subject of the e-mail
     * @param string $body The content of the e-mail
     * @param string $contentType The content type of the e-mail
     */
    public function sendMassMail(string $from, array $recipients, string $subject, string $body, string $contentType);

}