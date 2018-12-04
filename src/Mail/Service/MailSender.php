<?php

namespace App\Mail\Service;

use App\Mail\Entity\Email;
use App\Mail\Entity\EmailAddressType;
use Psr\Log\LoggerInterface;

/**
 * Mail sender service
 *
 * @author Dahiorus
 */
class MailSender implements MailSenderInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;


    public function __construct(LoggerInterface $logger, \Swift_Mailer $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }


    /**
     * @inheritdoc
     */
    public function sendEmail(Email $email) : void
    {
        $this->logger->debug("Sending one e-mail [{email}]", array ("email" => $email));

        $this->mailer->send($this->convertToSwiftMessage($email));
    }


    /**
     * @inheritdoc
     */
    public function sendEmails(array $emails) : void
    {
        $this->logger->debug("Sending e-mails [{emails}]", array ("emails" => $emails));

        foreach ($emails as $email)
        {
            $this->sendEmail($email);
        }
    }


    /**
     * Builds a Swift_Message from the specified Email
     *
     * @param Email $email The email to transform into a Swift_Message
     *
     * @return \Swift_Message
     */
    protected function convertToSwiftMessage(Email $email) : \Swift_Message
    {
        /** @var \Swift_Message */
        $swiftMsg = new \Swift_Message($email->getSubject(), $email->getBody(), $email->getContentType(), "UTF-8");

        $swiftMsg->setFrom($email->getSender()->getAddress(), $email->getSender()->getDisplayName());

        foreach ($email->getRecipients() as $recipient)
        {
            $address = $recipient->getAddress();
            $name = $recipient->getDisplayName();

            switch ($recipient->getType())
            {
                case EmailAddressType::TO:
                    $swiftMsg->addTo($address, $name);
                    break;
                case EmailAddressType::BCC:
                    $swiftMsg->addBcc($address, $name);
                    break;
                case EmailAddressType::CC:
                    $swiftMsg->addCc($address, $name);
                    break;
                case EmailAddressType::REPLY_TO:
                    $swiftMsg->addReplyTo($address, $name);
                    break;
                default:
                    $this->logger->warning("Unknown recipient type found for [{recipient}] in the email [{email}]",
                        array ("email" => $email, "recipient" => $recipient));
                    break;
            }
        }

        $this->logger->debug("Email converted to Swift_Message", array ("msg" => $swiftMsg));

        return $swiftMsg;
    }

}
