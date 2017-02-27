<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\Client;

class AuthenticationControlerTest extends WebTestCase {

    /**
     * @var Client
     */
    private static $client;

    private $userManager;


    public static function setUpBeforeClass() {
        self::$client = parent::createClient();
        self::$client->setServerParameter("HTTP_HOST", "coloc-matching.api");
    }


    protected function setUp() {
        $this->userManager = self::createMock(UserManager::class);
        self::$client->getKernel()->getContainer()->set("coloc_matching.core.user_manager", $this->userManager);
    }


    public function testPostAuthTokenAction() {
        $this->mockFindByUsername();

        $username = "user@test.fr";
        self::$client->request("POST", "/rest/auth-tokens/",
            array ("_username" => $username, "_password" => "password"), [ ], [ "CONTENT_TYPE" => "application/json"]);

        /** @var Response */
        $response = self::$client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode(),
            sprintf("Expected response status code to be equal to 201, but got %d", $response->getStatusCode()));
        $this->assertNotEmpty($data["token"], "Expected 'token' field to be not empty");
        $this->assertNotEmpty($data["user"], "Expected 'user' field to be not empty");
        $this->assertEquals($username, $data["user"]["username"],
            sprintf("Expected username to be equal to '%s', but got'%s'", $username, $data["user"]["username"]));
    }


    private function mockFindByUsername() {
        $user = new User();

        $user->setEmail("user@test.fr");
        $user->setFirstname("User");
        $user->setLastname("Test");
        $user->setPassword(password_hash("password", PASSWORD_BCRYPT, [ "cost" => 12]));
        $user->setEnabled(true);

        $this->userManager->expects($this->any())->method("findByUsername")->with("user@test.fr")->willReturn($user);
    }

}