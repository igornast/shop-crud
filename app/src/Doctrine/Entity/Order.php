<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Doctrine\Trait\TimestampTrait;
use App\State\OrderPostProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity,
    ORM\Table(name: 'orders'),
    ORM\HasLifecycleCallbacks,
    ApiResource(
        normalizationContext: ['groups' => ['read_order']],
        denormalizationContext: ['groups' => ['write_order']],
    ),
    Get(security: 'is_granted("ROLE_ADMIN") or object.getCustomer() == user'),
    GetCollection(security: 'is_granted("ROLE_ADMIN")'),
    Post(
        security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")',
        processor: OrderPostProcessor::class,
    ),
    Patch(security: 'is_granted("ROLE_ADMIN")'),
    Delete(security: 'is_granted("ROLE_ADMIN")'),
]
class Order
{
    use TimestampTrait;

    #[
        ORM\Id,
        ORM\Column(type: 'uuid'),
        ApiProperty(identifier: true),
        Assert\Uuid(versions: 4),
        Groups(['read_order'])
    ]
    private UuidInterface $id;

    #[
        ORM\ManyToOne(targetEntity: Customer::class),
        ApiProperty,
        Groups(['read_order'])
    ]
    private Customer $customer;

    /** @var Collection<int, Product> */
    #[
        ORM\JoinTable(name: 'orders_products'),
        ORM\JoinColumn(name: 'order_id', nullable: false, onDelete: 'CASCADE'),
        ORM\InverseJoinColumn(name: 'product_id', nullable: false, onDelete: 'CASCADE'),
        ORM\ManyToMany(targetEntity: Product::class),
        ApiProperty,
        Groups(['read_order', 'write_order'])
    ]
    private Collection $items;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->items = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): Order
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection<int, Product> $items
     */
    public function setItems(Collection $items): Order
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(Product $product): Order
    {
        if ($this->items->contains($product)) {
            return $this;
        }

        $this->items->add($product);

        return $this;
    }

    public function removeItem(Product $product): Order
    {
        if (!$this->items->contains($product)) {
            return $this;
        }

        $this->items->removeElement($product);

        return $this;
    }
}
