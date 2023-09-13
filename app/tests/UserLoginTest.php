<?php

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\UserRepository;

class UserLoginTest extends ApiTestCase
{
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->userRepository = $kernel->getContainer()->get(UserRepository::class);

        $this->userRepository->deleteAllUsers();

        static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);
    }

    public function tearDown(): void
    {
        $this->userRepository->deleteAllUsers();
    }

    public function testUserLogin(): void
    {
        $response = static::createClient()->request('POST', '/login', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertArrayHasKey('token', $response->toArray());
    }

    public function testUserLoginWithWrongPassword(): void
    {
        $response = static::createClient()->request('POST', '/login', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123',
            ]
        ]);

        $responseBody = $response->toArray(false);
        $this->assertResponseStatusCodeSame(401);
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertArrayHasKey('password', $responseBody['errors']);
        $this->assertSame('Incorrect password - please try again.', $responseBody['errors']['password']);
    }

    public function testUserLoginWithUnknownEmail(): void
    {
        $email = 'bob@test.com';

        $response = static::createClient()->request('POST', '/login', [
            'json' => [
                'email' => $email,
                'password' => 'test123456',
            ]
        ]);

        $responseBody = $response->toArray(false);
        $this->assertResponseStatusCodeSame(404);
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertArrayHasKey('email', $responseBody['errors']);
        $this->assertSame(sprintf('User with email (%s) does not exist.', $email), $responseBody['errors']['email']);
    }
}