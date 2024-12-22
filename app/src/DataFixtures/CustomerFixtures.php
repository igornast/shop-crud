<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Doctrine\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerFixtures extends Fixture
{
    public const string CUSTOMER_PASSWORD = 'password';
    public const string CUSTOMER_ADMIN_EMAIL = 'admin@example.com';
    public const string CUSTOMER_USER_EMAIL = 'user@example.com';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; ++$i) {
            $customer = new Customer();

            $customer->setName($faker->name);
            $customer->setEmail($faker->unique()->safeEmail);
            $roles = (0 === $i % 2) ? ['ROLE_ADMIN'] : ['ROLE_USER'];
            $customer->setRoles($roles);

            $hashedPassword = $this->passwordHasher->hashPassword($customer, self::CUSTOMER_PASSWORD);
            $customer->setPassword($hashedPassword);

            $manager->persist($customer);
        }

        $user = (new Customer())
        ->setName('Admin User')
        ->setEmail(self::CUSTOMER_ADMIN_EMAIL)
        ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::CUSTOMER_PASSWORD));
        $manager->persist($user);

        $user = (new Customer())
        ->setName('Regular User')
        ->setEmail(self::CUSTOMER_USER_EMAIL)
        ->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::CUSTOMER_PASSWORD));
        $manager->persist($user);

        $manager->flush();
    }
}
