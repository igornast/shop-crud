<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Doctrine\Entity\Product;
use Money\Currency;
use Money\Money;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProcessorInterface<Product, Product|void>
 */
class ProductPatchStateProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<Product, Product|void> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
    ) {
    }

    /**
     * @param Product $data
     *
     * @return Product|void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var Request|null $request */
        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            throw new \RuntimeException();
        }

        /** @phpstan-ignore-next-line  */
        $contentCollection = collect(json_decode($request->getContent(), true));

        if (!$contentCollection->has('price')) {
            return $data;
        }

        $amount = $contentCollection->pluck('amount')->whereNotNull()->first();
        $currency = $contentCollection->pluck('currency')->whereNotNull()->first();

        if (null === $currency || null === $amount) {
            return $data;
        }

        $data->setPrice(new Money($amount, new Currency($currency)));

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
