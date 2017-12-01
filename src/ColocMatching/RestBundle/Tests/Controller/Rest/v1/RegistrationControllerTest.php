<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Event\RegistrationEvent;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends RestTestCase {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;


    protected function setUp() {
        parent::setUp();

        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->client->getContainer()->set("event_dispatcher", $this->eventDispatcher);

        $this->logger = $this->client->getContainer()->get("logger");
    }


    public function testRegisterActionWith201() {
        $this->logger->info("Test registering a new user with status code 201");

        $data = array ("email" => "user@test.fr", "plainPassword" => "password", "firstname" => "User",
            "lastname" => "Test", "type" => UserConstants::TYPE_SEARCH);
        $user = UserMock::createUser(1, $data["email"], $data["plainPassword"], $data["firstname"],
            $data["lastname"], $data["type"]);

        $this->userManager->expects(self::once())->method("create")->with($data, false)->willReturn($user);
        $this->eventDispatcher->expects(self::once())->method("dispatch")
            ->with(RegistrationEvent::REGISTERED_EVENT, new RegistrationEvent($user));

        $this->client->request("POST", "/rest/registrations", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testRegisterActionWith422() {
        $this->logger->info("Test registering a new user with status code 201");

        $data = array ("email" => "user@test.fr", "firstname" => "User",
            "lastname" => "Test", "type" => UserConstants::TYPE_SEARCH);

        $this->userManager->expects(self::once())->method("create")->with($data, false)
            ->willThrowException($this->createMock(InvalidFormException::class));
        $this->eventDispatcher->expects(self::never())->method("dispatch");

        $this->client->request("POST", "/rest/registrations", $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response["code"]);
    }
}