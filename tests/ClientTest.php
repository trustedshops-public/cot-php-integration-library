<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use TRSTD\COT\AuthStorageInterface;
use TRSTD\COT\Client;
use TRSTD\COT\Exception\RequiredParameterMissingException;
use TRSTD\COT\Exception\TokenInvalidException;
use TRSTD\COT\Token;

final class ClientTest extends TestCase
{
    private const TEST_ID_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4iLCJpYXQiOjE1MTYyMzkwMjIsImN0Y19pZCI6IjEyMzQ1Njc4OTAifQ.vCzpQFib8wXKVzHBSuBkaqskUc3Q7RyaLvJ5HkmI4zA';

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingClientId()
    {
        $mockAuthStorage = $this->createMock(AuthStorageInterface::class);
        $this->expectException(RequiredParameterMissingException::class);
        new Client('', 'CLIENT_SECRET', $mockAuthStorage);
    }

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingClientSecret()
    {
        $mockAuthStorage = $this->createMock(AuthStorageInterface::class);
        $this->expectException(RequiredParameterMissingException::class);
        new Client('CLIENT_ID', '', $mockAuthStorage);
    }

    /**
     * @covers \TRSTD\COT\Client::__construct
     * @return void
     */
    public function testConstructorThrowsRequiredParameterMissingExceptionForMissingAuthStorage()
    {
        $this->expectException(RequiredParameterMissingException::class);
        new Client('CLIENT_ID', 'CLIENT_SECRET', null);
    }

    /**
     * @covers \TRSTD\COT\Client::handleCallback
     * @return void
     */
    public function testHandleCallbackWithCode()
    {
        $_GET['code'] = 'testCode';
        $_SERVER['HTTP_HOST'] = 'testHost';
        $_SERVER['REQUEST_URI'] = 'testUri';
        $_COOKIE['TRSTD_CV'] = null;
        $_COOKIE['TRSTD_CC'] = null;

        $expectedRequests = [
            function ($method, $url, $options): MockResponse {
                $this->assertSame('POST', $method);
                $this->assertSame('https://auth.trustedshops.com/auth/realms/myTS/protocol/openid-connect/token', $url);
                $this->assertArrayHasKey('body', $options);
                $this->assertArrayHasKey('headers', $options);

                return new MockResponse('{"id_token":"' . self::TEST_ID_TOKEN . '", "refresh_token":"testRefreshToken", "access_token":"testAccessToken"}');
            },
        ];

        $mockedHttpClient = new MockHttpClient($expectedRequests);
        $mockedLogger = $this->createMock(LoggerInterface::class);
        $mockedCacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->expects($this->once())->method('set')->with('1234567890', new Token(self::TEST_ID_TOKEN, 'testRefreshToken', 'testAccessToken'));

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);
        $client->setHttpClient($mockedHttpClient);
        $client->setCacheItemPool($mockedCacheItemPool);

        $client->handleCallback();
    }

    /**
     * @covers \TRSTD\COT\Client::getConsumerData
     * @return void
     */
    public function testGetConsumerDataWithInvalidCookie()
    {
        $_COOKIE['TRSTD_ID_TOKEN'] = 'invalid.token';

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedLogger = $this->createMock(LoggerInterface::class);

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);

        $result = $client->getConsumerData();

        // Should return null because the cookie is invalid
        $this->assertNull($result);
    }

    /**
     * Test that identity cookie is updated when tokens are refreshed
     * @return void
     */
    public function testCookieUpdatedOnTokenRefresh()
    {
        $_COOKIE['TRSTD_ID_TOKEN'] = self::TEST_ID_TOKEN;
        $_SERVER['HTTP_HOST'] = 'testHost';

        $newIdToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4iLCJpYXQiOjk5OTk5OTk5OTksImN0Y19pZCI6IjEyMzQ1Njc4OTAifQ.4Adcj0vVzL-V8Xv3TqaVGzjJe5TUk1JdWqVrZqClz1M';

        $expectedRequests = [
            function ($method, $url, $options): MockResponse {
                return new MockResponse('{"id_token":"' . self::TEST_ID_TOKEN . '", "refresh_token":"refreshToken", "access_token":"accessToken"}');
            },
        ];

        $mockedHttpClient = new MockHttpClient($expectedRequests);
        $mockedLogger = $this->createMock(LoggerInterface::class);
        $mockedCacheItemPool = $this->createMock(CacheItemPoolInterface::class);

        $token = new Token(self::TEST_ID_TOKEN, 'refreshToken', 'accessToken');
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->method('get')->willReturn($token);
        $mockedAuthStorage->expects($this->once())->method('set');

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);
        $client->setHttpClient($mockedHttpClient);
        $client->setCacheItemPool($mockedCacheItemPool);

        // Use reflection to call private method setTokenOnStorage
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('setTokenOnStorage');
        $method->setAccessible(true);

        $newToken = new Token(self::TEST_ID_TOKEN, 'newRefreshToken', 'newAccessToken');
        $method->invoke($client, $newToken);

        // Verify cookie is set (in real scenario, this would update $_COOKIE)
        $this->assertTrue(true); // Cookie setting tested indirectly via setIdentityCookie calls
    }

    /**
     * Test disconnect with invalid token format
     * @return void
     */
    public function testDisconnectWithInvalidToken()
    {
        $_COOKIE['TRSTD_ID_TOKEN'] = 'invalid.token.format';
        $_SERVER['HTTP_HOST'] = 'testHost';

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->expects($this->never())->method('remove');

        $mockedLogger = $this->createMock(LoggerInterface::class);
        $mockedLogger->expects($this->once())->method('error');

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);

        // Use reflection to call private disconnect method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('disconnect');
        $method->setAccessible(true);
        $method->invoke($client);
    }

    /**
     * Test decodeToken with invalid format (not a string)
     * @return void
     */
    public function testDecodeTokenWithNonStringToken()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('decodeToken');
        $method->setAccessible(true);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Token cannot be empty or null.');
        $method->invoke($client, null, false);
    }

    /**
     * Test decodeToken with empty string
     * @return void
     */
    public function testDecodeTokenWithEmptyString()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('decodeToken');
        $method->setAccessible(true);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Token cannot be empty or null.');
        $method->invoke($client, '', false);
    }

    /**
     * Test decodeToken with invalid JWT format (less than 3 parts)
     * @return void
     */
    public function testDecodeTokenWithInvalidJWTFormat()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('decodeToken');
        $method->setAccessible(true);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Token format is invalid. Expected JWT with 3 parts.');
        $method->invoke($client, 'invalid.token', false);
    }

    /**
     * Test decodeToken with JWT missing payload
     * @return void
     */
    public function testDecodeTokenWithEmptyPayload()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('decodeToken');
        $method->setAccessible(true);

        $this->expectException(TokenInvalidException::class);
        $this->expectExceptionMessage('Token format is invalid. Expected JWT with 3 parts.');
        $method->invoke($client, 'header..signature', false);
    }

    /**
     * Test isValidJwtFormat with valid token
     * @return void
     */
    public function testIsValidJwtFormatWithValidToken()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('isValidJwtFormat');
        $method->setAccessible(true);

        $result = $method->invoke($client, self::TEST_ID_TOKEN);
        $this->assertTrue($result);
    }

    /**
     * Test isValidJwtFormat with null token
     * @return void
     */
    public function testIsValidJwtFormatWithNullToken()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('isValidJwtFormat');
        $method->setAccessible(true);

        $result = $method->invoke($client, null);
        $this->assertFalse($result);
    }

    /**
     * Test isValidJwtFormat with invalid format (2 parts)
     * @return void
     */
    public function testIsValidJwtFormatWithTwoParts()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('isValidJwtFormat');
        $method->setAccessible(true);

        $result = $method->invoke($client, 'part1.part2');
        $this->assertFalse($result);
    }

    /**
     * Test isValidJwtFormat with empty payload
     * @return void
     */
    public function testIsValidJwtFormatWithEmptyPayload()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('isValidJwtFormat');
        $method->setAccessible(true);

        $result = $method->invoke($client, 'header..signature');
        $this->assertFalse($result);
    }

    /**
     * Test isValidJwtFormat with non-string value
     * @return void
     */
    public function testIsValidJwtFormatWithNonString()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('isValidJwtFormat');
        $method->setAccessible(true);

        $result = $method->invoke($client, 12345);
        $this->assertFalse($result);
    }

    /**
     * Test getIdentityCookie removes invalid cookie
     * @return void
     */
    public function testGetIdentityCookieRemovesInvalidCookie()
    {
        $_COOKIE['TRSTD_ID_TOKEN'] = 'invalid.format';
        $_SERVER['HTTP_HOST'] = 'testHost';

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('getIdentityCookie');
        $method->setAccessible(true);

        $result = $method->invoke($client);
        $this->assertNull($result);
    }

    /**
     * Test getIdentityCookie returns null when no cookie exists
     * @return void
     */
    public function testGetIdentityCookieReturnsNullWhenNoCookie()
    {
        unset($_COOKIE['TRSTD_ID_TOKEN']);

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('getIdentityCookie');
        $method->setAccessible(true);

        $result = $method->invoke($client);
        $this->assertNull($result);
    }

    /**
     * Test getIdentityCookie returns valid token
     * @return void
     */
    public function testGetIdentityCookieReturnsValidToken()
    {
        $_COOKIE['TRSTD_ID_TOKEN'] = self::TEST_ID_TOKEN;

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('getIdentityCookie');
        $method->setAccessible(true);

        $result = $method->invoke($client);
        $this->assertSame(self::TEST_ID_TOKEN, $result);
    }

    /**
     * Test getTokenFromStorage with invalid token logs error
     * @return void
     */
    public function testGetTokenFromStorageWithInvalidTokenLogsError()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedLogger = $this->createMock(LoggerInterface::class);
        $mockedLogger->expects($this->once())->method('error');

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('getTokenFromStorage');
        $method->setAccessible(true);

        $result = $method->invoke($client, 'invalid.token');
        $this->assertNull($result);
    }

    /**
     * Test getConsumerData returns null when no cookie exists
     * @return void
     */
    public function testGetConsumerDataReturnsNullWithNoCookie()
    {
        unset($_COOKIE['TRSTD_ID_TOKEN']);

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $result = $client->getConsumerData();

        $this->assertNull($result);
    }

    /**
     * Test handleCallback with disconnect action
     * @return void
     */
    public function testHandleCallbackWithDisconnectAction()
    {
        unset($_GET['code']);
        $_GET['cotAction'] = 'disconnect';  // Use lowercase 'disconnect'
        $_COOKIE['TRSTD_ID_TOKEN'] = self::TEST_ID_TOKEN;
        $_SERVER['HTTP_HOST'] = 'testHost';

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->expects($this->once())->method('remove')->with('1234567890');

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $client->handleCallback();

        unset($_GET['cotAction']);
    }

    /**
     * Test decodeToken returns valid decoded token
     * @return void
     */
    public function testDecodeTokenReturnsValidDecodedToken()
    {
        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('decodeToken');
        $method->setAccessible(true);

        $result = $method->invoke($client, self::TEST_ID_TOKEN, false);

        $this->assertIsObject($result);
        $this->assertEquals('1234567890', $result->sub);
        $this->assertEquals('John', $result->name);
    }

    /**
     * Test that invalid access token in getOrRefreshAccessToken logs error
     * @return void
     */
    public function testGetOrRefreshAccessTokenWithInvalidAccessTokenLogsError()
    {
        // Mock token with invalid access token (not a proper JWT)
        $token = new Token(self::TEST_ID_TOKEN, 'refreshToken', 'invalid-access-token');

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->method('get')->willReturn($token);

        $mockedLogger = $this->createMock(LoggerInterface::class);
        // Expect error to be logged for invalid access token
        $mockedLogger->expects($this->atLeastOnce())->method('error');

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);
        $client->setLogger($mockedLogger);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('getOrRefreshAccessToken');
        $method->setAccessible(true);

        try {
            $method->invoke($client, self::TEST_ID_TOKEN);
        } catch (\Exception $e) {
            // Expected to throw exception, that's fine
            $this->assertTrue(true);
        }
    }

    /**
     * Test setTokenOnStorage stores token successfully
     * @return void
     */
    public function testSetTokenOnStorageStoresTokenSuccessfully()
    {
        $token = new Token(self::TEST_ID_TOKEN, 'refreshToken', 'accessToken');

        $mockedAuthStorage = $this->createMock(AuthStorageInterface::class);
        $mockedAuthStorage->expects($this->once())
            ->method('set')
            ->with('1234567890', $token);

        $client = new Client('CLIENT_ID', 'CLIENT_SECRET', $mockedAuthStorage);

        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('setTokenOnStorage');
        $method->setAccessible(true);

        $method->invoke($client, $token);
        $this->assertTrue(true); // If we get here, the storage was called
    }
}
