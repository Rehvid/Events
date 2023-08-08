<?php

namespace App\Application\User\Query\FindAll;

use App\Domain\User\Exception\UserNotFoundException;
use App\Infrastructure\User\Repository\UserRepository;
use App\Shared\Application\Query\QueryHandlerInterface;

class FindAllQueryHandler implements QueryHandlerInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(FindAllQuery $findAllQuery): array
    {
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            throw new UserNotFoundException('Users not found');
        }

        return $users;
    }
}