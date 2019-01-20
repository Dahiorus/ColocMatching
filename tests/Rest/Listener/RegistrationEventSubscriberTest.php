<?php

namespace App\Tests\Rest\Listener;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\InvalidParameterException;
use App\Core\Exception\RegistrationException;
use App\Core\Manager\Notification\MailManager;
use App\Core\Manager\User\UserTokenDtoManager;
use App\Rest\Event\RegistrationEvent;
use App\Rest\Listener\RegistrationEventSubscriber;
use App\Tests\AbstractServiceTest;
use PHPUnit\Framework\MockObject\MockObject;

class RegistrationEventSubscriberTest extends AbstractServiceTest
{
    /**
     * @var MockObject
     */
    private $userTokenManager;

    /**
     * @var MockObject
     */
    private $mailManager;

    /**
     * @var RegistrationEventSubscriber
     */
    private $eventSubscriber;


    protected function setUp()
    {
        parent::setUp();

        $this->mailManager = $this->createMock(MailManager::class);
        $this->userTokenManager = $this->createMock(UserTokenDtoManager::class);

        $this->eventSubscriber = new RegistrationEventSubscriber($this->logger, $this->userTokenManager,
            $this->mailManager);
    }


    private function buildEvent()
    {
        $user = new UserDto();
        $user->setEmail("user@yopmail.fr");

        return new RegistrationEvent($user);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendConfirmationEmail()
    {
        $event = $this->buildEvent();

        $userToken = new UserTokenDto();
        $userToken->setToken("dkfqlsdkfmqsdkjqlkdfjqdfhqz");

        $this->userTokenManager->expects(self::once())
            ->method("createOrUpdate")
            ->with($event->getUser(), UserToken::REGISTRATION_CONFIRMATION,
                self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturn($userToken);
        $this->mailManager->expects(self::once())
            ->method("sendEmail");

        $this->eventSubscriber->sendConfirmationEmail($event);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function createRegistrationTokenWithErrorShouldThrowException()
    {
        $event = $this->buildEvent();

        $this->userTokenManager->expects(self::once())
            ->method("createOrUpdate")
            ->with($event->getUser(), UserToken::REGISTRATION_CONFIRMATION,
                self::isInstanceOf(\DateTimeImmutable::class))
            ->willThrowException(new InvalidParameterException("reason"));
        $this->mailManager->expects(self::never())
            ->method("sendEmail");

        $this->expectException(RegistrationException::class);

        $this->eventSubscriber->sendConfirmationEmail($event);
    }
}
