<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }
    
    public function testFailLogin()
    {
        $this->runLoginRequest('', '');
        $this->assertJsonResponse($this->client->getResponse(), 401);
    }
    
    public function testLogin()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::$container->get(EntityManagerInterface::class);
        /** @var UserPasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = static::$container->get(UserPasswordEncoderInterface::class);
        
        $username = 'test';
        $password = 'test';
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($passwordEncoder->encodePassword($user, $password));
        $entityManager->persist($user);
        $entityManager->flush();
        
        $response = $this->runLoginRequest($username, $password);
        $this->assertJsonResponse($this->client->getResponse());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('username', $response);
        $this->assertArrayHasKey('roles', $response);
        $this->assertEquals($username, $response['username']);
        $this->assertIsArray($response['roles']);
        $this->assertTrue(in_array('ROLE_USER', $response['roles']));
        
        $entityManager->remove($user);
        $entityManager->flush();
    }
    
    public function testLogout()
    {
        $this->client->request('POST', '/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => 'test',
            'password' => 'test'
        ]));
        $this->assertResponseIsSuccessful();
    }
}
