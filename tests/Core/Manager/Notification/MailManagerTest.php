<?php

namespace App\Tests\Core\Manager\Notification;

use App\Core\DTO\User\UserDto;
use App\Core\Manager\Notification\MailManager;
use App\Mail\Entity\Email;
use App\Mail\Service\MailSenderInterface;
use App\Tests\AbstractServiceTest;
use PHPUnit\Framework\MockObject\MockObject;

class MailManagerTest extends AbstractServiceTest
{
    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var MockObject
     */
    private $mailSender;


    protected function setUp()
    {
        parent::setUp();

        $this->mailSender = $this->createMock(MailSenderInterface::class);
        $translator = $this->getService("translator");
        $templateEngine = $this->getService("twig");

        $this->mailManager = new MailManager($this->logger, $this->mailSender, $translator, $templateEngine,
            "test@yopmail.com");
    }


    private function createUser() : UserDto
    {
        $user = new UserDto();
        $user->setEmail("user@yopmail.fr");
        $user->setFirstName("User");
        $user->setLastName("Test");

        return $user;
    }


    /**
     * @test
     */
    public function sendSimpleEmail()
    {
        $user = $this->createUser();
        $this->mailSender->expects(self::once())->method("sendEmail")->with(self::isInstanceOf(Email::class));

        $this->mailManager->sendEmail($user, "Email test", "simple_template.html.twig");
    }


    /**
     * @test
     */
    public function sendEmailWithParameters()
    {
        $user = $this->createUser();
        $this->mailSender->expects(self::once())->method("sendEmail")->with(self::isInstanceOf(Email::class));

        $this->mailManager->sendEmail($user, "mail.subject.registration", "template_with_params.html.twig",
            ["%name%" => "John Smith"], ["name" => "John Smith"]);
    }

}
