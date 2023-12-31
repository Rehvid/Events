<?php

declare(strict_types=1);

namespace App\Application\User\Command\UpdateProfile;

use App\Domain\User\User;
use App\Shared\Application\Command\CommandInterface;

class UpdateProfileUserCommand implements CommandInterface
{
    public function __construct(
        private readonly string $firstname,
        private readonly string $lastname,
        private readonly string $email,
        private readonly User $user,
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

    public function getUser(): User
    {
        return $this->user;
    }
}
