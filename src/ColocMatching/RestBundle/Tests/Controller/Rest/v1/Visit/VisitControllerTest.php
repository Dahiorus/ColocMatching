<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\VisitFilterType;
use ColocMatching\CoreBundle\Manager\Visit\VisitManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Visit\VisitMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class VisitControllerTest extends RestTestCase {

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitManager;

    /**
     * @var User
     */
    protected $authenticatedUser;

    /**
     * @var string
     */
    protected $visitableType;

    /**
     * @var string
     */
    protected $managerId;


    protected function setUp() {
        parent::setUp();

        $this->visitManager = $this->createMock(VisitManager::class);
        $this->client->getContainer()->set($this->managerId, $this->visitManager);

        $this->logger = $this->client->getContainer()->get("logger");

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_SEARCH);
        $this->authenticatedUser->setStatus(UserConstants::STATUS_ENABLED);
        $this->authenticatedUser->setRoles(array ("ROLE_API"));
        $this->setAuthenticatedRequest($this->authenticatedUser);
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    private function getVisitableClass(string $type) : string {
        $visitableClass = null;

        switch ($type) {
            case "user":
                $visitableClass = User::class;
                break;
            case "announcement":
                $visitableClass = Announcement::class;
                break;
            case "group":
                $visitableClass = Group::class;
                break;
            default:
                throw new \Exception("Unknown visitable class");
        }

        return $visitableClass;
    }


    public function testGetVisitsActionWith200() {
        $this->logger->info("Test getting visits with status code 200",
            array ("visitableType" => $this->visitableType));

        $filter = new PageableFilter();
        $total = 10;
        $expectedVisits = VisitMock::createVisitPage($filter, $total, $this->getVisitableClass($this->visitableType));

        $this->visitManager->expects(self::once())->method("list")->with($filter)->willReturn($expectedVisits);
        $this->visitManager->expects(self::once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/visits", array ("type" => $this->visitableType));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetVisitsActionWith206() {
        $this->logger->info("Test getting visits with status code 206",
            array ("visitableType" => $this->visitableType));

        $total = 10;
        $filter = new PageableFilter();
        $filter->setSize(5);

        $expectedVisits = VisitMock::createVisitPage($filter, $total, $this->getVisitableClass($this->visitableType));

        $this->visitManager->expects(self::once())->method("list")->with($filter)->willReturn($expectedVisits);
        $this->visitManager->expects(self::once())->method("countAll")->willReturn($total);

        $this->client->request("GET", "/rest/visits", array ("size" => 5, "type" => $this->visitableType));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
    }


    public function testSearchVisitsActionWith200() {
        $this->logger->info("Test searching visits with status code 200");

        $filter = new VisitFilter();
        $total = 10;
        $expectedVisits = VisitMock::createVisitPage($filter, $total, $this->getVisitableClass($this->visitableType));

        $this->visitManager->expects(self::once())->method("search")->with($filter)->willReturn($expectedVisits);
        $this->visitManager->expects(self::once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", sprintf("/rest/visits/searches?type=%s", $this->visitableType));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testSearchVisitsActionWith206() {
        $this->logger->info("Test searching visits with status code 206");

        $filter = new VisitFilter();
        $filter->setSize(5);
        $total = 10;
        $expectedVisits = VisitMock::createVisitPage($filter, $total, $this->getVisitableClass($this->visitableType));

        $this->visitManager->expects(self::once())->method("search")->with($filter)->willReturn($expectedVisits);
        $this->visitManager->expects(self::once())->method("countBy")->with($filter)->willReturn($total);

        $this->client->request("POST", sprintf("/rest/visits/searches?type=%s", $this->visitableType),
            array ("size" => 5));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
    }


    public function testSearchVisitsActionWith422() {
        $this->logger->info("Test searching visits with status code 422");

        $filter = new VisitFilter();

        $this->visitManager->expects(self::once())->method("search")->with($filter)
            ->willThrowException(new InvalidFormException("Exception from test",
                $this->getForm(VisitFilterType::class)->getErrors()));
        $this->visitManager->expects(self::never())->method("countBy");

        $this->client->request("POST", sprintf("/rest/visits/searches?type=%s", $this->visitableType));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }
}
