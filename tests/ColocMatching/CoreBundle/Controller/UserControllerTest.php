<?php

namespace Test\ColocMatching\CoreBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends TestCase {

    /**
     * Mocked REST client
     * @var Client
     */
    private static $client;

    /**
     * Authentication token to use in REST calls
     * @var string
     */
    private static $authToken;


    public static function setUpBeforeClass() {
        // create a JWT token to autenticate the user
        self::$authToken = self::createAuthenticatedToken('toto@test.fr', 'password');
    }


    /**
     * Test get users with response 200
     */
    public function testGetAllUsers() {
        /** @var Request */
        $request = new Request('GET', 'users/', [ ]);
        /** @var ResponseInterface */
        $response = self::$client->send($request);
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    /**
     * Test get users' fields with response 200
     */
    public function testGetAllUsersFields() {
        $headers = array ("Content-type" => "application/json");
        $body = [ 'fields' => 'id,email'];
        
        /** @var Request */
        $request = new Request('GET', 'users/', $headers, json_encode($body));
        /** @var ResponseInterface */
        $response = self::$client->send($request);
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    /**
     * Test Post a new user with response 201
     */
    public function testCreateUser() {
        $headers = array ("Content-type" => "application/json");
        $body = array (
            'user' => array ('email' => 'phpunit@test.fr', 'plainPassword' => 'phpunitpwd', 'firstname' => 'phpunit',
                'lastname' => 'test'));
        
        /** @var Request */
        $request = new Request('POST', 'users/', $headers, json_encode($body));
        /** @var ResponseInterface */
        $response = self::$client->send($request);
        
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }


    /**
     * Test Get a user's announcement
     */
    public function testGetUserAnnouncement() {
        $headers = array ("Authorization" => sprintf("Bearer %s", self::$authToken),
            "Content-type" => 'application/json');
        $userId = 1;
        /** @var Request */
        $request = new Request("GET", "users/$userId/announcement", $headers);
        /** @var ResponseInterface */
        $response = self::$client->send($request);
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    private static function createAuthenticatedToken(string $_username, string $_password) {
        self::$client = new Client(array ('base_uri' => 'http://localhost/rest/'));
        
        /** @var array */
        $headers = array ('content-type', 'application/json');
        $body = array ('_username' => $_username, '_password' => $_password);
        /** @var Request */
        $request = new Request('POST', 'auth-tokens/', $headers, json_encode($body));
        
        /** @var ResponseInterface */
        $response = self::$client->send($request);
        
        $data = json_decode($response->getBody(), true);
        
        return $data['token'];
    }

}
