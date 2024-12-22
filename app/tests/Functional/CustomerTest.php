<?php

declare(strict_types=1);

use App\DataFixtures\CustomerFixtures;
use Symfony\Component\HttpFoundation\Response;

beforeAll(function (): void {
    resetDatabase();
});

afterEach(function (): void {
    self::ensureKernelShutdown();
});

it('fetches a collection of customers as admin', function (): string {
    $response = makeRoleRequest($this, 'GET', 'api/customers', 'ROLE_ADMIN');

    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toBeArray()
        ->and($data[0])->toHaveKeys(['id', 'name', 'email', 'roles']);

    return $data[0]['id'];
});

it('can\'t fetch a collection of customers as user', function (): void {
    $response = makeRoleRequest($this, 'GET', 'api/customers', 'ROLE_USER');

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
});

it('fetches a single customer as admin', function (string $customerId): void {
    $response = makeRoleRequest($this, 'GET', sprintf('api/customers/%s', $customerId), 'ROLE_ADMIN');

    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toHaveKeys(['id', 'name', 'email', 'roles']);
})->depends('it fetches a collection of customers as admin');

it('fetches a single customer as the same user', function (string $customerId): void {
    $response = makeRoleRequest($this, 'GET', 'api/customers', 'ROLE_ADMIN');

    $customersCollection = collect(json_decode($response->getContent(), true));
    $sameCustomerData = $customersCollection->firstWhere('email', CustomerFixtures::CUSTOMER_USER_EMAIL);

    $response = makeRoleRequest($this, 'GET', sprintf('api/customers/%s', $sameCustomerData['id']), 'ROLE_USER');
    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toHaveKeys(['id', 'name', 'email', 'roles'])
        ->and($data['id'])->toBe($sameCustomerData['id'])
        ->and($data['email'])->toBe($sameCustomerData['email']);
})->depends('it fetches a collection of customers as admin');

it('can patch a customer as admin', function (string $customerId): void {
    $response = makeRoleRequest(
        $this,
        'PATCH',
        sprintf('api/customers/%s', $customerId),
        'ROLE_ADMIN',
        ['name' => 'Updated Name', 'email' => 'updated@example.com']
    );

    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data['name'])->toBe('Updated Name')
        ->and($data['email'])->toBe('updated@example.com');
})->depends(
    'it fetches a collection of customers as admin',
    'it fetches a single customer as the same user'
);

it('can\'t fetch another customer as user', function (string $customerId): void {
    $response = makeRoleRequest($this, 'GET', sprintf('api/customers/%s', $customerId), 'ROLE_USER');

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
})->depends(
    'it fetches a collection of customers as admin',
    'it fetches a single customer as the same user'
);

it('can\'t update a customer as user', function (string $customerId): void {
    $response = makeRoleRequest(
        $this,
        'PATCH',
        sprintf('api/customers/%s', $customerId),
        'ROLE_USER',
        ['name' => 'Unauthorized Name']
    );

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
})->depends(
    'it fetches a collection of customers as admin',
    'it fetches a single customer as the same user'
);

it('can\'t delete a customer as user', function (string $customerId): void {
    $response = makeRoleRequest($this, 'DELETE', sprintf('api/customers/%s', $customerId), 'ROLE_USER');

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
})->depends(
    'it fetches a collection of customers as admin',
    'it fetches a single customer as the same user'
);
