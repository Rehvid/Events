<?php

namespace Controller;

use App\Application\User\Command\Register\RegisterUserCommand;
use App\Application\User\Query\FindAll\FindAllQuery;
use App\Application\User\Query\FindByEmail\FindByEmailQuery;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Role;
use App\Domain\User\User;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class ProfileControllerTest extends WebTestCase
{

    private readonly User $user;
    private readonly mixed $commandBus;
    private readonly mixed $queryBus;

    private readonly Generator $faker;
    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->commandBus = $this->getContainer()->get('command.bus');
        $this->queryBus = $this->getContainer()->get('query.bus');
        $this->faker = Factory::create();
        $this->user = $this->findUser();
        $this->client->loginUser($this->user);
    }

    public function testIsPageDisplay(): void
    {
        $this->client->request('GET', '/user/profile');

        $this->assertResponseIsSuccessful();
    }

    public function testCanUserUpdateProfileInformation(): void
    {
        $this->client->request('GET', '/user/profile');
        $firstname = $this->user->getFirstname();
        $lastname = $this->user->getLastname();
        $currentEmail = $this->user->getEmail();
        $newEmail = $this->faker->unique()->email();

        $this->client->submitForm('Submit', [
            'profile_user_form[firstname]' => $this->faker->firstName(),
            'profile_user_form[lastname]' => $this->faker->lastName(),
            'profile_user_form[email]' => $newEmail
        ], method: 'PATCH');

        $this->assertResponseIsSuccessful();

        try {
            $handleStamp = $this->queryBus->dispatch(new FindByEmailQuery(email: $newEmail));
            $user = $handleStamp->last(HandledStamp::class)->getResult();
        } catch (\Exception) {
            $this->expectException(HandlerFailedException::class);
        }

        $this->assertNotEquals($firstname, $user->getFirstname());
        $this->assertNotEquals($lastname, $user->getLastname());
        $this->assertNotEquals($currentEmail, $user->getEmail());
    }

    public function testUserCanDeleteHisAccount(): void
    {
        $this->assertNotNull($this->user);
        $this->client->request(
            'GET',
            "user/profile/{$this->user->getId()}/delete"
        );

        $this->assertResponseRedirects();

        $this->expectException(HandlerFailedException::class);
        $this->expectExceptionMessage('User not found');
        $this->queryBus->dispatch(new FindByEmailQuery(email: $this->user->getEmail()));
    }

    public function testUserCanChangePassword(): void
    {
        $this->client->request(
            'GET',
            '/user/profile/change-password'
        );

        $userPassword = $this->user->getPassword();
        $newPassword = $this->faker->password(8);

        $this->client->submitForm('Submit', [
            'change_password_user_form[plainPassword][first]' => $newPassword,
            'change_password_user_form[plainPassword][second]' => $newPassword,
        ], method: 'PATCH');

        $this->client->followRedirects();
        $handleStamp = $this->queryBus->dispatch(new FindByEmailQuery(email: $this->user->getEmail()));
        $user = $handleStamp->last(HandledStamp::class)->getResult();

        $this->assertNotSame($userPassword, $user->getPassword());
    }

    private function findUser(): User
    {
        $user = null;

        try {
            $users = $this->queryBus->dispatch(new FindAllQuery());
            $user = $this->faker->randomElement($users);
        } catch (\Exception) {
            $email = $this->faker->unique()->email();
            $this->commandBus->dispatch(
                new RegisterUserCommand(
                    email: $email,
                    plainPassword: $this->faker->password(8),
                    firstname: $this->faker->firstName(),
                    lastname: $this->faker->lastName(),
                    roles: Role::valueToArray(Role::USER)
                )
            );

            $handleStamp = $this->queryBus->dispatch(new FindByEmailQuery(email: $email));
            $user = $handleStamp->last(HandledStamp::class)->getResult();
        }

       return $user;
    }

}