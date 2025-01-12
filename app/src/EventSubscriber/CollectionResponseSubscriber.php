<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CollectionResponseSubscriber implements EventSubscriberInterface
{
    private const string HEADER_CONTENT_RANGE = 'Content-Range';
    private const string HEADER_ACCESS_CONTROL_EXPOSE = 'Access-Control-Expose-Headers';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['addRangeHeader', EventPriorities::POST_RESPOND],
        ];
    }

    public function addRangeHeader(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        /** @var GetCollection|null $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$operation instanceof GetCollection || !$request->attributes->get('_api_resource_class')) {
            return;
        }

        /** @var Paginator $ormPaginator */
        $ormPaginator = $request->attributes->get('data');

        $page = (int) $ormPaginator->getCurrentPage();
        $limit = (int) $ormPaginator->getItemsPerPage();
        $itemsCount = $ormPaginator->count();
        $totalItems = (int) $ormPaginator->getTotalItems();
        $start = ($page * $limit) - $itemsCount;
        $end = ($page * $limit) - ($limit - $itemsCount);

        $response->headers->set(self::HEADER_CONTENT_RANGE, sprintf('items %d-%d/%d', $start, $end, $totalItems));
        $response->headers->set(self::HEADER_ACCESS_CONTROL_EXPOSE, self::HEADER_CONTENT_RANGE);
    }
}
