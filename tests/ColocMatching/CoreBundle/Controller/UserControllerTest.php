<?php

namespace Test\ColocMatching\CoreBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends TestCase
{
	/**
	 * Mocked REST client
	 * @var Client
	 */
	private $client;
	
	/**
	 * Authentication token to use in REST calls
	 * @var string
	 */
	private $authToken;
	
	
	public function setUp() {
		$this->client = new Client(array (
			'base_uri' => 'http://coloc-matching.api/rest/'));
		
		$this->authToken = $this->createAuthenticatedToken('john.doe@test.fr', 'password');
	}
	
	
	/**
	 * Test get users with response 200
	 */
	public function testGetAllUsers() {
		/** @var Request */
		$request = new Request('GET', 'users/', ['Authorization' => "Bearer $this->authToken"]);
		/** @var ResponseInterface */
		$response = $this->client->send($request);
		
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}
	
	
	/**
	 * Test get users' fields with response 200
	 */
	public function testGetAllUsersFields() {
		$headers = array (
			'Authorization' => "Bearer $this->authToken",
			'Content-type' => 'application/json');
		$body = ['fields' => 'id,email'];
		
		/** @var Request */
		$request = new Request('GET', 'users/', $headers, json_encode($body));
		/** @var ResponseInterface */
		$response = $this->client->send($request);
		
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}
	
	
	/**
	 * Test Post a new user with response 201
	 */
	public function testPostUser() {
		$headers = array (
				'Authorization' => "Bearer $this->authToken",
				'Content-type' => 'application/json');
		$body = array (
			'user' => array (
				'email' => 'phpunit@test.fr',
				'plainPassword' => 'phpunitpwd',
				'firstname' => 'phpunit',
				'lastname' => 'test'));
		
		/** @var Request */
		$request = new Request('POST', 'users/', $headers, json_encode($body));
		/** @var ResponseInterface */
		$response = $this->client->send($request);
		
		$this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
	}
	
	
	public function testGetUserAnnouncement() {
		$headers = array (
				'Authorization' => "Bearer $this->authToken",
				'Content-type' => 'application/json');
		$userId = 10;
		/** @var Request */
		$request = new Request("GET", "users/$userId/announcement", $headers);
		/** @var ResponseInterface */
		$response = $this->client->send($request);
		
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}
	
	
	private function createAuthenticatedToken(string $_username, string $_password) {
		/** @var array */
		$headers = array ('content-type', 'application/json');
		$body = array (
			'_username' => $_username,
			'_password' => $_password);
		/** @var Request */
		$request = new Request('POST', 'auth-tokens/', $headers, json_encode($body));
		
		/** @var ResponseInterface */
		$response = $this->client->send($request);
		
		$data = json_decode($response->getBody(), true);
		
		return $data['token'];
	}
}
