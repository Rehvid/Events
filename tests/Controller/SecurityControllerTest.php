<?php

namespace Controller;

use App\Application\User\Command\Register\RegisterUserCommand;
use App\Application\User\Query\FindByEmail\FindByEmailQuery;
use App\Domain\User\Role;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class SecurityControllerTest extends WebTestCase
{
    public function testIsDisplayPage(): void
    {
        $client = self::createClient();

        $client->request('GET','/login');
        $this->assertResponseIsSuccessful();
    }

    public function testUserLogin(): void
    {
        $client = self::createClient();
        $queryBus = $this->getContainer()->get('query.bus');
        $commandBus = $this->getContainer()->get('command.bus');
        $faker = Factory::create();

        try {
            $email = $faker->unique()->email();
            $password = $faker->password(8);

            $commandBus->dispatch(
                new RegisterUserCommand(
                    email: $email,
                    plainPassword: $password,
                    firstname: $faker->firstName(),
                    lastname: $faker->lastName(),
                    roles: Role::valueToArray(Role::USER)
                )
            );

            $handleStamp = $queryBus->dispatch(
                new FindByEmailQuery(
                    email: $email
                )
            );
            $user = $handleStamp->last(HandledStamp::class)->getResult();
            $this->assertNotNull($user);

        } catch (\Exception) {
            $this->expectException(HandlerFailedException::class);
        }

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            'email' => $email,
            'password' => $password
        ]);

        $this->assertResponseRedirects();
    }
}