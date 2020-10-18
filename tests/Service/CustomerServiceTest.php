<?php

namespace App\Tests\Service;

use App\DataProvider\CustomerDataProvider;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\CustomerService;
use App\Tests\CustomerHelpersTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomerServiceTest extends KernelTestCase
{
    use CustomerHelpersTrait;

    protected CustomerService $service;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CustomerService $service */
        $service = (self::bootKernel())->getContainer()->get(CustomerService::class);
        $this->service = $service;
        $this->em = (self::bootKernel())->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        $this->deleteAllInCustomerTable();
    }

    public function testNationalitySettedCorrectly(): void
    {
        $code = 'aaaa';
        $this->service->setNationality($code);
        self::assertEquals($code, $this->service->nationality);
    }

    public function testNumberPerRequestSettedCorrectly(): void
    {
        $num = 123;
        $this->service->setNumberPerRequest($num);
        self::assertEquals($num, $this->service->numberPerRequest);
    }

    public function testNumberToImportSettedConrrectly(): void
    {
        $num = 321;
        $this->service->setNumberToImport($num);
        self::assertEquals($num, $this->service->numberToImport);
    }

    public function testImportUsersEmpty(): void
    {
        $stub = $this->createMock(CustomerDataProvider::class);
        $stub->method('loadUsers')->willReturn([]);

        $this->service->provider = $stub;

        self::assertEquals(false, $this->service->importUsers());
    }

    public function testImportUsersFully(): void
    {
        $stub = $this->createMock(CustomerDataProvider::class);
        $stub->method('setNumberPerRequest')->will(self::returnSelf());
        $stub->method('setNationality')->will(self::returnSelf());
        $stub->method('loadUsers')->willReturn(
            [
                'results' => [$this->createFakeData(),],
            ]
        );

        $this->service->provider = $stub;
        $this->service->setNumberToImport(1);

        self::assertTrue($this->service->importUsers());
    }

    public function testImportUsersPartically(): void
    {
        $this->deleteAllInCustomerTable();

        $stub = $this->createMock(CustomerDataProvider::class);
        $stub->method('setNumberPerRequest')->will(self::returnSelf());
        $stub->method('setNationality')->will(self::returnSelf());
        $stub->method('loadUsers')->willReturn(
            [
                'results' => [
                    $this->createFakeData(),
                    [
                        'invalid' => 'record',
                    ],
                    [
                        'email' => 'exists',
                        'but' => 'other',
                        'data' => 'is absent',
                    ],
                ],
            ]
        );
        $this->service->provider = $stub;
        $this->service->setNumberToImport(2);

        /** @var CustomerRepository $repo */
        $repo = $this->em->getRepository(Customer::class);

        self::assertFalse($this->service->importUsers());
        self::assertEquals(1, $repo->count([]));
    }

    public function testImportUsersUpdateRecordIfEmailExists(): void
    {
        $this->deleteAllInCustomerTable();

        $newPhone = 'changed-phone';

        $data = $changedData = $this->createFakeData();
        $changedData['phone'] = $newPhone;

        $stub = $this->createMock(CustomerDataProvider::class);
        $stub->method('setNumberPerRequest')->will(self::returnSelf());
        $stub->method('setNationality')->will(self::returnSelf());
        $stub->method('loadUsers')->willReturn(
            [
                'results' => [$data, $changedData],
            ]
        );
        $this->service->provider = $stub;
        $this->service->setNumberToImport(2);

        self::assertFalse($this->service->importUsers()); // Actually only one imported customer

        $customer = $this->em->getRepository(Customer::class)->findOneBy(['email' => $data['email']]);

        self::assertEquals($newPhone, $customer->getPhone());
    }

}
