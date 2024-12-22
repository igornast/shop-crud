<?php

declare(strict_types=1);

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Doctrine\Entity\Customer;
use App\Doctrine\Entity\Order;
use App\State\OrderPostProcessor;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

beforeEach(function () {
    $this->persistProcessor = mock(ProcessorInterface::class);
    $this->removeProcessor = mock(ProcessorInterface::class);
    $this->security = mock(Security::class);

    $this->processor = new OrderPostProcessor(
        $this->persistProcessor,
        $this->removeProcessor,
        $this->security
    );
});

it('processes an order for a POST operation', function () {
    $customer = mock(Customer::class);
    $operation = new Post();
    $order = new Order();

    $this->security
        ->shouldReceive('getUser')
        ->andReturn($customer);

    $this->persistProcessor
        ->shouldReceive('process')
        ->with($order, $operation, [], [])
        ->andReturn($order);

    $result = $this->processor->process($order, $operation);

    expect($result)->toBe($order)
        ->and($order->getCustomer())->toBe($customer);
});

it('throws an exception if the user is not a customer', function () {
    $this->security
        ->shouldReceive('getUser')
        ->andReturn(null);

    $order = new Order();

    $this->processor->process($order, new Post());
})->throws(UnauthorizedHttpException::class, 'User must be an instance of Customer.');

it('processes an order for a DELETE operation', function () {
    $operation = new Delete();
    $order = new Order();

    $this->removeProcessor
        ->shouldReceive('process')
        ->with($order, $operation, [], [])
        ->andReturnNull();

    $result = $this->processor->process($order, $operation);

    expect($result)->toBeNull();
});

it('processes with additional context', function () {
    $customer = mock(Customer::class);
    $operation = new Post();
    $order = new Order();
    $context = ['key' => 'value'];

    $this->security
        ->shouldReceive('getUser')
        ->andReturn($customer);

    $this->persistProcessor
        ->shouldReceive('process')
        ->with($order, $operation, [], $context)
        ->andReturn($order);

    $result = $this->processor->process($order, $operation, [], $context);

    expect($result)->toBe($order)
        ->and($order->getCustomer())->toBe($customer);
});
