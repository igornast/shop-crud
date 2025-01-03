<?php

declare(strict_types=1);

use App\DataFixtures\CustomerFixtures;
use Symfony\Component\HttpFoundation\Response;

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

it('fetches a single customer as the same user', function (): void {
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

it('can patch a customer as admin', function (string $customerId): string {
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

    return $customerId;
})->depends(
    'it fetches a collection of customers as admin',
    'it fetches a single customer as the same user'
);

it('fails to patch a customer with invalid data', function (string $customerId): void {
    $response = makeRoleRequest(
        $this,
        'PATCH',
        sprintf('api/customers/%s', $customerId),
        'ROLE_ADMIN',
        ['name' => '', 'email' => 'xxx.com']
    );

    $data = json_decode($response->getContent(throw: false), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->getContent(throw: false))->toBeJson()
        ->and($data['violations'])->toHaveCount(3)
        ->and($data['detail'])->toContain(
            'name: This value should not be blank.',
            'name: This value is too short. It should have 2 characters or more.',
            'email: This value is not a valid email address.'
        );
})->depends('it can patch a customer as admin');

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
