<?php

namespace App\Command;

use App\Entity\Customer;
use App\Service\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CustomerImportAustralianCommand extends Command
{
    protected static $defaultName = 'customer:import:australian';

    protected $service;
    protected $em;

    public function __construct(CustomerService $service, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->service = $service;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('This command imports australian customers from external api')
            ->addOption(
                'number',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of users to must be reached',
                CustomerService::DEFAULT_NUMBER_OF_USERS_TO_BE_REACHED
            )
            ->addOption(
                'load-step',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of loaded users per request',
                CustomerService::DEFAULT_NUMBER_OF_LOADED_USERS_PER_REQUEST
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $numberToImport = $input->getOption('number');
        $numberPerRequest = $input->getOption('load-step');

        $result = $this->service
            ->setNumberPerRequest($numberPerRequest)
            ->setNumberToImport($numberToImport)
            ->importUsers();

        if ($result) {
            $io->success('Success, try to use api');
        } else {
            $count = $this->em->getRepository(Customer::class)->count([]);

            if ($count > 0) {
                $io->error('Failure, but database contains previously created records');
            } else {
                $io->error('Failure. something happened');
            }

        }

        return Command::SUCCESS;
    }
}
