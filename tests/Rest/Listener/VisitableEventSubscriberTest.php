<?php

namespace App\Tests\Rest\Listener;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\User\UserDto;
use App\Core\DTO\Visit\VisitDto;
use App\Core\Manager\Visit\VisitDtoManagerInterface;
use App\Core\Service\RoleService;
use App\Rest\Event\VisitEvent;
use App\Rest\Listener\VisitableEventSubscriber;
use App\Tests\AbstractServiceTest;
use PHPUnit\Framework\MockObject\MockObject;

class VisitableEventSubscriberTest extends AbstractServiceTest
{
    /**
     * @var MockObject
     */
    private $visitManager;

    /**
     * @var MockObject
     */
    private $roleService;

    /**
     * @var VisitableEventSubscriber
     */
    private $eventSubscriber;


    protected function setUp()
    {
        parent::setUp();

        $this->visitManager = $this->createMock(VisitDtoManagerInterface::class);
        $this->roleService = $this->createMock(RoleService::class);

        $this->eventSubscriber = new VisitableEventSubscriber($this->logger, $this->visitManager, $this->roleService);
    }


    private function createVisitor(int $id) : UserDto
    {
        $user = new UserDto();
        $user->setId($id);

        return $user;
    }


    /**
     * @test
     * @throws \Exception
     */
    public function generateVisit()
    {
        $visitor = $this->createVisitor(1);
        $visited = new AnnouncementDto();
        $visited->setId(1)
            ->setCreatorId(2);
        $event = new VisitEvent($visited, $visitor);

        $this->visitManager->expects(self::once())->method("countByFilter")->willReturn(0);
        $this->visitManager->expects(self::once())->method("create")->willReturn(VisitDto::create($visitor, $visited));

        $this->eventSubscriber->generateVisit($event);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function spamVisits()
    {
        $visitor = $this->createVisitor(1);
        $visited = new GroupDto();
        $visited->setId(1)
            ->setCreatorId(2);
        $event = new VisitEvent($visited, $visitor);

        $this->visitManager->expects(self::once())->method("countByFilter")->willReturn(1);
        $this->visitManager->expects(self::never())->method("create");

        $this->eventSubscriber->generateVisit($event);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function skipVisit()
    {
        $visitor = $this->createVisitor(1);
        $visited = new GroupDto();
        $visited->setId(1)
            ->setCreatorId($visitor->getId());
        $event = new VisitEvent($visited, $visitor);

        $this->visitManager->expects(self::never())->method("countByFilter");
        $this->visitManager->expects(self::never())->method("create");

        $this->eventSubscriber->generateVisit($event);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function skipVisitAsAdmin()
    {
        $visitor = $this->createVisitor(1);
        $visited = new GroupDto();
        $visited->setId(1)
            ->setCreatorId(2);
        $event = new VisitEvent($visited, $visitor);

        $this->roleService->expects(self::once())->method("isGranted")->with("ROLE_ADMIN", $visitor)->willReturn(true);
        $this->visitManager->expects(self::never())->method("create");

        $this->eventSubscriber->generateVisit($event);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function skipVisitAsVisitingSelf()
    {
        $visitor = $this->createVisitor(1);
        $event = new VisitEvent($visitor, $visitor);

        $this->visitManager->expects(self::never())->method("create");

        $this->eventSubscriber->generateVisit($event);
    }

}
