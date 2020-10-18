<?php

namespace App\Dto\Customer;

/**
 * Class CollectionOutputDto
 * @package App\Dto\Customer
 * @codeCoverageIgnore - it will be tests as schema test in CustomerTest::testGetCollection
 */
class CollectionOutputDto
{
    public $id;
    public $fullName;
    public $email;
    public $country;
}
