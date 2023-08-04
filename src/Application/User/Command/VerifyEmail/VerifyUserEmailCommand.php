<?php

declare(strict_types=1);

namespace App\Application\User\Command\VerifyEmail;

use App\Shared\Application\Command\CommandInterface;

final class VerifyUserEmailCommand implements CommandInterface
{
    public function __construct(
        private readonly string $email,
        private readonly bool $isVerified,
        private readonly array $roles,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
