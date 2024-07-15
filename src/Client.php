<?php

declare(strict_types=1);

namespace TRSTD\COT;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Config\ConfigurationOption;

use TRSTD\COT\Logger;
use TRSTD\COT\AuthStorageInterface;
use TRSTD\COT\Token;
use TRSTD\COT\ActionType;
use TRSTD\COT\AnonymousConsumerData;
use TRSTD\COT\Exception\UnexpectedErrorException;
use TRSTD\COT\Exception\RequiredParameterMissingException;
use TRSTD\COT\Exception\TokenNotFoundException;
use TRSTD\COT\Util\EncryptionUtils;
use TRSTD\COT\Util\PKCEUtils;

if (!defined('AUTH_SERVER_BASE_URI')) {
    define('AUTH_SERVER_BASE_URI', 'https://auth-qa.trustedshops.com/auth/realms/myTS-QA/protocol/openid-connect/');
}

if (!defined('RESOURCE_SERVER_BASE_URI')) {
    define('RESOURCE_SERVER_BASE_URI', 'https://scoped-cns-data.consumer-account-test.trustedshops.com/api/v1/');
}

CacheManager::setDefaultConfig(new ConfigurationOption([
    "path" => __DIR__ . "/cache"
]));

class Client
{
    private const IDENTITY_COOKIE_KEY = 'TRSTD_ID_TOKEN';
    private const CODE_VERIFIER_COOKIE_KEY = 'TRSTD_CV';
    private const CODE_CHALLENGE_COOKIE_KEY = 'TRSTD_CC';

    private const JWKS_CACHE_KEY = 'JWKS';
    private const JWKS_CACHE_TTL = 3600; // 1 hour

    private const CONSUMER_ANONYMOUS_DATA_CACHE_KEY = 'CONSUMER_ANONYMOUS_DATA_';
    private const CONSUMER_ANONYMOUS_DATA_CACHE_TTL = 3600; // 1 hour

    /**
     * @var AuthStorageInterface
     */
    private $authStorage;

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
     * @var Logger
     */
    private $logger;

    /**
     * @var HttpClientInterface
     */
    private $authHttpClient;

    /**
     * @var HttpClientInterface
     */
    private $resourceHttpClient;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @param string $tsId TS ID
     * @param string $clientId client ID
     * @param string $clientSecret client secret
     * @param AuthStorageInterface $authStorage auth storage to store tokens
     * @throws RequiredParameterMissingException if any required parameter is missing
     */
    public function __construct($tsId, $clientId, $clientSecret, AuthStorageInterface $authStorage)
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

        $this->tsId = $tsId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authStorage = $authStorage;
        $this->logger = new Logger();

        $this->authHttpClient = HttpClient::createForBaseUri(AUTH_SERVER_BASE_URI);
        $this->resourceHttpClient = HttpClient::createForBaseUri(RESOURCE_SERVER_BASE_URI);

        $this->cacheItemPool = CacheManager::getInstance('files');
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
     * returns the anonymous consumer data for the connected community user if any
     * @return AnonymousConsumerData|null
     */
    public function getAnonymousConsumerData()
    {
        try {
            $idToken = $this->getIdentityCookie();
            $accessToken = $this->getOrRefreshAccessToken($idToken);
            $decodedToken = $this->decodeToken($idToken, false);

            // check if the consumer anonymous data is cached
            $cachedConsumerAnonymousDataItem = $this->cacheItemPool->getItem(self::CONSUMER_ANONYMOUS_DATA_CACHE_KEY . $decodedToken->ctc_id);
            if ($cachedConsumerAnonymousDataItem->isHit()) {
                return $cachedConsumerAnonymousDataItem->get();
            }

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $response = $this->resourceHttpClient->request("GET", "anonymous-data" . ($this->tsId ? "?shopId=" . $this->tsId : ""), ['headers' => $headers]);
            $consumerAnonymousData = json_decode($response->getContent());

            // cache the consumer anonymous data
            // TODO remove the caching feature when the pilot phase is over
            $cachedConsumerAnonymousDataItem->set($consumerAnonymousData)->expiresAfter(self::CONSUMER_ANONYMOUS_DATA_CACHE_TTL);
            $this->cacheItemPool->save($cachedConsumerAnonymousDataItem);

            return $consumerAnonymousData;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return null;
        }
    }

    /**
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
        if (isset($idToken)) {
            $decodedToken = $this->decodeToken($idToken, false);
            $this->authStorage->remove($decodedToken->ctc_id);
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

        $response = $this->authHttpClient->request("POST", "token", ['headers' => $headers, 'body' => $data]);
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

        $response = $this->authHttpClient->request("POST", "token", ['headers' => $headers, 'body' => $data]);
        $responseJson = json_decode($response->getContent());
        if (!$responseJson || isset($responseJson->error)) {
            return null;
        }

        return new Token($responseJson->id_token, $responseJson->refresh_token, $responseJson->access_token);
    }

    /**
     * @param string $idTokenid token to get or refresh access token
     * @return string
     * @throws TokenNotFoundException if a valid token cannot be found in storage
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
                $refreshedToken = $this->getRefreshedToken($token->refreshToken);

                if (!$refreshedToken) {
                    $this->logger->debug('Refresh token is invalid.');
                    return null;
                }

                $token->accessToken = $refreshedToken->accessToken;
                $this->setTokenOnStorage($refreshedToken);
                $this->logger->debug('Access token is refreshed. returning...');

                return $token->accessToken;
            }

            $this->logger->debug('Access token is valid. returning...');
            return $token->accessToken;
        }

        throw new TokenNotFoundException('A valid token cannot be found in storage. Authentication is required.');
    }

    /**
     * @param Token $token token to set in storage
     */
    private function setTokenOnStorage(Token $token)
    {
        try {
            $decodedToken = $this->decodeToken($token->idToken, false);
            $this->authStorage->set($decodedToken->ctc_id, $token);
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
            return $this->authStorage->get($decodedToken->ctc_id);
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
        if (!$validateExp) {
            $tks = explode('.', $token);
            return JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        }

        return JWT::decode($token, $this->getJWKS());
    }

    /**
     * @return JWK
     */
    private function getJWKS()
    {
        $cachedJWKSItem = $this->cacheItemPool->getItem(self::JWKS_CACHE_KEY);

        if (!$cachedJWKSItem->isHit()) {
            $response = $this->authHttpClient->request("GET", "certs");
            $jwks = json_decode($response->getContent(), true);
            $cachedJWKSItem->set($jwks)->expiresAfter(self::JWKS_CACHE_TTL);
            $this->cacheItemPool->save($cachedJWKSItem);
        }

        $jwks = $cachedJWKSItem->get();

        return JWK::parseKeySet($jwks);
    }

    /**
     * @param string $code code to handle
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
     */
    private function handleAction($actionType)
    {
        if ($actionType === ActionType::DISCONNECT) {
            $this->disconnect();
        }
    }

    /**
     * @return string
     */
    private function getIdentityCookie()
    {
        return $_COOKIE[self::IDENTITY_COOKIE_KEY];
    }

    /**
     * @param string $idToken id token to set in cookie
     */
    private function setIdentityCookie($idToken)
    {
        setcookie(self::IDENTITY_COOKIE_KEY, $idToken, strtotime("2038-01-1 00:00:00"), '/',  $_SERVER['HTTP_HOST'], true, false);
    }

    /**
     * @return void
     */
    private function removeIdentityCookie()
    {
        setcookie(self::IDENTITY_COOKIE_KEY, '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, false);
    }

    /**
     * @param string $codeVerifier code verifier to set in cookie
     * @param string $codeChallenge code challenge to set in cookie
     * @return string
     */
    private function setCodeVerifierAndChallengeCookie($codeVerifier, $codeChallenge)
    {
        $encryptedCodeVerifier = EncryptionUtils::encryptValue($this->clientSecret, $codeVerifier);
        setcookie(self::CODE_VERIFIER_COOKIE_KEY, $encryptedCodeVerifier, 0, '/', $_SERVER['HTTP_HOST'], true, true);
        setcookie(self::CODE_CHALLENGE_COOKIE_KEY, $codeChallenge, 0, '/', $_SERVER['HTTP_HOST'], true, false);
    }

    /**
     * @param bool $force if true, refreshes the PKCE even if it is already set
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
        $encryptedCodeVerifier = $_COOKIE[self::CODE_VERIFIER_COOKIE_KEY];

        if ($encryptedCodeVerifier) {
            return EncryptionUtils::decryptValue($this->clientSecret, $encryptedCodeVerifier);
        }

        return null;
    }
}
