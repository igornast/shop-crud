<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CollectionResponseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
      return [
          KernelEvents::RESPONSE => ['addRangeHeader', EventPriorities::POST_RESPOND]
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

        $response->headers->set('Content-Range', sprintf('items %d-%d/%d', $start, $end, $totalItems));
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Range');
    }
}