<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Trait;

use Doctrine\ORM\Mapping as ORM;

trait Timestamps
{
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $timestamp): self
    {
        $this->createdAt = $timestamp;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $timestamp): self
    {
        $this->updatedAt = $timestamp;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtAutomatically(): void
    {
        if (null === $this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtAutomatically(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }
}
