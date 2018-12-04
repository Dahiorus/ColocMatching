<?php

namespace App\Tests\Mail\Service;

use App\Mail\Entity\Email;
use App\Mail\Entity\EmailAddress;
use App\Mail\Entity\EmailAddressType;
use App\Mail\Service\MailSender;
use App\Tests\AbstractServiceTest;

class MailSenderTest extends AbstractServiceTest
{
    /**
     * @var MailSender
     */
    private $mailSender;

    /**
     * @var CountableMemoryPool
     */
    private $pool;


    protected function setUp()
    {
        parent::setUp();

        $this->pool = new CountableMemoryPool();
        $transport = new \Swift_Transport_SpoolTransport(
            new \Swift_Events_SimpleEventDispatcher(),
            $this->pool
        );
        $this->mailSender = new MailSender($this->logger, new \Swift_Mailer($transport));
    }


    /**
     * @test
     */
    public function sendEmail()
    {
        $email = new Email();
        $email
            ->setSubject("Mail test")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to@yopmail.com", "To Test");

        $this->mailSender->sendEmail($email);

        self::assertEquals(1, $this->pool->count(), "Expected 1 email to be sent");

        $message = $this->pool->getMessages()[0];
        self::assertArrayHasKey($email->getSender()->getAddress(), $message->getFrom(),
            "The sender is not what expected");
        self::assertEquals($email->getSubject(), $message->getSubject(), "The email subject is not what expected");
        self::assertArrayHasKey($email->getRecipients()[0]->getAddress(), $message->getTo(),
            "The email recipient is not what expected");
    }


    /**
     * @test
     */
    public function sendEmailWithMultipleRecipients()
    {
        $email = new Email();
        $email
            ->setSubject("Mail test")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to-1@yopmail.com", "To Test")
            ->addTo("to-2@yopmail.com", "Toto Test")
            ->addTo("to-3@yopmail.com", "Tot Test");

        $this->mailSender->sendEmail($email);

        self::assertEquals(1, $this->pool->count(), "Expected 1 email to be sent");

        $message = $this->pool->getMessages()[0];
        self::assertArrayHasKey($email->getSender()->getAddress(), $message->getFrom(),
            "The sender is not what expected");
        self::assertEquals($email->getSubject(), $message->getSubject(), "The email subject is not what expected");

        foreach ($email->getRecipients() as $recipient)
        {
            self::assertArrayHasKey($recipient->getAddress(), $message->getTo(),
                "Expected $recipient to be one of the mail recipients");
        }
    }


    /**
     * @test
     */
    public function sendEmailWithCc()
    {
        $email = new Email();
        $email
            ->setSubject("Mail test")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to@yopmail.com", "To Test")
            ->addCc("cc@yopmail.com", "Cc Test");

        $this->mailSender->sendEmail($email);

        self::assertEquals(1, $this->pool->count(), "Expected 1 email to be sent");

        $message = $this->pool->getMessages()[0];
        self::assertArrayHasKey("cc@yopmail.com", $message->getCc(), "Expected to find a CC in the mail recipients");
    }


    /**
     * @test
     */
    public function sendEmailWithBcc()
    {
        $email = new Email();
        $email
            ->setSubject("Mail test")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to@yopmail.com", "To Test")
            ->addBcc("bcc@yopmail.com", "Bcc Test");

        $this->mailSender->sendEmail($email);

        self::assertEquals(1, $this->pool->count(), "Expected 1 email to be sent");

        $message = $this->pool->getMessages()[0];
        self::assertArrayHasKey("bcc@yopmail.com", $message->getBcc(), "Expected to find a BCC in the mail recipients");
    }


    /**
     * @test
     */
    public function sendEmailWithReplyTo()
    {
        $email = new Email();
        $email
            ->setSubject("Mail test")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to@yopmail.com", "To Test")
            ->addReplyTo("reply-to@yopmail.com", "Reply Test");

        $this->mailSender->sendEmail($email);

        self::assertEquals(1, $this->pool->count(), "Expected 1 email to be sent");

        $message = $this->pool->getMessages()[0];
        self::assertArrayHasKey("reply-to@yopmail.com", $message->getReplyTo(),
            "Expected to find a Reply-To in the mail recipients");
    }


    /**
     * @test
     */
    public function sendEmailWithFromInRecipients()
    {
        $email = new Email();
        $email
            ->setSubject("Mail test")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to@yopmail.com", "To Test");

        $from = new EmailAddress();
        $from->setType(EmailAddressType::FROM)->setAddress("from-as-recipient@yopmail.com");
        $email->setRecipients(array_merge([$from], $email->getRecipients()));

        $this->mailSender->sendEmail($email);

        self::assertEquals(1, $this->pool->count(), "Expected 1 email to be sent");

        $message = $this->pool->getMessages()[0];
        self::assertArrayNotHasKey($from->getAddress(), $message->getTo(),
            "Unexpected address found in the message recipients");
    }


    /**
     * @test
     */
    public function sendEmails()
    {
        $email1 = new Email();
        $email1
            ->setSubject("Mail test 1")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to@yopmail.com", "To Test");

        $email2 = new Email();
        $email2
            ->setSubject("Mail test 2")
            ->setBody("Hello!")
            ->setContentType("UTF-8")
            ->setFrom("from@yopmail.com", "From Test")
            ->addTo("to-2@yopmail.com", "To2 Test");

        $this->mailSender->sendEmails(array ($email1, $email2));

        self::assertEquals(2, $this->pool->count(), "Expected 2 emails to be sent");
    }
}
