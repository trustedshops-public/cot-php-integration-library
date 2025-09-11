<?php

declare(strict_types=1);

namespace TRSTD\COT;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use TRSTD\COT\AuthStorageInterface;
use TRSTD\COT\Token;
use TRSTD\COT\ActionType;
use TRSTD\COT\ConsumerData;
use TRSTD\COT\Exception\UnexpectedErrorException;
use TRSTD\COT\Exception\RequiredParameterMissingException;
use TRSTD\COT\Exception\TokenInvalidException;
use TRSTD\COT\Exception\TokenNotFoundException;
use TRSTD\COT\Util\EncryptionUtils;
use TRSTD\COT\Util\PKCEUtils;
use TRSTD\COT\Cache\SimpleArrayCachePool;

CacheManager::setDefaultConfig(new ConfigurationOption([
    "path" => __DIR__ . "/cache"
]));

final class Client
{
    private const IDENTITY_COOKIE_KEY = 'TRSTD_ID_TOKEN';
    private const CODE_VERIFIER_COOKIE_KEY = 'TRSTD_CV';
    private const CODE_CHALLENGE_COOKIE_KEY = 'TRSTD_CC';

    private const JWKS_CACHE_KEY = 'JWKS';
    private const JWKS_CACHE_TTL = 3600; // 1 hour

    private const CONSUMER_DATA_CACHE_KEY = 'CONSUMER_DATA_';
    private const CONSUMER_DATA_CACHE_TTL = 3600; // 1 hour

    private const AUTH_SERVER_BASE_URI_DEV = 'https://auth-integr.trustedshops.com/auth/realms/myTS-DEV/protocol/openid-connect/';
    private const AUTH_SERVER_BASE_URI_QA = 'https://auth-qa.trustedshops.com/auth/realms/myTS-QA/protocol/openid-connect/';
    private const AUTH_SERVER_BASE_URI_PROD = 'https://auth.trustedshops.com/auth/realms/myTS/protocol/openid-connect/';

    private const RESOURCE_SERVER_BASE_URI_DEV = 'https://scoped-cns-data.consumer-account-dev.trustedshops.com/api/v1/';
    private const RESOURCE_SERVER_BASE_URI_QA = 'https://scoped-cns-data.consumer-account-test.trustedshops.com/api/v1/';
    private const RESOURCE_SERVER_BASE_URI_PROD = 'https://scoped-cns-data.consumer-account.trustedshops.com/api/v1/';

    /**
     * @var CacheItemPoolInterface|null
     */
    private static $sharedCacheInstance = null;

    /**
     * @var string
     */
    private $authServerBaseUri;

    /**
     * @var string
     */
    private $resourceServerBaseUri;

    /**
     * @var string
     */
    private $tsId;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var AuthStorageInterface
     */
    private $authStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * Get or create a shared cache instance to avoid calling CacheManager::getInstance() multiple times
     * @return CacheItemPoolInterface
     */
    private static function getSharedCacheInstance(): CacheItemPoolInterface
    {
        if (self::$sharedCacheInstance === null) {
            self::$sharedCacheInstance = CacheManager::getInstance('files');
        }
        return self::$sharedCacheInstance;
    }

    /**
     * Clear the shared cache instance (useful for testing or forcing recreation)
     * @return void
     */
    public static function clearSharedCacheInstance(): void
    {
        self::$sharedCacheInstance = null;
    }

    /**
     * @param string $tsId TS ID
     * @param string $clientId client ID
     * @param string $clientSecret client secret
     * @param AuthStorageInterface|null $authStorage auth storage to store tokens
     * @param string $env environment dev, qa, or prod
     * @throws RequiredParameterMissingException if any required parameter is missing
     */
    public function __construct($tsId, $clientId, $clientSecret, ?AuthStorageInterface $authStorage = null, $env = 'prod')
    {
        if (!$tsId) {
            throw new RequiredParameterMissingException('TS ID is required.');
        }

        if (!$clientId) {
            throw new RequiredParameterMissingException('Client ID is required.');
        }

        if (!$clientSecret) {
            throw new RequiredParameterMissingException('Client Secret is required.');
        }

        if (!$authStorage) {
            throw new RequiredParameterMissingException('AuthStorage is required.');
        }

        $this->authServerBaseUri = $this->getAuthServerBaseUri($env);
        $this->resourceServerBaseUri = $this->getResourceServerBaseUri($env);

        $this->tsId = $tsId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authStorage = $authStorage;

        $this->logger = new Logger("TRSTD/COT");
        $this->httpClient = HttpClient::create();
        $this->cacheItemPool = self::getSharedCacheInstance();
    }

    /**
     * Handles the callback from the auth server
     * @return void
     */
    public function handleCallback()
    {
        if (isset($_GET['code'])) {
            $this->handleAuthCode($_GET['code']);
        } elseif (isset($_GET['cotAction'])) {
            $this->handleAction($_GET['cotAction']);
        }

        $this->refreshPKCE(false);
    }

    /**
     * returns the consumer data for the connected community user if any
     * @return ConsumerData|null
     */
    public function getConsumerData()
    {
        try {
            $idToken = $this->getIdentityCookie();
            if (!$idToken) {
                return null;
            }
            
            $accessToken = $this->getOrRefreshAccessToken($idToken);
            $decodedToken = $this->decodeToken($idToken, false);

            // check if the consumer data is cached
            $cachedConsumerDataItem = $this->cacheItemPool->getItem(self::CONSUMER_DATA_CACHE_KEY . $decodedToken->sub);
            if ($cachedConsumerDataItem->isHit()) {
                return $cachedConsumerDataItem->get();
            }

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $response = $this->httpClient->request("GET", "consumer-data" . ($this->tsId ? "?shopId=" . $this->tsId : ""), ['headers' => $headers, 'base_uri' => $this->resourceServerBaseUri]);
            $consumerData = json_decode($response->getContent());

            // cache the consumer data
            $cachedConsumerDataItem->set($consumerData)->expiresAfter(self::CONSUMER_DATA_CACHE_TTL);
            $this->cacheItemPool->save($cachedConsumerDataItem);

            return $consumerData;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return null;
        }
    }



    /**
     * @param LoggerInterface $logger logger to set
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param HttpClientInterface $httpClient http client to set
     * @return void
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param CacheItemPoolInterface $cacheItemPool cache item pool to set
     * @return void
     */
    public function setCacheItemPool(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @param string $env environment dev, qa, or prod
     * @return string
     */
    private function getAuthServerBaseUri($env = 'prod')
    {
        if ($env === 'dev') {
            return self::AUTH_SERVER_BASE_URI_DEV;
        } elseif ($env === 'qa') {
            return self::AUTH_SERVER_BASE_URI_QA;
        } elseif ($env === 'prod') {
            return self::AUTH_SERVER_BASE_URI_PROD;
        }

        throw new Exception("Invalid environment.");
    }

    /**
     * @param string $env environment dev, qa, or prod
     * @return string
     */
    private function getResourceServerBaseUri($env = 'prod')
    {
        if ($env === 'dev') {
            return self::RESOURCE_SERVER_BASE_URI_DEV;
        } elseif ($env === 'qa') {
            return self::RESOURCE_SERVER_BASE_URI_QA;
        } elseif ($env === 'prod') {
            return self::RESOURCE_SERVER_BASE_URI_PROD;
        }

        throw new Exception("Invalid environment.");
    }

    /**
     * @param string $code code to exchange for token
     * @return Token|null
     */
    private function connect($code)
    {
        $token = $this->getToken($code);
        if (!$token) {
            return null;
        }

        $this->refreshPKCE(true);
        $this->setTokenOnStorage($token);

        return $token;
    }

    /**
     * @return void
     */
    private function disconnect()
    {
        $idToken = $this->getIdentityCookie();
        if ($idToken) {
            $decodedToken = $this->decodeToken($idToken, false);
            $this->authStorage->remove($decodedToken->sub);
            $this->removeIdentityCookie();
        }
    }

    /**
     * @param string $code code to get token
     * @return Token|null
     */
    private function getToken($code)
    {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => "https://" . strtok($_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"], '?'),
            'code' => $code,
            'code_verifier' => $this->getCodeVerifierCookie(),
        ];

        $response = $this->httpClient->request("POST", "token", ['headers' => $headers, 'body' => $data, 'base_uri' => $this->authServerBaseUri]);
        $responseJson = json_decode($response->getContent());
        if (!$responseJson || isset($responseJson->error)) {
            return null;
        }

        return new Token($responseJson->id_token, $responseJson->refresh_token, $responseJson->access_token);
    }

    /**
     * @param string $refreshToken
     * @return Token|null
     */
    private function getRefreshedToken($refreshToken)
    {
        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $data = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ];

        $response = $this->httpClient->request("POST", "token", ['headers' => $headers, 'body' => $data, 'base_uri' => $this->authServerBaseUri]);
        $responseJson = json_decode($response->getContent());
        if (!$responseJson || isset($responseJson->error)) {
            return null;
        }

        return new Token($responseJson->id_token, $responseJson->refresh_token, $responseJson->access_token);
    }

    /**
     * @param string $idToken id token to get or refresh access token
     * @return string
     * @throws TokenNotFoundException if a valid token cannot be found in storage
     * @throws TokenInvalidException if the token is invalid
     * @throws UnexpectedErrorException if an unexpected error occurs
     */
    private function getOrRefreshAccessToken($idToken)
    {
        $token = $this->getTokenFromStorage($idToken);

        if ($token) {
            $shouldRefresh = false;

            try {
                if ($token->accessToken) {
                    $this->logger->debug('access token is in storage. verifying...');
                    $this->decodeToken($token->accessToken);
                } else {
                    $this->logger->debug('access token cannot be found. refreshing...');
                    $shouldRefresh = true;
                }
            } catch (ExpiredException $ex) {
                $this->logger->debug('access token is expired. refreshing...');
                $shouldRefresh = true;
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                throw new UnexpectedErrorException('Unexpected error occurred: ' . $ex->getMessage(), 0, $ex);
            }

            if ($shouldRefresh) {
                try {
                    $refreshedToken = $this->getRefreshedToken($token->refreshToken);

                    $token->accessToken = $refreshedToken->accessToken;
                    $this->setTokenOnStorage($refreshedToken);
                    $this->logger->debug('Access token is refreshed. returning...');

                    return $token->accessToken;
                } catch (Exception $ex) {
                    $this->logger->debug('Error occurred while refreshing the token: ' . $ex->getMessage());
                    $this->removeIdentityCookie();
                    throw $ex;
                }
            }

            $this->logger->debug('Access token is valid. returning...');
            return $token->accessToken;
        }

        throw new TokenNotFoundException('A valid token cannot be found in storage. Authentication is required.');
    }

    /**
     * @param Token $token token to set in storage
     * @return void
     */
    private function setTokenOnStorage(Token $token)
    {
        try {
            $decodedToken = $this->decodeToken($token->idToken, false);
            $this->authStorage->set($decodedToken->sub, $token);
        } catch (ExpiredException $ex) {
            $this->logger->debug('id token is expired. returning...');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw new UnexpectedErrorException('Unexpected error occurred.: ' . $ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @param string $idToken id token to get token from storage
     * @return Token|null
     */
    private function getTokenFromStorage($idToken)
    {
        try {
            $decodedToken = $this->decodeToken($idToken, false);
            return $this->authStorage->get($decodedToken->sub);
        } catch (ExpiredException $ex) {
            $this->logger->debug('id token is expired. returning...');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw new UnexpectedErrorException('Unexpected error occurred: ' . $ex->getMessage(), 0, $ex);
        }

        return null;
    }

    /**
     * @param string $token token to decode
     * @param bool $validateExp if true, validates the expiration time
     * @return object decoded token
     */
    private function decodeToken($token, $validateExp = true)
    {
        if (!$token) {
            throw new TokenInvalidException('Token cannot be empty or null.');
        }

        if (!$validateExp) {
            $tks = explode('.', $token);
            return JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        }

        return JWT::decode($token, $this->getJWKS());
    }

    /**
     * @return array<string, \Firebase\JWT\Key> JWKS
     */
    private function getJWKS()
    {
        $cachedJWKSItem = $this->cacheItemPool->getItem(self::JWKS_CACHE_KEY);

        if (!$cachedJWKSItem->isHit()) {
            $response = $this->httpClient->request("GET", "certs", ['base_uri' => $this->authServerBaseUri]);
            $jwks = json_decode($response->getContent(), true);
            $cachedJWKSItem->set($jwks)->expiresAfter(self::JWKS_CACHE_TTL);
            $this->cacheItemPool->save($cachedJWKSItem);
        }

        $jwks = $cachedJWKSItem->get();

        return JWK::parseKeySet($jwks);
    }

    /**
     * @param string $code code to handle
     * @return void
     */
    private function handleAuthCode($code)
    {
        $token = $this->connect($code);

        if ($token) {
            $this->setIdentityCookie($token->idToken);
        }
    }

    /**
     * @param string $actionType action type to handle
     * @return void
     */
    private function handleAction($actionType)
    {
        if ($actionType === ActionType::DISCONNECT) {
            $this->disconnect();
        }
    }

    /**
     * @return string|null
     */
    private function getIdentityCookie()
    {
        return $_COOKIE[self::IDENTITY_COOKIE_KEY] ?? null;
    }

    /**
     * @param string $idToken id token to set in cookie
     * @return void
     */
    private function setIdentityCookie($idToken)
    {
        if (!headers_sent()) {
            setcookie(self::IDENTITY_COOKIE_KEY, $idToken, strtotime("2038-01-1 00:00:00"), '/', $this->getCookieDomain(), true, false);
        }
    }

    /**
     * @return void
     */
    private function removeIdentityCookie()
    {
        if (!headers_sent()) {
            setcookie(self::IDENTITY_COOKIE_KEY, '', time() - 3600, '/', $this->getCookieDomain(), true, false);
        }
    }

    /**
     * @param string $codeVerifier code verifier to set in cookie
     * @param string $codeChallenge code challenge to set in cookie
     * @return void
     */
    private function setCodeVerifierAndChallengeCookie($codeVerifier, $codeChallenge)
    {
        $encryptedCodeVerifier = EncryptionUtils::encryptValue($this->clientSecret, $codeVerifier);
        if (!headers_sent()) {
            setcookie(self::CODE_VERIFIER_COOKIE_KEY, $encryptedCodeVerifier, 0, '/', $this->getCookieDomain(), true, true);
            setcookie(self::CODE_CHALLENGE_COOKIE_KEY, $codeChallenge, 0, '/', $this->getCookieDomain(), true, false);
        }
    }

    /**
     * @param bool $force if true, refreshes the PKCE even if it is already set
     * @return void
     */
    private function refreshPKCE($force = false)
    {
        if ($force || !isset($_COOKIE[self::CODE_VERIFIER_COOKIE_KEY]) || !isset($_COOKIE[self::CODE_CHALLENGE_COOKIE_KEY])) {
            $codeVerifier = PKCEUtils::generateCodeVerifier();
            $codeChallenge = PKCEUtils::generateCodeChallenge($codeVerifier);
            $this->setCodeVerifierAndChallengeCookie($codeVerifier, $codeChallenge);
        }
    }

    /**
     * @return string|null
     */
    private function getCodeVerifierCookie()
    {
        $encryptedCodeVerifier = $_COOKIE[self::CODE_VERIFIER_COOKIE_KEY] ?? null;

        if ($encryptedCodeVerifier) {
            return EncryptionUtils::decryptValue($this->clientSecret, $encryptedCodeVerifier);
        }

        return null;
    }

    /**
     * Get the cookie domain by removing port from HTTP_HOST if present
     * @return string
     */
    private function getCookieDomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Remove port if present (e.g., "example.com:8080" becomes "example.com")
        return explode(':', $host)[0];
    }
}
