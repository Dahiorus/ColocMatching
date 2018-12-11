<?php

namespace App\Tests\Core\Manager\Notification;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Manager\Notification\AlertNotifier;
use App\Core\Manager\Notification\MailManager;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\InvitationFilter;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Repository\Filter\Searchable;
use App\Core\Repository\Filter\UserFilter;
use App\Tests\AbstractServiceTest;
use PHPUnit\Framework\MockObject\MockObject;

class AlertNotifierTest extends AbstractServiceTest
{
    /** @var MockObject */
    private $mailManager;

    /** @var AlertNotifier */
    private $notifier;


    protected function setUp()
    {
        parent::setUp();

        $this->mailManager = $this->createMock(MailManager::class);
        $this->notifier = new AlertNotifier($this->logger, $this->mailManager);
    }


    private function createAlert(string $notificationType, Searchable $filter) : AlertDto
    {
        $alert = new AlertDto();

        $alert->setName("alert test")
            ->setNotificationType($notificationType)
            ->setFilter($filter);

        return $alert;
    }


    /**
     * @test
     */
    public function notifyByMail()
    {
        $alert = $this->createAlert(NotificationType::EMAIL, new GroupFilter());

        $this->mailManager->expects(self::once())->method("sendEmail");

        $this->notifier->notify(new UserDto(), new Page(new PageRequest(1, 5), [], 0), $alert);
    }


    /**
     * @test
     */
    public function notifyBySms()
    {
        $alert = $this->createAlert(NotificationType::SMS, new AnnouncementFilter());

        $this->mailManager->expects(self::never())->method("sendEmail");

        $this->notifier->notify(new UserDto(), new Page(new PageRequest(1, 5), [], 0), $alert);
    }


    /**
     * @test
     */
    public function notifyByPush()
    {
        $alert = $this->createAlert(NotificationType::PUSH, new UserFilter());

        $this->mailManager->expects(self::never())->method("sendEmail");

        $this->notifier->notify(new UserDto(), new Page(new PageRequest(1, 5), [], 0), $alert);
    }


    /**
     * @test
     */
    public function notifyWithUnsupportedFilterShouldThrowException()
    {
        $alert = $this->createAlert(NotificationType::EMAIL, new InvitationFilter());

        $this->expectException(\RuntimeException::class);

        $this->notifier->notify(new UserDto(), new Page(new PageRequest(1, 5), [], 0), $alert);
    }


    /**
     * @test
     */
    public function notifyWithUnsupportedNotificationType()
    {
        $alert = $this->createAlert("khkqjskjld", new UserFilter());

        $this->mailManager->expects(self::never())->method("sendEmail");

        $this->notifier->notify(new UserDto(), new Page(new PageRequest(1, 5), [], 0), $alert);
    }

}
