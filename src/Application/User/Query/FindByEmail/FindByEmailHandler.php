<?php

declare(strict_types=1);

namespace App\Application\User\Query\FindByEmail;

use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use App\Shared\Application\Query\QueryHandlerInterface;

final class FindByEmailHandler implements QueryHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @throws UserNotFoundException
     */
    public function __invoke(FindByEmailQuery $findByEmailQuery): ?User
    {
        $user = $this->userRepository->findByEmail($findByEmailQuery->getEmail());

        if (null === $user) {
            throw new UserNotFoundException('User not found');
        }

        return $this->userRepository->findByEmail($findByEmailQuery->getEmail());
    }
}
