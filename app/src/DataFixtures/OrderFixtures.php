<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Doctrine\Entity\Customer;
use App\Doctrine\Entity\Order;
use App\Doctrine\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            ProductFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $customers = collect($manager->getRepository(Customer::class)->findAll());
        $products = $manager->getRepository(Product::class)->findAll();

        $customers = $customers->filter(function (Customer $customer) {
            return CustomerFixtures::CUSTOMER_USER_EMAIL !== $customer->getEmail() && CustomerFixtures::CUSTOMER_ADMIN_EMAIL !== $customer->getEmail();
        });

        for ($i = 0; $i < 10; ++$i) {
            $order = new Order();


            $customer = $faker->randomElement($customers);
            $order->setCustomer($customer);

            $orderItems = $faker->randomElements($products, $faker->numberBetween(1, 5));
            foreach ($orderItems as $product) {
                $order->addItem($product);
            }

            $manager->persist($order);
        }

        $manager->flush();
    }
}
