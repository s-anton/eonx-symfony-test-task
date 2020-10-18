<?php

namespace App\Tests;

use App\Entity\Customer;
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

    /**
     * @return array
     */
    public function createFakeData(): array
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

    public function createFakeCustomer(): Customer
    {
        $faker = Factory::create();

        $c = new Customer();
        $c->setGender($faker->randomElement(['male', 'female']));
        $c->setPhone($faker->phoneNumber);
        $c->setUsername($faker->userName);
        $c->setLastName($faker->lastName);
        $c->setFirstName($faker->firstName);
        $c->setEmail($faker->email);
        $c->setCountry($faker->country);
        $c->setCity($faker->city);

        return $c;
    }
}
