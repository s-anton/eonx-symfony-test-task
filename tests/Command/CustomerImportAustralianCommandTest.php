<?php

namespace App\Tests\Command;

use App\Command\CustomerImportAustralianCommand;
use App\DataProvider\CustomerDataProvider;
use App\Entity\Customer;
use App\Service\CustomerService;
use App\Tests\CustomerHelpersTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CustomerImportAustralianCommandTest extends KernelTestCase
{
    use CustomerHelpersTrait;

    protected function tearDown(): void
    {
        $this->deleteAllInCustomerTable();
    }

    public function testExecuteWhenFailure()
    {
        $this->deleteAllInCustomerTable();

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $stub = $this->createMock(CustomerDataProvider::class);
        $stub->method('setNumberPerRequest')->will(self::returnSelf());
        $stub->method('setNationality')->will(self::returnSelf());
        $stub->method('loadUsers')->willReturn(
            [
                'results' => [],
            ]
        );

        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $service = new CustomerService($stub, $em, $em->getRepository(Customer::class));
        $service->provider = $stub;

        $kernel->getContainer()->set(CustomerService::class, $service);

        /** @var CustomerImportAustralianCommand $command */
        $command = $application->find('customer:import:australian');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        self::assertEquals("\n Failure, something happened\n", $output);

        $em->persist($this->createFakeCustomer());
        $em->flush();

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        self::assertEquals("\n Failure, but database contains previously created records\n", $output);
    }

    public function testExecuteWhenSuccess()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $stub = $this->createMock(CustomerDataProvider::class);
        $stub->method('setNumberPerRequest')->will(self::returnSelf());
        $stub->method('setNationality')->will(self::returnSelf());
        $stub->method('loadUsers')->willReturn(
            [
                'results' => [$this->createFakeData()],
            ]
        );

        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $service = new CustomerService($stub, $em, $em->getRepository(Customer::class));
        $service->provider = $stub;

        $kernel->getContainer()->set(CustomerService::class, $service);

        /** @var CustomerImportAustralianCommand $command */
        $command = $application->find('customer:import:australian');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--number' => 1]);

        $output = $commandTester->getDisplay();

        self::assertEquals("\n Success\n", $output);
    }
}
