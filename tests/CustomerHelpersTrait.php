<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

trait CustomerHelpersTrait
{
    public function deleteAllInCustomerTable()
    {
        /** @var EntityManagerInterface $em */
        $em = (self::bootKernel())->getContainer()->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\Customer as c')->execute();
    }

    public function createFakeData()
    {
        $faker = Factory::create();

        return [
            'gender' => $faker->randomElement(['male', 'female']),
            'phone' => $faker->phoneNumber,
            'login' => [
                'username' => $faker->userName,
            ],
            'name' => [
                'last' => $faker->lastName,
                'first' => $faker->firstName,
            ],
            'email' => $faker->email,
            'location' => [
                'country' => $faker->country,
                'city' => $faker->city,
            ],
        ];
    }
}
