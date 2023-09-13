<?php

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;

class UserRegistrationTest extends ApiTestCase
{
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->userRepository = $kernel->getContainer()->get(UserRepository::class);

        $this->userRepository->deleteAllUsers();
    }

    public function testValidUserRegistration(): void
    {
        $response = static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertArrayHasKey('token', $response->toArray());

        $user = $this->userRepository->findOneBy([
            'email' => 'test@test.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserRegistrationWithSameEmail(): void
    {
        static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);

        $response = static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);

        $responseBody = $response->toArray(false);

        $this->assertResponseStatusCodeSame(400);
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertArrayHasKey('email', $responseBody['errors']);
        $this->assertSame('There is already an account with this email', $responseBody['errors']['email']);
    }

    public function testUserRegistrationWithInvalidPassword(): void
    {
        $response = static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test@test.com',
                'password' => '123',
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);

        $responseBody = $response->toArray(false);
        $this->assertResponseStatusCodeSame(400);
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertArrayHasKey('password', $responseBody['errors']);
        $this->assertSame('Your password should be at least 6 characters.', $responseBody['errors']['password']);
    }

    public function testUserRegistrationWithInvalidEmail(): void
    {
        $response = static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test',
                'password' => 'password123',
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);

        $responseBody = $response->toArray(false);
        $this->assertResponseStatusCodeSame(400);
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertArrayHasKey('email', $responseBody['errors']);
        $this->assertSame('This value is not a valid email format.', $responseBody['errors']['email']);
    }

    public function testUserRegistrationAndLogin(): void
    {
        $response = static::createClient()->request('POST', '/register', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertArrayHasKey('token', $response->toArray());

        $response = static::createClient()->request('POST', '/login', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test123456',
            ]
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertArrayHasKey('token', $response->toArray());
    }
}