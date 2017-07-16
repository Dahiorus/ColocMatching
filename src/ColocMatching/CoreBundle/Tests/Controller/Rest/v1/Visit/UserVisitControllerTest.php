<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Exception\VisitNotFoundException;
use ColocMatching\CoreBundle\Manager\Visit\VisitManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Tests\Controller\Rest\v1\RestTestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Visit\VisitMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserVisitControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $visitManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $visitedClass = User::class;

    /**
     * @var User
     */
    private $authenticatedUser;


    protected function setUp() {
        parent::setUp();

        $this->visitManager = $this->createMock(VisitManager::class);
        $this->client->getContainer()->set("coloc_matching.core.user_visit_manager", $this->visitManager);

        $this->logger = $this->client->getContainer()->get("logger");

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_PROPOSAL);
        $this->setAuthenticatedRequest($this->authenticatedUser);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    public function testGetVisitsActionWith200() {
        $this->logger->info("Test getting visits with status code 200");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(2);
        $visits = VisitMock::createVisitPage($filter, $total, $this->visitedClass);

        $this->visitManager->expects($this->once())->method("list")->with($filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/users/visits", array ("page" => 2));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($visits), $response["rest"]["content"]);
        $this->assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetVisitsActionWith206() {
        $this->logger->info("Test getting visits with status code 206");

        $total = 30;
        $filter = new PageableFilter();
        $visits = VisitMock::createVisitPage($filter, $total, $this->visitedClass);

        $this->visitManager->expects($this->once())->method("list")->with($filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/users/visits");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertCount(count($visits), $response["rest"]["content"]);
    }


    public function testGetUserVisitsActionWith200() {
        $this->logger->info("Test getting visits of one user with status code 200");

        $total = 30;
        $filter = new PageableFilter();
        $filter->setPage(2);
        $user = UserMock::createUser(2, "user2@test.fr", "password", "User2", "Test", UserConstants::TYPE_SEARCH);
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $user);

        $this->userManager->expects($this->once())->method("read")->with($user->getId())->willReturn($user);
        $this->visitManager->expects($this->once())->method("listByVisited")->with($user, $filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/users/2/visits", array ("page" => 2));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertCount(count($visits), $response["rest"]["content"]);
        $this->assertEquals($filter->getSize(), $response["rest"]["size"]);
    }


    public function testGetUserVisitsActionWith206() {
        $this->logger->info("Test getting visits of one user with status code 206");

        $total = 30;
        $filter = new PageableFilter();
        $user = UserMock::createUser(2, "user2@test.fr", "password", "User2", "Test", UserConstants::TYPE_SEARCH);
        $visits = VisitMock::createVisitPageForVisited($filter, $total, $user);

        $this->userManager->expects($this->once())->method("read")->with($user->getId())->willReturn($user);
        $this->visitManager->expects($this->once())->method("listByVisited")->with($user, $filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/users/2/visits");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertCount(count($visits), $response["rest"]["content"]);
    }


    public function testGetUserVisitsActionWith404() {
        $this->logger->info("Test getting visits of one user with status code 404");

        $id = 2;

        $this->userManager->expects($this->once())->method("read")->with($id)
            ->willThrowException(new UserNotFoundException("id", $id));
        $this->visitManager->expects($this->never())->method("listByVisited");
        $this->visitManager->expects($this->never())->method("countAll");

        $this->client->request("GET", "/rest/users/$id/visits");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetUserVisitActionWith200() {
        $this->logger->info("Test getting one visit on one user with status code 200");

        $id = 1;
        $expectedVisit = VisitMock::createVisit(
            $id,
            UserMock::createUser(2, "user2@test.fr", "password", "User2", "Test", UserConstants::TYPE_SEARCH),
            $this->authenticatedUser, new \DateTime());

        $this->visitManager->expects($this->once())->method("read")->with($id)->willReturn($expectedVisit);

        $this->client->request("GET", "/rest/users/visits/$id");
        $response = $this->getResponseContent();
        $visit = $response["rest"]["content"];

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($visit);
        $this->assertEquals($expectedVisit->getId(), $visit["id"]);
    }


    public function testGetUserVisitActionWith404() {
        $this->logger->info("Test getting one visit on one user with status code 404");

        $id = 1;

        $this->visitManager->expects($this->once())->method("read")->with($id)->willThrowException(
            new VisitNotFoundException("id", $id));

        $this->client->request("GET", "/rest/users/visits/$id");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testSearchUsersVisitsWith200() {
        $this->logger->info("Test searching users visits with status code 200");

        $total = 30;
        $filter = new VisitFilter();
        $filter->setPage(2);
        $visits = VisitMock::createVisitPage($filter, $total, $this->visitedClass);

        $this->visitManager->expects($this->once())->method("search")->with($filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", "/rest/users/visits/searches", array ("page" => 2));
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_OK, $response["code"]);
        $this->assertNotNull($response["rest"]);
    }


    public function testSearchUsersVisitsWith206() {
        $this->logger->info("Test searching users visits with status code 206");

        $total = 30;
        $filter = new VisitFilter();
        $visits = VisitMock::createVisitPage($filter, $total, $this->visitedClass);

        $this->visitManager->expects($this->once())->method("search")->with($filter)->willReturn($visits);
        $this->visitManager->expects($this->once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", "/rest/users/visits/searches");
        $response = $this->getResponseContent();

        $this->assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
        $this->assertNotNull($response["rest"]);
    }

}