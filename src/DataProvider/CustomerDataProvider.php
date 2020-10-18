<?php

namespace App\DataProvider;


use App\Service\CustomerService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CustomerDataProvider
{
    public const FIELDS_TO_RETRIEVE = 'name,email,location,gender,login,phone';

    public string $url;
    public HttpClientInterface $client;

    public int $numberPerRequest;
    public string $nationality;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->url = $params->get('customer_api_provider_url');
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function loadUsers(): array
    {
        try {
            return $this->makeRequest()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @return ResponseInterface
     * @throws Exception\TransportExceptionInterface
     */
    public function makeRequest()
    {
        return $this->client->request('GET', $this->url, ['query' => $this->buildQueryParams(),]);
    }

    public function buildQueryParams(): array
    {
        $queryParams = [
            'inc' => static::FIELDS_TO_RETRIEVE,
        ];
        if (!empty($this->numberPerRequest)) {
            $queryParams['results'] = $this->numberPerRequest;
        }
        if (!empty($this->nationality)) {
            $queryParams['nat'] = $this->nationality;
        }

        return $queryParams;
    }

    public function setNumberPerRequest(int $numberPerRequest): self
    {
        $this->numberPerRequest = $numberPerRequest;

        return $this;
    }

    public function setNationality(string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }
}
