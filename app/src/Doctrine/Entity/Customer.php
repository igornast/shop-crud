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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity,
    ORM\Table(name: 'customers'),
    ORM\HasLifecycleCallbacks,
    ApiResource(
        normalizationContext: ['groups' => ['read_customer']],
    ),
    Get(security: 'is_granted("ROLE_ADMIN") or object == user'),
    GetCollection(security: 'is_granted("ROLE_ADMIN")'),
    Patch(security: 'is_granted("ROLE_ADMIN")'),
    Delete(security: 'is_granted("ROLE_ADMIN")'),
]
class Customer implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampTrait;

    #[
        ORM\Id,
        ORM\Column(type: 'uuid'),
        ApiProperty(identifier: true),
        Assert\Uuid(versions: 4),
        Groups(['read_customer', 'read_order'])
    ]
    private UuidInterface $id;

    #[
        ORM\Column(type: 'string', length: 100),
        ApiProperty,
        Assert\NotBlank,
        Assert\Length(min: 2, max: 100),
        Groups(['read_customer', 'read_order'])
    ]
    private string $name;

    #[
        ORM\Column(type: 'string', length: 255, unique: true),
        ApiProperty,
        Assert\Email,
        Groups(['read_customer', 'read_order'])
    ]
    private string $email;

    /** @var string[] */
    #[
        ORM\Column(type: 'json'),
        Groups(['read_customer'])
    ]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Customer
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Customer
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): Customer
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPassword(string $password): Customer
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        return;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
