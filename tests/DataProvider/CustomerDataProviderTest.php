<?php

namespace App\Tests\DataProvider;

use App\DataProvider\CustomerDataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CustomerDataProviderTest extends WebTestCase
{
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->provider = (self::bootKernel())->getContainer()->get(CustomerDataProvider::class);
    }

    public function testLoadUsersNormal()
    {
        $body = '{"body":"response"}';

        $response = new MockResponse($body);
        $this->provider->client = new MockHttpClient($response);

        self::assertSame(json_decode($body, true), $this->provider->loadUsers());
    }

    public function testLoadUserAbnormal()
    {
        $response = new MockResponse('some-non-json-like-content');
        $this->provider->client = new MockHttpClient($response);

        self::assertSame([], $this->provider->loadUsers());
    }

    public function testMakeRequest()
    {
        $this->provider->client = new MockHttpClient(new MockResponse());

        $request = $this->provider->makeRequest();
        self::assertInstanceOf(ResponseInterface::class, $request);
    }

    public function testBuildParamsCustom()
    {
        $this->provider->setNumberPerRequest(200);
        $this->provider->setNationality('ru');

        self::assertEqualsCanonicalizing(
            ['nat' => 'ru', 'results' => 200, 'inc' => CustomerDataProvider::FIELDS_TO_RETRIEVE],
            $this->provider->buildQueryParams()
        );
    }

    public function testBuildParamsDefault()
    {
        self::assertEqualsCanonicalizing(
            ['inc' => CustomerDataProvider::FIELDS_TO_RETRIEVE,],
            $this->provider->buildQueryParams()
        );
    }
}
