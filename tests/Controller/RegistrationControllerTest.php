<?php

namespace Controller;

use App\Domain\User\User;
use Faker\Factory;
use Faker\Provider\Address;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testIsDisplayPage(): void
    {
        $client = self::createClient();
        $client->request('GET','/register');

        $this->assertResponseIsSuccessful();
    }

    public function testRegisterUserWithNotPassingValidation(): void
    {

        $client = self::createClient();
        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'register_user_form[firstname]' => 't',
            'register_user_form[lastname]' => 'Test',
            'register_user_form[email]' => 'test@teasta',
            'register_user_form[plainPassword]' => 'Test1235'
        ]);

        $this->assertEquals($client->getResponse()->isClientError(), $client->getResponse()->getStatusCode());
        $this->assertFalse($client->getResponse()->isRedirection());
    }

    public function testRegisterUserWithPassingValidation(): void
    {
        $client = static::createClient();
        $faker = Factory::create();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository(User::class);
        $email = $faker->unique()->email();
        $firstname = $faker->firstName();
        $lastname = $faker->lastName();

        $client->request('GET', '/register');
        $client->submitForm('Register', [
            'register_user_form[firstname]' => $firstname,
            'register_user_form[lastname]' => $lastname,
            'register_user_form[email]' => $email,
            'register_user_form[plainPassword]' => 'Test1235'
        ]);

        $entity = $repository->findByEmail($email);
        $this->assertNotNull($entity);
        $this->assertEquals($email, $entity->getEmail());
        $this->assertEquals($firstname, $entity->getFirstName());
        $this->assertEquals($lastname, $entity->getLastName());
        $this->assertFalse($entity->isVerified());
        $this->assertResponseRedirects('/login');

        $client->followRedirect();
    }
}