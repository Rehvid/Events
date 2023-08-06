<?php

declare(strict_types=1);

namespace App\Application\User\Command\UpdateProfile;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;

class UpdateProfileUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(UpdateProfileUserCommand $updateProfileUserCommand): void
    {
        $user = $this->userRepository->findByEmail($updateProfileUserCommand->getEmail());

        $user->setFirstname($updateProfileUserCommand->getFirstname())
            ->setLastname($updateProfileUserCommand->getLastname())
            ->setEmail($updateProfileUserCommand->getEmail());

        $this->userRepository->update($user);
    }
}
