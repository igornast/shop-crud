<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

afterEach(function (): void {
    self::ensureKernelShutdown();
});

it('fetches a collection if admin', function (): string {
    $response = makeRoleRequest($this, 'GET', 'api/orders', 'ROLE_ADMIN');

    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toBeArray()
        ->and($data[0])->toHaveKeys(['id', 'customer', 'items']);

    return $data[0]['id'];
});

it('fetches a single if admin', function (string $orderId): void {
    $response = makeRoleRequest($this, 'GET', sprintf('api/orders/%s', $orderId), 'ROLE_ADMIN');

    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toHaveKeys(['id', 'customer', 'items']);
})->depends('it fetches a collection if admin');

it('can\'t fetch a collection if public', function (): void {
    $response =  makeRequest($this, 'GET', 'api/orders');

    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
});

it('can\'t fetch single if public', function (string $orderId): void {
    $response =  makeRequest($this, 'GET', sprintf('api/orders/%s', $orderId));

    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
})->depends('it fetches a collection if admin');


it('creates an order', function (): string {
    $productResponse =  makeRequest($this, 'GET', 'api/products');
    $productData = json_decode($productResponse->getContent(), true);

    $response = makeRoleRequest(
        $this,
        'POST',
        'api/orders',
        'ROLE_USER',
        ['items' => [
            sprintf('/api/products/%s', $productData[0]['id']),
            sprintf('/api/products/%s', $productData[1]['id']),
            sprintf('/api/products/%s', $productData[2]['id']),
        ]]
    );
    $data = json_decode($response->getContent(), true);
    $itemsCollection = collect($data['items']);

    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toHaveKeys(['id', 'customer', 'items'])
        ->and($data['customer']['email'])->toBe('user@example.com')
        ->and($itemsCollection->count())->toBe(3)
        ->and($itemsCollection->first())->toHaveKeys(['id', 'sku', 'name', 'brand', 'price'])
        ->and($itemsCollection->contains('id', $productData[0]['id']))->toBeTrue()
        ->and($itemsCollection->contains('id', $productData[1]['id']))->toBeTrue()
        ->and($itemsCollection->contains('id', $productData[2]['id']))->toBeTrue();

    return $data['id'];
});

it('can\'t delete the order if not admin', function (string $orderId) {
    $response = makeRoleRequest($this, 'DELETE', sprintf('api/orders/%s', $orderId), 'ROLE_USER');

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
})->depends('it creates an order');

it('can patch an order if admin', function (string $orderId): void {
    $productResponse =  makeRequest($this, 'GET', 'api/products');

    $productData = json_decode($productResponse->getContent(), true);
    $productCollection = collect($productData);
    $productCollection = $productCollection->random(3);
    $productData = $productCollection->all();

    $response = makeRoleRequest(
        $this,
        'PATCH',
        sprintf('api/orders/%s', $orderId),
        'ROLE_ADMIN',
        ['items' => [
            sprintf('/api/products/%s', $productData[0]['id']),
            sprintf('/api/products/%s', $productData[1]['id']),
            sprintf('/api/products/%s', $productData[2]['id']),
        ]]
    );
    $data = json_decode($response->getContent(), true);
    $itemsCollection = collect($data['items']);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($data)->toHaveKeys(['id', 'customer', 'items'])
        ->and($data['customer']['email'])->toBe('user@example.com')
        ->and($itemsCollection->count())->toBe(3)
        ->and($itemsCollection->first())->toHaveKeys(['id', 'sku', 'name', 'brand', 'price'])
        ->and($itemsCollection->contains('id', $productData[0]['id']))->toBeTrue()
        ->and($itemsCollection->contains('id', $productData[1]['id']))->toBeTrue()
        ->and($itemsCollection->contains('id', $productData[2]['id']))->toBeTrue();
})->depends(
    'it creates an order',
);


it('can delete an order if admin', function (string $orderId): void {
    $response = makeRoleRequest($this, 'DELETE', sprintf('api/orders/%s', $orderId), 'ROLE_ADMIN');

    expect($response->getStatusCode())->toBe(Response::HTTP_NO_CONTENT);
})->depends(
    'it creates an order',
    'it can\'t delete the order if not admin',
    'it can patch an order if admin',
);
