<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Doctrine\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Money\Currency;
use Money\Money;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; ++$i) {
            $product = new Product();

            /* @phpstan-ignore-next-line */
            $product->setSku($faker->unique()->ean13);
            $product->setName($faker->word.' '.$faker->word);
            $product->setCategory($faker->randomElement(['Electronics', 'Clothing', 'Home', 'Books', 'Toys']));
            $product->setBrand($faker->company);
            $product->setPrice(new Money($faker->numberBetween(1000, 100000), new Currency('USD')));
            $product->setDescription($faker->optional()->paragraph);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
