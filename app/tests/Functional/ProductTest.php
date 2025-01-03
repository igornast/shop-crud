<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

afterEach(function (): void {
    self::ensureKernelShutdown();
});

it('fetches a collection public', function (): string {
    $response =  makeRequest($this, 'GET', 'api/products');

    $items = json_decode($response->getContent());

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson()
        ->and($items[0])->toHaveKeys(['id', 'name', 'description', 'category', 'price', 'sku']);

    return $items[0]->id;
});

it('fetches a single public', function (string $productId): void {
    $response =  makeRequest($this, 'GET', sprintf('api/products/%s', $productId));

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getContent())->toBeJson();
})->depends('it fetches a collection public');

it('can\'t delete if unauthorized', function (string $productId): void {
    $response =  makeRequest($this, 'DELETE', sprintf('api/products/%s', $productId));

    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
})->depends('it fetches a collection public');

it('can\'t patch if unauthorized', function (string $productId): void {
    $response =  makeRequest($this, 'PATCH', sprintf('api/products/%s', $productId));

    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
})->depends('it fetches a collection public');

it('can\'t patch if role user', function (string $productId): void {
    $response = makeRoleRequest($this, 'PATCH', sprintf('api/products/%s', $productId), 'ROLE_USER');

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
})->depends('it fetches a collection public');

it('can\'t delete if role user', function (string $productId): void {
    $response = makeRoleRequest($this, 'DELETE', sprintf('api/products/%s', $productId), 'ROLE_USER');

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
})->depends('it fetches a collection public');

it('can patch if admin', function (string $productId): void {
    $response = makeRoleRequest(
        $this,
        'PATCH',
        sprintf('api/products/%s', $productId),
        'ROLE_ADMIN',
        [
            'sku' => 'new sku',
            'category' => 'new category',
            'brand' => 'new brand',
            'name' => 'new product name',
            'description' => 'new product description',
            'price' => ['amount' => '999999', 'currency' => 'USD'],
        ]
    );

    $data = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)

    ->and($data['sku'])->toBe('new sku')
    ->and($data['brand'])->toBe('new brand')
    ->and($data['name'])->toBe('new product name')
    ->and($data['description'])->toBe('new product description')
    ->and($data['category'])->toBe('new category')
    ->and($data['price']['amount'])->toBe('999999')
    ->and($data['price']['currency'])->toBe('USD');
})->depends('it fetches a collection public');

it('can delete if admin', function (string $productId): void {
    $response = makeRoleRequest($this, 'DELETE', sprintf('api/products/%s', $productId), 'ROLE_ADMIN');

    expect($response->getStatusCode())->toBe(Response::HTTP_NO_CONTENT);
})->depends(
    'it fetches a collection public',
    'it fetches a single public',
    'it can patch if admin',
    'it can\'t delete if unauthorized',
    'it can\'t delete if role user',
    'it can\'t patch if unauthorized',
    'it can\'t patch if role user',
);
