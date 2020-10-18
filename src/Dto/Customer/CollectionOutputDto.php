<?php

namespace App\Dto\Customer;

/**
 * Class CollectionOutputDto
 * @package App\Dto\Customer
 * @codeCoverageIgnore - it will be tests as schema test in CustomerTest::testGetCollection
 */
class CollectionOutputDto
{
    public int $id;
    public string $fullName;
    public string $email;
    public string $country;
}
