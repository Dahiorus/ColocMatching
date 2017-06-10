<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AnnouncementManagerTest extends TestCase {

    /**
     * @var AnnouncementManager
     */
    private $announcementManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $dateFormat = "d/m/Y";


    protected function setUp() {
        $entityClass = "CoreBundle:Announcement\\Announcement";
        $this->announcementRepository = $this->createMock(AnnouncementRepository::class);
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
        $this->objectManager->expects($this->once())->method("getRepository")->with($entityClass)->willReturn(
            $this->announcementRepository);
        $this->logger = self::getContainer()->get("logger");

        $this->announcementManager = new AnnouncementManager($this->objectManager, $entityClass, $this->entityValidator,
            $this->logger);
    }


    protected function tearDown() {
    }


    public function testList() {
        $this->logger->info("Test listing announcements");

        $filter = new PageableFilter();
        $expectedAnnouncement = AnnouncementMock::createAnnouncementArray($filter, 50);
        $this->announcementRepository->expects($this->once())->method("findByPageable")->with($filter)->willReturn(
            $expectedAnnouncement);

        $announcements = $this->announcementManager->list($filter);

        $this->assertNotNull($announcements);
        $this->assertEquals($expectedAnnouncement, $announcements);
    }


    public function testCreateWithSuccess() {
        $this->logger->info("Test creating an announcement");

        $user = UserMock::createUser(1, "user-test@test.fr", "password", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "title" => "Announcement",
            "location" => "Paris 75015",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 513,
            "startDate" => "15/05/2018");
        $expectedAnnouncement = AnnouncementMock::createAnnouncement(1, $user, $data["location"], $data["title"],
            $data["type"], $data["rentPrice"], \DateTime::createFromFormat($this->dateFormat, $data["startDate"]));

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with(new Announcement($user),
            $data, AnnouncementType::class, true)->willReturn($expectedAnnouncement);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedAnnouncement);

        $announcement = $this->announcementManager->create($user, $data);

        $this->assertNotNull($announcement);
        $this->assertEquals($expectedAnnouncement, $announcement);
    }


    public function testCreateWithInvalidData() {
        $this->logger->info("Test creating an announcement with invalid data");

        $user = UserMock::createUser(1, "user-test@test.fr", "password", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "title" => "Announcement",
            "location" => "Paris 75015",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 513);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with(new Announcement($user),
            $data, AnnouncementType::class, true)->willThrowException(
            new InvalidFormDataException("Exception from testCreateWithInvalidData()",
                self::getForm(AnnouncementType::class)->getErrors(true, true)));
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(InvalidFormDataException::class);

        $this->announcementManager->create($user, $data);

        $this->assertNull($user->getAnnouncement());
    }


    public function testCreateWithUnprocessableEntity() {
        $this->logger->info("Test creating an announcement with unprocessable entity");

        $user = UserMock::createUser(1, "user-test@test.fr", "password", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $data = array (
            "title" => "Announcement",
            "location" => "Paris 75015",
            "type" => Announcement::TYPE_RENT,
            "rentPrice" => 513,
            "startDate" => "15/05/2018");
        $user->setAnnouncement(
            AnnouncementMock::createAnnouncement(1, $user, $data["location"], $data["title"], $data["type"],
                $data["rentPrice"], \DateTime::createFromFormat($this->dateFormat, $data["startDate"])));

        $this->entityValidator->expects($this->never())->method("validateEntityForm");
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(UnprocessableEntityHttpException::class);

        $this->announcementManager->create($user, $data);
    }


    public function testReadWithSuccess() {
        $this->logger->info("Test reading an existing announcement with success");

        $expectedAnnouncement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(1, "user-test@test.fr", "password", "Toto", "Toto", UserConstants::TYPE_PROPOSAL),
            "Paris 75006", "Announcement title", Announcement::TYPE_SHARING, 251, new \DateTime());

        $this->announcementRepository->expects($this->once())->method("findById")->with(1)->willReturn(
            $expectedAnnouncement);

        $announcement = $this->announcementManager->read(1);

        $this->assertNotNull($announcement);
        $this->assertEquals($expectedAnnouncement, $announcement);
    }


    public function testReadWithNotFound() {
        $this->logger->info("Test reading announcement with not found");

        $this->announcementRepository->expects($this->once())->method("findById")->with(1)->willReturn(null);
        $this->expectException(AnnouncementNotFoundException::class);

        $this->announcementManager->read(1);
    }


    public function testFullUpdateWithSuccess() {
        $this->logger->info("Test updating (full) an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75020", "Announcement test",
            Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $data = array (
            "title" => "New title",
            "rentPrice" => 843,
            "location" => "Paris 75020",
            "description" => "New description of the announcement",
            "type" => $announcement->getType(),
            "startDate" => $announcement->getStartDate()->format($this->dateFormat),
            "endDate" => "15/09/2017");
        $expectedAnnouncement = AnnouncementMock::createAnnouncement($announcement->getId(), $user, $data["location"],
            $data["title"], $data["type"], $data["rentPrice"],
            \DateTime::createFromFormat($this->dateFormat, $data["startDate"]));
        $expectedAnnouncement->setDescription($data["description"]);
        $expectedAnnouncement->setEndDate(\DateTime::createFromFormat($this->dateFormat, $data["endDate"]));

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($announcement, $data,
            AnnouncementType::class, true)->willReturn($expectedAnnouncement);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedAnnouncement);

        $updatedAnnouncement = $this->announcementManager->update($announcement, $data, true);

        $this->assertNotNull($updatedAnnouncement);
        $this->assertEquals($expectedAnnouncement, $updatedAnnouncement);
    }


    public function testFullUpdateWithInvalidData() {
        $this->logger->info("Test updating (full) an announcement with invalid data");

        $data = array (
            "title" => "New title",
            "rentPrice" => 843,
            "location" => "Paris 75020",
            "description" => "New description of the announcement",
            "endDate" => "15/09/2017");
        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL),
            "Paris 75020", "Announcement test", Announcement::TYPE_SUBLEASE, 638, new \DateTime());

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($announcement, $data,
            AnnouncementType::class, true)->willThrowException(
            new InvalidFormDataException("Exception from testFullUpdateWithInvalidData()",
                self::getForm(AnnouncementType::class)->getErrors(true, true)));
        $this->expectException(InvalidFormDataException::class);
        $this->objectManager->expects($this->never())->method("persist");

        $this->announcementManager->update($announcement, $data, true);
    }


    public function testPartialUpdateWithSuccess() {
        $this->logger->info("Test updating (partial) an announcement with success");

        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75020", "Announcement test",
            Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $data = array ("title" => "New title", "rentPrice" => 1000);
        $expectedAnnouncement = AnnouncementMock::createAnnouncement($announcement->getId(), $user, "Paris 75020",
            $data["title"], $announcement->getType(), $data["rentPrice"], $announcement->getStartDate());

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($announcement, $data,
            AnnouncementType::class, false)->willReturn($expectedAnnouncement);
        $this->objectManager->expects($this->once())->method("persist")->with($expectedAnnouncement);

        $updatedAnnouncement = $this->announcementManager->update($announcement, $data, false);

        $this->assertNotNull($updatedAnnouncement);
        $this->assertEquals($expectedAnnouncement, $updatedAnnouncement);
    }


    public function testPartialUpdateWithInvalidData() {
        $this->logger->info("Test updating (partial) an announcement with invalid data");

        $id = 1;
        $announcement = AnnouncementMock::createAnnouncement($id,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL),
            "Paris 75020", "Announcement test", Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $data = array ("title" => null, "rentPrice" => 1000);

        $this->entityValidator->expects($this->once())->method("validateEntityForm")->with($announcement, $data,
            AnnouncementType::class, false)->willThrowException(
            new InvalidFormDataException("Exception from testPartialUpdateWithInvalidData()",
                self::getForm(AnnouncementType::class)->getErrors(true, true)));
        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(InvalidFormDataException::class);

        $this->announcementManager->update($announcement, $data, false);
    }


    public function testDeleteAnnouncement() {
        $this->logger->info("Test deleting an announcement");

        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL),
            "Paris 75003", "Announcement to delete", Announcement::TYPE_SHARING, 1420, new \DateTime());

        $this->objectManager->expects($this->once())->method("remove")->with($announcement);

        $this->announcementManager->delete($announcement);
    }


    public function testAddNewCandidateWithSuccess() {
        $this->logger->info("Test adding a new candidate to an announcement");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(3, "user2@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_PROPOSAL),
            "Paris 75020", "Announcement test", Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $candidateCount = count($announcement->getCandidates());

        $this->objectManager->expects($this->once())->method("persist")->with($announcement);

        $candidates = $this->announcementManager->addCandidate($announcement, $user);

        $this->assertEquals($candidateCount + 1, count($candidates));
    }


    public function testAddNewCandidateWithUnprocessableEntity() {
        $this->logger->info("Test adding a new candidate to an announcement with unprocessable entity");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_PROPOSAL);
        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(2, "user2@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_PROPOSAL),
            "Paris 75020", "Announcement test", Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $candidateCount = count($announcement->getCandidates());

        $this->objectManager->expects($this->never())->method("persist");
        $this->expectException(UnprocessableEntityHttpException::class);

        $candidates = $this->announcementManager->addCandidate($announcement, $user);

        $this->assertEquals($candidateCount, count($candidates));
    }


    public function testRemoveCandidateWithSuccess() {
        $this->logger->info("Test removing a candidate from an announcement with success");

        $user = UserMock::createUser(1, "user@test.fr", "secret", "Toto", "Toto", UserConstants::TYPE_SEARCH);
        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(15, "user2@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_PROPOSAL),
            "Paris 75020", "Announcement test", Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $announcement->addCandidate($user);
        $candidateCount = count($announcement->getCandidates());

        $this->objectManager->expects($this->once())->method("persist")->with($announcement);

        $this->announcementManager->removeCandidate($announcement, $user->getId());

        $this->assertEquals($candidateCount - 1, count($announcement->getCandidates()));
    }


    public function testRemoveCandidateNotFound() {
        $this->logger->info("Test removing a non existing candidate from an announcement");

        $announcement = AnnouncementMock::createAnnouncement(1,
            UserMock::createUser(2, "user2@test.fr", "secret", "Titi", "Titi", UserConstants::TYPE_PROPOSAL),
            "Paris 75020", "Announcement test", Announcement::TYPE_SUBLEASE, 638, new \DateTime());
        $candidateCount = count($announcement->getCandidates());

        $this->objectManager->expects($this->never())->method("persist");

        $this->announcementManager->removeCandidate($announcement, 1);

        $this->assertEquals($candidateCount, count($announcement->getCandidates()));
    }

}