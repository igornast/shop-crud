<?php


use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Response;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

uses(ApiTestCase::class)->in('Functional');

function makeRoleRequest(ApiTestCase $testCase, string $method, string $url, string $role, array $parameters = []): Response
{
    $contentType = match ($method) {
        'PATCH' => 'application/merge-patch+json',
        default => 'application/json',
    };

    $client = createClientAnonymous($testCase, true);
    $client->request(
        method: $method,
        url: $url,
        options: [
            'headers' => [
                'Content-Type' => $contentType,
                'Accept' => 'application/json',
            ],
            'auth_bearer' => getTokenForRole($client, $role),
            'json' => $parameters,
        ],
    );

    return $client->getResponse();
}

function makeRequest(ApiTestCase $testCase, string $method, string $url): Response
{
    $client = createClientAnonymous($testCase);
    $client->request(
        method: $method,
        url: $url,
        options: [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ],
    );

    return $client->getResponse();
}
