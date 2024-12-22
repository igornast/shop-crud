<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Doctrine\Entity\Customer;
use App\Doctrine\Entity\Order;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProcessorInterface<Order, Order|void>
 */
class OrderPostProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<Order, Order|void> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        /** @var ProcessorInterface<Order, Order|void> */
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private readonly ProcessorInterface $removeProcessor,
        private readonly Security $security,
    ) {
    }

    /**
     * @param Order                                                                                                                                              $data
     * @param string[]                                                                                                                                           $uriVariables
     * @param array<string, mixed>&array{request?: Request|\Illuminate\Http\Request, previous_data?: mixed, resource_class?: string|null, original_data?: mixed} $context
     *
     * @return Order
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($operation instanceof DeleteOperationInterface) {
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }

        $customer = $this->security->getUser();

        if (!$customer instanceof Customer) {
            throw new UnauthorizedHttpException('customer', 'User must be an instance of Customer.');
        }

        if ($operation instanceof Post) {
            $data->setCustomer($customer);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
