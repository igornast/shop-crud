<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

function makeRoleRequest(WebTestCase $testCase, string $method, string $url, string $role, array $parameters = []): Response
{
    $contentType = match ($method) {
        'PATCH' => 'application/merge-patch+json',
        default => 'application/json',
    };

    $client = createClientAnonymous($testCase, true);
    $client->request(
        method: $method,
        uri: $url,
        server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', getTokenForRole($client, $role)),
            'CONTENT_TYPE' => $contentType,
            'HTTP_ACCEPT' => 'application/json',
        ],
        content: (!empty($parameters) ? json_encode($parameters) : null)
    );

    return $client->getResponse();
}
