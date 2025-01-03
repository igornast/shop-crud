<?php

declare(strict_types=1);


use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\CustomerFixtures;

function createClientAnonymous(ApiTestCase $context, bool $reuse = false): Client
{
    static $client = null;

    if ($reuse && null !== $client) {
        return $client;
    }

    $reflection = new ReflectionMethod($context, 'createClient');
    $reflection->setAccessible(true);
    /** @var Client $client */
    $client = $reflection->invoke($context);
    $client->getKernelBrowser()->insulate();

    return $client;
}


function getTokenForRole(Client $client, string $role): string
{
    static $tokens = [];

    if (isset($tokens[$role])) {
        return $tokens[$role];
    }

    $email = match ($role) {
        'ROLE_ADMIN' => CustomerFixtures::CUSTOMER_ADMIN_EMAIL,
        'ROLE_USER' => CustomerFixtures::CUSTOMER_USER_EMAIL,
        default => throw new InvalidArgumentException(sprintf('Unknown role "%s"', $role)),
    };

    $client->request('POST', '/auth', ['json' => ['email' => $email, 'password' => CustomerFixtures::CUSTOMER_PASSWORD]]);

    $response = $client->getResponse();
    if (200 !== $response->getStatusCode()) {
        throw new RuntimeException(sprintf('Failed to get token for role "%s": %s', $role, $response->getContent()));
    }

    $data = json_decode($response->getContent(), true);

    return $tokens[$role] = $data['token']  ?? throw new RuntimeException('Token not found in response');
}
