<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Doctrine\Trait\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity,
    ORM\Table(name: 'products'),
    ORM\HasLifecycleCallbacks,
    ApiResource(
        normalizationContext: ['skip_null_values' => false]
    ),
    Get,
    GetCollection,
    Patch(security: 'is_granted("ROLE_ADMIN")'),
    Delete(security: 'is_granted("ROLE_ADMIN")'),
]
class Product
{
    use TimestampTrait;

    #[
        ORM\Id,
        ORM\Column(type: 'uuid'),
        ApiProperty(identifier: true),
        Groups(['read_order']),
        Assert\Uuid(versions: 4)
    ]
    private UuidInterface $id;

    #[
        ORM\Column(type: 'string', length: 255),
        ApiProperty,
        Groups(['read_order']),
        Assert\NotBlank,
    ]
    private string $sku;

    #[
        ORM\Column(type: 'string', length: 255),
        ApiProperty,
        Groups(['read_order']),
        Assert\NotBlank,
    ]
    private string $name;

    #[
        ORM\Column(type: 'string', length: 40),
        ApiProperty,
        Groups(['read_order']),
        Assert\NotBlank,
    ]
    private string $category;

    #[
        ORM\Column(type: 'string', length: 255),
        ApiProperty,
        Groups(['read_order']),
        Assert\NotBlank,
    ]
    private string $brand;

    #[
        ORM\Embedded(class: Money::class, columnPrefix: null),
        ApiProperty(
            openapiContext: ['example' => ['amount' => '72255', 'currency' => 'USD']]
        ),
        Groups(['read_order']),
        Assert\NotNull,
    ]
    private Money $price;

    #[
        ORM\Column(type: 'text', nullable: true),
        ApiProperty,
        Groups(['read_order']),
    ]
    private ?string $description = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): Product
    {
        $this->sku = $sku;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Product
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): Product
    {
        $this->category = $category;

        return $this;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): Product
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): Product
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Product
    {
        $this->description = $description;

        return $this;
    }
}
