<?php

namespace App\Tests\DataProvider;

use App\DataProvider\CustomerDataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CustomerDataProviderTest extends WebTestCase
{
    protected CustomerDataProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CustomerDataProvider $provider */
        $provider = (self::bootKernel())->getContainer()->get(CustomerDataProvider::class);
        $this->provider = $provider;
    }

    public function testLoadUsersNormal(): void
    {
        $body = '{"body":"response"}';

        $response = new MockResponse($body);
        $this->provider->client = new MockHttpClient($response);

        try {
            $body = json_decode($body, true, 1, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $body = null;
        }

        self::assertSame($body, $this->provider->loadUsers());
    }

    public function testLoadUserAbnormal(): void
    {
        $response = new MockResponse('some-non-json-like-content');
        $this->provider->client = new MockHttpClient($response);

        self::assertSame([], $this->provider->loadUsers());
    }

    public function testMakeRequest(): void
    {
        $this->provider->client = new MockHttpClient(new MockResponse());

        $request = $this->provider->makeRequest();
        self::assertInstanceOf(ResponseInterface::class, $request);
    }

    public function testBuildParamsCustom(): void
    {
        $this->provider->setNumberPerRequest(200);
        $this->provider->setNationality('ru');

        self::assertEqualsCanonicalizing(
            ['nat' => 'ru', 'results' => 200, 'inc' => CustomerDataProvider::FIELDS_TO_RETRIEVE],
            $this->provider->buildQueryParams()
        );
    }

    public function testBuildParamsDefault(): void
    {
        self::assertEqualsCanonicalizing(
            ['inc' => CustomerDataProvider::FIELDS_TO_RETRIEVE,],
            $this->provider->buildQueryParams()
        );
    }
}
