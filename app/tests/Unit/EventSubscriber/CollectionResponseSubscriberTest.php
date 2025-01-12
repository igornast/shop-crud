<?php

use ApiPlatform\Metadata\Get;
use ApiPlatform\State\Pagination\PaginatorInterface;
use App\EventSubscriber\CollectionResponseSubscriber;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

beforeEach(function () {
    $this->subscriber = new CollectionResponseSubscriber();
});

it('does not add headers if operation is missing', function () {
    $request = new Request();
    $response = new Response();

    $event = new ResponseEvent(mock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $this->subscriber->addRangeHeader($event);

    expect($response->headers->has('Content-Range'))->toBeFalse()
        ->and($response->headers->has('Access-Control-Expose-Headers'))->toBeFalse();
});

it('does not add headers if operation is not GetCollection', function () {
    $request = new Request();
    $request->attributes->set('_api_operation', new Get());
    $response = new Response();

    $event = new ResponseEvent(mock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $this->subscriber->addRangeHeader($event);

    expect($response->headers->has('Content-Range'))->toBeFalse()
        ->and($response->headers->has('Access-Control-Expose-Headers'))->toBeFalse();
});

it('does not add headers if resource class is missing', function () {
    $request = new Request();
    $request->attributes->set('_api_operation', new GetCollection());
    $response = new Response();

    $event = new ResponseEvent(mock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $this->subscriber->addRangeHeader($event);

    expect($response->headers->has('Content-Range'))->toBeFalse()
        ->and($response->headers->has('Access-Control-Expose-Headers'))->toBeFalse();
});

it('adds headers for collection operation', function () {
    $paginator = mock(PaginatorInterface::class);
    $paginator->shouldReceive('getCurrentPage')->andReturn(2);
    $paginator->shouldReceive('getItemsPerPage')->andReturn(30);
    $paginator->shouldReceive('count')->andReturn(30);
    $paginator->shouldReceive('getTotalItems')->andReturn(200);

    $request = new Request();
    $request->attributes->set('_api_operation', new GetCollection());
    $request->attributes->set('_api_resource_class', 'SomeResourceClass');
    $request->attributes->set('data', $paginator);

    $response = new Response();
    $event = new ResponseEvent(mock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);

    $this->subscriber->addRangeHeader($event);

    expect($response->headers->get('Content-Range'))->toBe('items 30-60/200')
        ->and($response->headers->get('Access-Control-Expose-Headers'))->toBe('Content-Range');
});
