<?php

namespace App\Tests\ApiResource;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class CustomerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /** @var EntityManagerInterface $em */
    private $em;

    protected function setUp()
    {
        parent::setUp();

        $this->em = (self::bootKernel())->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Actually this code tests ApiPlatform, but not mine
     * Thus I will check that resource is properly configured
     *
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollection()
    {
        $response = static::createClient()->request(
            'GET',
            '/api/customers',
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        /** @var CustomerRepository $repo */
        $repo = $this->em->getRepository(Customer::class);

        static::assertResponseIsSuccessful();

        static::assertEquals($repo->count([]), \count($response->toArray())); // service must return all records

        $item = $response->toArray()[0];
        self::assertSame(['id', 'fullName', 'email', 'country'], array_keys($item));
    }

    public function testGetItem()
    {
        /** @var CustomerRepository $repo */
        $repo = $this->em->getRepository(Customer::class);

        $customer = $repo->findOneBy([]);

        $response = static::createClient()->request(
            'GET',
            sprintf('/api/customers/%d', $customer->getId()),
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $responseData = $response->toArray();
        self::assertSame(
            [
                'id' => $customer->getId(),
                'fullName' => $customer->getFullName(),
                'email' => $customer->getEmail(),
                'country' => $customer->getCountry(),
                'username' => $customer->getUsername(),
                'gender' => $customer->getGender(),
                'city' => $customer->getCity(),
                'phone' => $customer->getPhone(),
            ],
            $responseData
        );
    }
}
