<?php

declare(strict_types=1);

namespace App\Application\User\Command\Register;

use App\Shared\Application\Command\CommandInterface;

final class RegisterUserCommand implements CommandInterface
{
    public function __construct(
        private readonly string $email,
        private readonly string $plainPassword,
        private readonly string $firstname,
        private readonly string $lastname,
        private readonly array $roles,
    ) {
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
