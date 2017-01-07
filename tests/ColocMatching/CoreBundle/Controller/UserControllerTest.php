<?php

namespace Test\ColocMatching\CoreBundle\Controller;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends TestCase
{
	
	public function testGetUsers() {
		/** @var Client */
		$client = new Client(array (
			'base_uri' => 'http://coloc-matching.api/rest/'
		));
		
		/** @var ResponseInterface */
		$response = $client->get('users');
		
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}
	
	
	public function testGetUser() {
		/** @var Client */
		$client = new Client(array (
				'base_uri' => 'http://coloc-matching.api/rest/'
		));
		
		$userId = 4;
		
		/** @var ResponseInterface */
		$response = $client->get("users/$userId");
		
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
	}
	
	
	public function testPostUsers() {
		
	}
}
