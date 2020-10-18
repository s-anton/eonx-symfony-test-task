<?php

namespace App\Dto\Customer;

/**
 * Class ItemOutputDto
 * @package App\Dto\Customer
 * @codeCoverageIgnore - it will be tests as schema test in CustomerTest::testGetItem
 */
class ItemOutputDto
{
    public $id;
    public $fullName;
    public $email;
    public $country;
    public $username;
    public $gender;
    public $city;
    public $phone;
}
