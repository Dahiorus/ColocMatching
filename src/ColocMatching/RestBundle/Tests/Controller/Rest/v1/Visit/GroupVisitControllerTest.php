<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Manager\Group\GroupManager;
use ColocMatching\CoreBundle\Manager\Visit\VisitManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Visit\VisitMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class GroupVisitControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $visitManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->visitManager = $this->createMock(VisitManager::class);
        $this->client->getContainer()->set("coloc_matching.core.group_visit_manager", $this->visitManager);

        $this->groupManager = $this->createMock(GroupManager::class);
        $this->client->getContainer()->set("coloc_matching.core.group_manager", $this->groupManager);

        $this->logger = $this->client->getContainer()->get("logger");

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL);
        $this->setAuthenticatedRequest($this->authenticatedUser);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testGetVisitsActionWith200() {
        $this->logger->info("Test getting visits of one group with status code 200");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(2);
        $group = GroupMock::createGroup(1, $this->authenticatedUser, "Group in test", null);
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $group);

        $this->groupManager->expects($this->once())->method("read")->with($group->getId())->willReturn($group);
        $this->visitManager->expects($this->once())->method("listByVisited")->with($group,
            $filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countByVisited")->willReturn($total);

        $this->client->request("GET", "/rest/groups/1/visits", array ("page" => 2));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($visits), $response["rest"]["content"]);
        $this->assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetVisitsActionWith206() {
        $this->logger->info("Test getting visits of one group with status code 206");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(1);
        $group = GroupMock::createGroup(1, $this->authenticatedUser, "Group in test", null);
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $group);

        $this->groupManager->expects($this->once())->method("read")->with($group->getId())->willReturn($group);
        $this->visitManager->expects($this->once())->method("listByVisited")->with($group,
            $filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countByVisited")->willReturn($total);

        $this->client->request("GET", "/rest/groups/1/visits");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertCount(count($visits), $response["rest"]["content"]);
    }


    public function testGetVisitsActionWith404() {
        $this->logger->info("Test getting visits of one group with status code 404");

        $this->groupManager->expects($this->once())->method("read")->with(1)
            ->willThrowException(new GroupNotFoundException("id", 1));
        $this->visitManager->expects($this->never())->method("listByVisited");
        $this->visitManager->expects($this->never())->method("countByVisited");

        $this->client->request("GET", "/rest/groups/1/visits");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetGroupVisitActionWith200() {
        $this->logger->info("Test getting one visit on one group with status code 200");

        $id = 1;
        $expectedVisit = VisitMock::createVisit($id,
            GroupMock::createGroup(1, $this->authenticatedUser, "Group in test", null), UserMock::createUser(5,
                "visitor@test.fr", "password", "Visitor", "Test", UserConstants::TYPE_PROPOSAL), new \DateTime());

        $this->groupManager->expects(self::once())->method("read")->with(1)->willReturn($expectedVisit->getVisited());
        $this->visitManager->expects($this->once())->method("read")->with($id)->willReturn($expectedVisit);

        $this->client->request("GET", "/rest/groups/1/visits/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetGroupVisitActionWith404() {
        $this->logger->info("Test getting one visit on one group with status code 404");

        $id = 1;
        $group = GroupMock::createGroup(1, $this->authenticatedUser, "Group in test", null);

        $this->groupManager->expects(self::once())->method("read")->with(1)->willReturn($group);
        $this->visitManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new VisitNotFoundException("id", $id));

        $this->client->request("GET", "/rest/groups/1/visits/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testSearchGroupsVisitsWith200() {
        $this->logger->info("Test searching groups visits with status code 200");

        $total = 30;
        $filter = new VisitFilter();
        $filter->setPage(2);
        $filter->setVisitedId(1);
        $group = GroupMock::createGroup(1, $this->authenticatedUser, "Group in test", null);

        $visits = VisitMock::createVisitPageForVisited($filter, $total, $group);

        $this->groupManager->expects(self::once())->method("read")->with(1)->willReturn($group);
        $this->visitManager->expects($this->once())->method("search")->with($filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", "/rest/groups/1/visits/searches", array ("page" => 2));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($response["rest"]);
    }


    public function testSearchGroupsVisitsWith206() {
        $this->logger->info("Test searching groups visits with status code 206");

        $total = 30;
        $filter = new VisitFilter();
        $filter->setVisitedId(1);
        $group = GroupMock::createGroup(1, $this->authenticatedUser, "Group in test", null);
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $group);

        $this->groupManager->expects(self::once())->method("read")->with(1)->willReturn($group);
        $this->visitManager->expects($this->once())->method("search")->with($filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", "/rest/groups/1/visits/searches");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertNotNull($response["rest"]);
    }

}