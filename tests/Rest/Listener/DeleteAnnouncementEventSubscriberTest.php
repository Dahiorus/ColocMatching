<?php

namespace App\Tests\Rest\Listener;

use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Entity\User\User;
use App\Core\Entity\User\UserType;
use App\Core\Manager\Notification\MailManager;
use App\Core\Mapper\User\ProfilePictureDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Announcement\AnnouncementRepository;
use App\Rest\Event\DeleteAnnouncementEvent;
use App\Rest\Listener\DeleteAnnouncementEventSubscriber;
use App\Tests\AbstractServiceTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class DeleteAnnouncementEventSubscriberTest extends AbstractServiceTest
{
    /** @var MockObject */
    private $mailManager;

    /** @var MockObject */
    private $entityManager;

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var DeleteAnnouncementEventSubscriber */
    private $eventSubscriber;


    protected function setUp()
    {
        parent::setUp();

        $this->mailManager = $this->createMock(MailManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userDtoMapper = new UserDtoMapper($this->entityManager,
            $this->createMock(ProfilePictureDtoMapper::class));

        $this->eventSubscriber = new DeleteAnnouncementEventSubscriber($this->logger, $this->mailManager,
            $this->entityManager, $this->userDtoMapper);
    }


    private function mockAnnouncement(int $id) : Announcement
    {
        $creator = new User("creator@yopmail.com", "password", "Creator", "Test");
        $creator->setId(1);
        $creator->setType(UserType::PROPOSAL);

        $announcement = new Announcement($creator);
        $announcement->setId($id);

        $repository = $this->createMock(AnnouncementRepository::class);
        $this->entityManager->expects(self::exactly(2))
            ->method("getRepository")
            ->with(Announcement::class)
            ->willReturn($repository);
        $repository->expects(self::exactly(2))
            ->method("find")
            ->with($id)
            ->willReturn($announcement);

        return $announcement;
    }


    /**
     * @test
     */
    public function onDeleteEventCreateHistoricEntry()
    {
        $announcement = $this->mockAnnouncement(1);

        $this->entityManager->expects(self::once())
            ->method("persist")
            ->with(HistoricAnnouncement::create($announcement));

        $event = new DeleteAnnouncementEvent($announcement->getId());
        $this->eventSubscriber->onDeleteEvent($event);
    }


    /**
     * @test
     */
    public function onDeleteEventSendMailsToCandidates()
    {
        $announcement = $this->mockAnnouncement(1);
        $announcement->addCandidate(new User("candidate-1@yopmail.com", "password", "Candidate-1", "Test"))
            ->addCandidate(new User("candidate-2@yopmail.com", "password", "Candidate-2", "Test"))
            ->addCandidate(new User("candidate-3@yopmail.com", "password", "Candidate-3", "Test"));

        $this->mailManager->expects(self::exactly($announcement->getCandidates()->count()))->method("sendEmail");

        $event = new DeleteAnnouncementEvent($announcement->getId());
        $this->eventSubscriber->onDeleteEvent($event);
    }

}
