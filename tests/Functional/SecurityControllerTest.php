<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixture;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    
    protected function setUp() {
        $this->client = static::createClient();
    }
    
    protected function runLoginRequest($username, $password)
    {
        $this->client->request('POST', '/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => $username,
            'password' => $password
        ]));
        return json_decode($this->client->getResponse()->getContent(), true);
    }
    
    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(),
            'Status codes do not match'
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'Content type is different than application/json'
        );
    }
    
    public function testFailLogin()
    {
        $this->runLoginRequest('', '');
        $this->assertJsonResponse($this->client->getResponse(), 401);
    }
    
    public function testLogin()
    {
        $response = $this->runLoginRequest(UserFixture::TEST_USERNAME, UserFixture::TEST_USER_PASSWORD);
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('username', $response);
        $this->assertArrayHasKey('roles', $response);
        $this->assertEquals(UserFixture::TEST_USERNAME, $response['username'], 'Username does not match');
        $this->assertIsArray($response['roles']);
        $this->assertTrue(in_array('ROLE_USER', $response['roles']), 'User\'s role is missing');
    }
    
    public function testLogout()
    {
        $this->client->request('POST', '/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => UserFixture::TEST_USERNAME,
            'password' => UserFixture::TEST_USER_PASSWORD
        ]));
        $this->assertResponseIsSuccessful('Logout request failed');
    }
}
