<?php

use App\State\ProductPatchStateProcessor;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\Entity\Product;
use Money\Money;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\State\ProcessorInterface;

beforeEach(function () {
    $this->persistProcessor = mock(ProcessorInterface::class);
    $this->processor = new ProductPatchStateProcessor($this->persistProcessor);
});

it('returns the original product if no price is provided', function () {
    $product = mock(Product::class);

    $request = new Request([], [], [], [], [], [], json_encode([]));
    $operation = mock(Operation::class);

    $result = $this->processor->process($product, $operation, [], ['request' => $request]);

    expect($result)->toBe($product);
});

it('throws an exception if the request is missing', function () {
    $product = mock(Product::class);
    $operation = mock(Operation::class);

    $this->processor->process($product, $operation, [], []);
})->throws(RuntimeException::class);

it('updates the product price if valid price data is provided', function () {
    $product = mock(Product::class);
    $product->shouldReceive('setPrice')->once()->withArgs(function (Money $money) {
        return '1000' === $money->getAmount() && 'USD' === $money->getCurrency()->getCode();
    });

    $this->persistProcessor->shouldReceive('process')->once()->andReturn($product);

    $request = new Request([], [], [], [], [], [], json_encode([
        'price' => ['amount' => '1000', 'currency' => 'USD'],
    ]));

    $operation = mock(Operation::class);

    $result = $this->processor->process($product, $operation, [], ['request' => $request]);

    expect($result)->toBe($product);
});

it('does not update the product price if amount or currency is missing', function () {
    $product = mock(Product::class);

    $request = new Request([], [], [], [], [], [], json_encode([
        'price' => ['amount' => null, 'currency' => 'USD'],
    ]));

    $operation = mock(Operation::class);

    $result = $this->processor->process($product, $operation, [], ['request' => $request]);

    expect($result)->toBe($product);
});
