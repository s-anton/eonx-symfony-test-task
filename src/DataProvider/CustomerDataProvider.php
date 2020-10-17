<?php

namespace App\DataProvider;


use App\Service\CustomerService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception;

class CustomerDataProvider
{
    private $url;
    private $client;

    public const FIELDS_TO_RETRIEVE = 'name,email,location,gender,login,phone';

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->url = $params->get('customer_api_provider_url');
        $this->client = $client;
    }

    /**
     * @param int $numberPerRequest
     * @param string|null $nationality
     * @return array
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     */
    public function loadUsers(
        int $numberPerRequest = CustomerService::DEFAULT_NUMBER_OF_LOADED_USERS_PER_REQUEST,
        string $nationality = null
    ): array {
        $queryParams = [
            'results' => $numberPerRequest,
            'inc' => static::FIELDS_TO_RETRIEVE,
        ];
        if (!empty($nationality)) {
            $queryParams['nat'] = $nationality;
        }

        $response = $this->client->request('GET', $this->url, ['query' => $queryParams,]);

        return $response->toArray();
    }
}
