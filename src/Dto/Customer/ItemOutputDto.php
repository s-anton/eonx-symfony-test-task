<?php

namespace App\Dto\Customer;

/**
 * Class ItemOutputDto
 * @package App\Dto\Customer
 * @codeCoverageIgnore - it will be tests as schema test in CustomerTest::testGetItem
 */
class ItemOutputDto
{
    public int $id;
    public string $fullName;
    public string $email;
    public string $country;
    public string $username;
    public string $gender;
    public string $city;
    public string $phone;
}
