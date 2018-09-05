<?php

namespace App\Mail\Entity;

/**
 * A representation of an e-mail
 *
 * @author Dahiorus
 */
class Email
{
    /**
     * @var EmailAddress
     */
    private $sender;

    /**
     * @var EmailAddress[]
     */
    private $recipients = array ();

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;


    public function __toString()
    {
        return "Email [sender = " . $this->sender . ", recipients = {" . implode(", ", $this->recipients)
            . ", subject = '" . $this->subject . "', contentType = '" . $this->contentType . "', body = '" . $this->body
            . "']";
    }


    public function getSender()
    {
        return $this->sender;
    }


    public function setSender(EmailAddress $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }


    public function getRecipients()
    {
        return $this->recipients;
    }


    public function setRecipients(array $recipients = [])
    {
        $this->recipients = $recipients;

        return $this;
    }


    public function getContentType()
    {
        return $this->contentType;
    }


    public function setContentType(?string $contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }


    public function getSubject()
    {
        return $this->subject;
    }


    public function setSubject(?string $subject)
    {
        $this->subject = $subject;

        return $this;
    }


    public function getBody()
    {
        return $this->body;
    }


    public function setBody(?string $body)
    {
        $this->body = $body;

        return $this;
    }


    public function addTo(string $emailAddress, string $displayName = null)
    {
        return $this->addRecipient(EmailAddressType::TO, $emailAddress, $displayName);
    }


    public function addCc(string $emailAddress, string $displayName = null)
    {
        return $this->addRecipient(EmailAddressType::CC, $emailAddress, $displayName);
    }


    public function addBcc(string $emailAddress, string $displayName = null)
    {
        return $this->addRecipient(EmailAddressType::BCC, $emailAddress, $displayName);
    }


    public function addReplyTo(string $emailAddress, string $displayName = null)
    {
        return $this->addRecipient(EmailAddressType::REPLY_TO, $emailAddress, $displayName);
    }


    public function setFrom(string $emailAddress, string $displayName = null)
    {
        $sender = new EmailAddress();

        $sender->setType(EmailAddressType::FROM);
        $sender->setAddress($emailAddress);
        $sender->setDisplayName($displayName);

        $this->setSender($sender);

        return $this;
    }


    private function addRecipient(string $type, string $emailAddress, string $displayName = null)
    {
        $recipient = new EmailAddress();

        $recipient->setAddress($emailAddress);
        $recipient->setDisplayName($displayName);
        $recipient->setType($type);

        $this->recipients[] = $recipient;

        return $this;
    }

}