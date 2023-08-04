<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function add(User $user): void;

    public function remove(User $user): void;

    public function update(User $user): void;
}
