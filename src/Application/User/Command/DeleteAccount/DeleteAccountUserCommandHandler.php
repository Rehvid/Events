<?php

declare(strict_types=1);

namespace App\Application\User\Command\DeleteAccount;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;

class DeleteAccountUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(DeleteAccountUserCommand $deleteAccountUserCommand): void
    {
        $this->userRepository->remove($deleteAccountUserCommand->getUser());
    }
}
