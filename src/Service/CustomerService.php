<?php

namespace App\Service;

use App\DataProvider\CustomerDataProvider;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

class CustomerService
{
    public const DEFAULT_NUMBER_OF_USERS_TO_BE_REACHED = 100;
    public const DEFAULT_NUMBER_OF_LOADED_USERS_PER_REQUEST = 100;
    public const DEFAULT_NATIONALITY_CODE = 'au';

    public const ENTITY_WAS_CREATED = 1;
    public const ENTITY_WAS_UPDATED = 2;

    public int $numberPerRequest = self::DEFAULT_NUMBER_OF_LOADED_USERS_PER_REQUEST;
    public int $numberToImport = self::DEFAULT_NUMBER_OF_USERS_TO_BE_REACHED;
    public string $nationality = self::DEFAULT_NATIONALITY_CODE;

    public CustomerDataProvider $provider;
    public EntityManagerInterface $em;
    public CustomerRepository $repository;

    protected array $existedRecords = [];

    public function __construct(
        CustomerDataProvider $provider,
        EntityManagerInterface $em,
        CustomerRepository $repository
    ) {
        $this->provider = $provider;
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * This function tries to import new users until provided count of records will be reached
     *
     * @return bool
     */
    public function importUsers(): bool
    {
        $importedCount = 0;
        while ($importedCount < $this->numberToImport) {
            $portion = $this->loadPortion();
            $count = $this->handleIncomingData($portion);

            if ($count === 0) {
                // Nothing imported, it can be for various reasons, but we just
                // break the cycle because its just test task
                break;
            }

            $importedCount += $count;
        }

        return $importedCount === $this->numberToImport;
    }

    public function setNationality(string $code): CustomerService
    {
        $this->nationality = $code;

        return $this;
    }

    public function setNumberPerRequest(int $numberPerRequest): CustomerService
    {
        $this->numberPerRequest = $numberPerRequest ?? self::DEFAULT_NUMBER_OF_LOADED_USERS_PER_REQUEST;

        return $this;
    }

    public function setNumberToImport(int $numberToImport): CustomerService
    {
        $this->numberToImport = $numberToImport ?? self::DEFAULT_NUMBER_OF_USERS_TO_BE_REACHED;

        return $this;
    }

    /**
     * Function loads data using data-provider class
     * We dont respect pagination because external api returns different result for same requests
     *
     * @return array
     */
    protected function loadPortion(): array
    {
        try {
            // If results does not exists php throws exception and we will catch it and return empty array
            // We dont respect any other exceptions for same reason
            $data = $this->provider
                ->setNumberPerRequest($this->numberPerRequest)
                ->setNationality($this->nationality)
                ->loadUsers();

            return $data['results'];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Function handles incoming data and returns count of new records
     *
     * @param array $data
     * @return int
     */
    protected function handleIncomingData(array $data): int
    {
        $this->fillExistedRecords($data);
        $importedCount = 0;
        foreach ($data as $entry) {
            $result = $this->makeOrUpdateCustomerEntity($entry);
            if ($result === static::ENTITY_WAS_CREATED) {
                $importedCount++;
            }
        }

        foreach ($this->existedRecords as $record) {
            $this->em->persist($record);
        }
        $this->em->flush();

        return $importedCount;
    }

    /**
     * Preload records
     *
     * @param array $data
     */
    protected function fillExistedRecords(array $data): void
    {
        $this->existedRecords = [];
        $emails = array_filter(
            array_unique(
                array_map(
                    static function ($item) {
                        return $item['email'] ?? null;
                    },
                    $data
                )
            )
        );
        $results = $this->em->createQueryBuilder()
            ->select('c')
            ->from('App:Customer', 'c')
            ->where('c.email in (:emails)')
            ->setParameter('emails', $emails)
            ->getQuery()
            ->getResult();
        /** @var Customer $customer */
        foreach ($results as $customer) {
            $this->existedRecords[$customer->getEmail()] = $customer;

        }
    }

    /**
     * This function makes or updates Customer entity
     * Returns int for success, false otherwise
     * int result can be 1 ot 2, 1 for create, 2 for update
     *
     * @param array $data
     * @return false|int
     */
    protected function makeOrUpdateCustomerEntity(array $data)
    {
        if (!isset($data['email'])) {
            return false;
        }
        $email = $data['email'];
        $existed = $this->existedRecords[$email] ?? null;
        $result = $existed instanceof Customer ? self::ENTITY_WAS_UPDATED : self::ENTITY_WAS_CREATED;

        $customer = $existed ?? new Customer();
        try {
            $customer->setGender($data['gender']);
            $customer->setPhone($data['phone']);
            $customer->setUsername($data['login']['username']);
            $customer->setLastName($data['name']['last']);
            $customer->setFirstName($data['name']['first']);
            $customer->setEmail($email);
            $customer->setCountry($data['location']['country']);
            $customer->setCity($data['location']['city']);
        } catch (\Throwable $e) {
            return false;
        }

        $this->existedRecords[$email] = $customer;

        return $result;
    }
}
