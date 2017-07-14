<?php
/**
 * Created by PhpStorm.
 * User: Dahiorus
 * Date: 06/07/2017
 * Time: 22:23
 */

namespace ColocMatching\CoreBundle\Tests\Manager\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\Visit\AnnouncementVisit;
use ColocMatching\CoreBundle\Entity\Visit\GroupVisit;
use ColocMatching\CoreBundle\Entity\Visit\UserVisit;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Manager\Visit\VisitManager;
use ColocMatching\CoreBundle\Manager\Visit\VisitManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Repository\Visit\AnnouncementVisitRepository;
use ColocMatching\CoreBundle\Repository\Visit\GroupVisitRepository;
use ColocMatching\CoreBundle\Repository\Visit\UserVisitRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Visit\VisitMock;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

abstract class VisitManagerTest extends TestCase {

    /**
     * @var VisitManagerInterface
     */
    protected $visitManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $visitedClass;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    protected function setUp() {
        $visitClass = $this->getVisitClass($this->visitedClass);
        $this->visitRepository = $this->createMock($this->getRepositoryClass($this->visitedClass));
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->objectManager->expects($this->once())->method("getRepository")->with($visitClass)
            ->willReturn($this->visitRepository);
        $this->logger = self::getContainer()->get("logger");

        $this->visitManager = new VisitManager($this->objectManager, $visitClass, $this->logger);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    private function getRepositoryClass(string $visitedClass) : string {
        $repositoryClass = null;

        switch ($visitedClass) {
            case Announcement::class:
                $repositoryClass = AnnouncementVisitRepository::class;
                break;
            case Group::class:
                $repositoryClass = GroupVisitRepository::class;
                break;
            case User::class:
                $repositoryClass = UserVisitRepository::class;
                break;
            default:
                throw new \Exception("Unknown visited class");
        }

        return $repositoryClass;
    }


    private function getVisitClass(string $visitedClass) {
        $visitClass = null;

        switch ($visitedClass) {
            case Announcement::class:
                $visitClass = AnnouncementVisit::class;
                break;
            case Group::class:
                $visitClass = GroupVisit::class;
                break;
            case User::class:
                $visitClass = UserVisit::class;
                break;
            default:
                throw new \Exception("Unknown visited class");
        }

        return $visitClass;
    }


    private function createVisited() : Visitable {
        $visited = null;
        $user = UserMock::createUser(1, "proposal@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL);

        switch ($this->visitedClass) {
            case Announcement::class:
                $visited = AnnouncementMock::createAnnouncement(1, $user, "Paris 75004", "Announcement test",
                    Announcement::TYPE_SHARING, 1340, new \DateTime());
                break;
            case Group::class:
                $visited = GroupMock::createGroup(1, $user, "Group test", "Description of group");
                break;
            case User::class:
                $visited = $user;
                break;
            default:
                throw new \Exception("Unknown visited class");
        }

        return $visited;
    }


    public function testList() {
        $this->logger->info("Test listing visits", array ("visitable class" => $this->visitedClass));

        $filter = new PageableFilter();
        $expectedVisits = VisitMock::createVisitPage($filter, 30, $this->visitedClass);

        $this->visitRepository->expects($this->once())->method("findByPageable")->with($filter)->willReturn($expectedVisits);

        $visits = $this->visitManager->list($filter);

        $this->assertNotNull($visits);
        $this->assertEquals($expectedVisits, $visits);
    }


    public function testListByVisited() {
        $this->logger->info("Test listing visits by visited", array ("visitable class" => $this->visitedClass));

        $filter = new PageableFilter();
        $visited = $this->createVisited();
        $expectedVisits = VisitMock::createVisitPage($filter, 50, $this->visitedClass, $visited->getId());

        $this->visitRepository->expects($this->once())->method("findByVisited")->with($visited, $filter)->willReturn($expectedVisits);

        $visits = $this->visitManager->listByVisited($visited, $filter);

        $this->assertNotNull($visits);
        $this->assertEquals($expectedVisits, $visits);
    }


    public function testListByVisitor() {
        $this->logger->info("Test listing visits by visitor", array ("visitable class" => $this->visitedClass));

        $filter = new PageableFilter();
        $visitor = UserMock::createUser(1, "visitor@test.fr", "password", "User", "Test",
            UserConstants::TYPE_SEARCH);
        $expectedVisits = VisitMock::createVisitPage($filter, 50, $this->visitedClass, null, $visitor);

        $this->visitRepository->expects($this->once())->method("findByVisitor")->with($visitor, $filter)->willReturn($expectedVisits);

        $visits = $this->visitManager->listByVisitor($visitor, $filter);

        $this->assertNotNull($visits);
        $this->assertEquals($expectedVisits, $visits);
    }


    public function testCreate() {
        $this->logger->info("Test creating a visit");

        $visited = $this->createVisited();
        $visitor = UserMock::createUser(1, "visitor@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL);

        $this->objectManager->expects($this->once())->method("persist");

        $visit = $this->visitManager->create($visited, $visitor);

        $this->assertNotNull($visit);
        $this->assertEquals($visitor, $visit->getVisitor());
        $this->assertEquals($visited, $visit->getVisited());
    }


    public function testReadWithSuccess() {
        $this->logger->info("Test reading a visit");

        $id = 1;
        $expectedVisit = VisitMock::createVisit($id, $visited = $this->createVisited(), UserMock::createUser(1, "visitor@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL), new \DateTime());

        $this->visitRepository->expects($this->once())->method("findById")->with($id)->willReturn($expectedVisit);

        $visit = $this->visitManager->read($id);

        $this->assertNotNull($visit);
        $this->assertEquals($expectedVisit, $visit);
    }


    public function testReadWithFailure() {
        $this->logger->info("Test reading a visit");

        $id = 1;

        $this->visitRepository->expects($this->once())->method("findById")->with($id)->willReturn(null);
        $this->expectException(VisitNotFoundException::class);

        $this->visitManager->read($id);
    }


    public function testSearch() {
        $this->logger->info("Test searching visits", array ("visitable class" => $this->visitedClass));

        $filter = new VisitFilter();
        $expectedVisits = VisitMock::createVisitPage($filter, 30, $this->visitedClass);

        $this->visitRepository->expects($this->once())->method("findByFilter")->with($filter)->willReturn
        ($expectedVisits);

        $visits = $this->visitManager->search($filter);

        $this->assertNotNull($visits);
        $this->assertEquals($expectedVisits, $visits);
    }
}
