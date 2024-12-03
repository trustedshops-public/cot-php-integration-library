<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use TRSTD\COT\Client;
use TRSTD\COT\Exception\RequiredParameterMissingException;
use TRSTD\COT\AuthStorageInterface;
use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use TRSTD\COT\Token;

final class ClientTest extends TestCase
{
    private const TEST_ID_TOKEN = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4iLCJpYXQiOjE1MTYyMzkwMjIsImN0Y19pZCI6IjEyMzQ1Njc4OTAifQ.vCzpQFib8wXKVzHBSuBkaqskUc3Q7RyaLvJ5HkmI4zA";

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingTsId()
    {
        $mockAuthStorage = $this->createMock(AuthStorageInterface::class);
        $this->expectException(RequiredParameterMissingException::class);
        new Client('', 'CLIENT_ID', 'CLIENT_SECRET', $mockAuthStorage);
    }

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingClientId()
    {
        $mockAuthStorage = $this->createMock(AuthStorageInterface::class);
        $this->expectException(RequiredParameterMissingException::class);
        new Client('TSID', '', 'CLIENT_SECRET', $mockAuthStorage);
    }

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingClientSecret()
    {
        $mockAuthStorage = $this->createMock(AuthStorageInterface::class);
        $this->expectException(RequiredParameterMissingException::class);
        new Client('TSID', 'CLIENT_ID', '', $mockAuthStorage);
    }

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingAuthStorage()
    {
        $this->expectException(RequiredParameterMissingException::class);
        new Client('TSID', 'CLIENT_ID', 'CLIENT_SECRET', null);
    }

    /**
     * @covers \TRSTD\COT\Client::handleCallback
     * @return void
     */
    public function testHandleCallbackWithCode()
    {
        $_GET['code'] = 'testCode';
        $_SERVER['HTTP_HOST'] = 'testHost';
        $_SERVER["REQUEST_URI"] = 'testUri';
        $_COOKIE["TRSTD_CV"] = null;
        $_COOKIE["TRSTD_CC"] = null;

        $expectedRequests = [
            function ($method, $url, $options): MockResponse {
                $this->assertSame('POST', $method);
                $this->assertSame('https://auth.trustedshops.com/auth/realms/myTS-QA/protocol/openid-connect/token', $url);
                $this->assertArrayHasKey('body', $options);
                $this->assertArrayHasKey('headers', $options);

                return new MockResponse('{"id_token":"' . self::TEST_ID_TOKEN . '", "refresh_token":"testRefreshToken", "access_token":"testAccessToken"}');
            },
        ];

        $mockedHttpClient = new MockHttpClient($expectedRequests);
        $mockedLogger = $this->createMock(LoggerInterface::class);
        $mockedCacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->expects($this->once())->method('set')->with("1234567890", new Token(self::TEST_ID_TOKEN, 'testRefreshToken', 'testAccessToken'));

        $client = new Client('TSID', 'CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);
        $client->setHttpClient($mockedHttpClient);
        $client->setCacheItemPool($mockedCacheItemPool);

        $client->handleCallback();
    }
}
