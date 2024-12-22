<?php

declare(strict_types=1);

namespace App\Doctrine\Trait;

use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
trait TimestampTrait
{
    #[
        ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false)
    ]
    private \DateTimeImmutable $createdAt;

    #[
        ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false)
    ]
    private \DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
