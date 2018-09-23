<?php

namespace App\Mail\Service;

use App\Mail\Entity\Email;

/**
 * Mail sender service interface
 *
 * @author Dahiorus
 */
interface MailSenderInterface
{
    /**
     * Sends an e-mail
     *
     * @param Email $email The e-mail to send
     */
    public function sendEmail(Email $email) : void;


    /**
     * Sends a collection of e-mails
     *
     * @param Email[] $emails The collection of e-mails to send
     */
    public function sendEmails(array $emails) : void;

}
