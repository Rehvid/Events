<?php

namespace Controller;

use App\Application\User\Command\Register\RegisterUserCommand;
use App\Application\User\Query\FindAll\FindAllQuery;
use App\Application\User\Query\FindByEmail\FindByEmailQuery;
use App\Domain\User\Role;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class ResetPasswordControllerTest extends WebTestCase
{

    private readonly mixed $commandBus;
    private readonly mixed $queryBus;

    private Generator $faker;

    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->commandBus = $this->getContainer()->get('command.bus');
        $this->queryBus = $this->getContainer()->get('query.bus');
        $this->faker = Factory::create();
    }

    public function testIsDisplayPage(): void
    {
        $this->client->request('GET','/reset-password');
        $this->assertResponseIsSuccessful();
    }

    public function testResetUserPassword(): void
    {
        $this->client->request('GET','/reset-password');
        $urlGenerator = $this->getContainer()->get('router');
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

        $this->client->submitForm('Send password reset email', [
            'reset_password_request_form[email]' => $user->getEmail(),
        ]);

        $mail = $this->getMailerMessage();

        $this->assertEmailCount(1);
        $this->assertEmailTextBodyContains($mail, 'To reset your password, please visit the following link');
        $this->assertResponseRedirects('/reset-password/check-email');

        $mailToString = $mail->toString();
        $tempToken = trim(
            substr(
                $mailToString,
                stripos($mailToString, 'reset/'),
                stripos($mailToString, 'this') - stripos($mailToString, 'reset/')
            )
        );
        $tokenMail = substr($tempToken, 6, -1);

        $this->client->followRedirects();
        $this->client->request('GET', $urlGenerator->generate('app_reset_password', ['token' => $tokenMail]));

        $password = $this->faker->password(8);
        $this->client->submitForm('Reset password', [
            'change_password_form[plainPassword][first]' => $password,
            'change_password_form[plainPassword][second]' => $password
        ]);

        try {
            $handleStamp = $this->queryBus->dispatch(new FindByEmailQuery(email: $user->getEmail()));
            $userWithNewPassword = $handleStamp->last(HandledStamp::class)->getResult();
            $this->assertNotEquals($userWithNewPassword->getPassword(), $user->getPassword());
            $this->assertNotNull($userWithNewPassword->getUpdatedAt());
        } catch (\Exception) {
            $this->expectException(HandlerFailedException::class);
        }

    }
}