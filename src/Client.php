<?php

namespace COT;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;

use COT\Logger;
use COT\HttpClient;
use COT\AuthStorage;
use COT\Token;
use COT\ActionType;
use COT\AnonymousConsumerData;
use COT\Exception\UnexpectedErrorException;
use COT\Exception\RequiredParameterMissingException;

if (!defined('URL_REALM')) {
    define('URL_REALM', 'https://auth-qa.trustedshops.com/auth/realms/myTS-QA');
}

if (!defined('PROTOCOL')) {
    define('PROTOCOL', 'protocol/openid-connect');
}

if (!defined('ENDPOINT_TOKEN')) {
    define('ENDPOINT_TOKEN', URL_REALM . '/' . PROTOCOL . '/token');
}

if (!defined('ENDPOINT_CERTS')) {
    define('ENDPOINT_CERTS', URL_REALM . '/' . PROTOCOL . '/certs');
}

if (!defined('ENDPOINT_ANONYMOUS_DATA')) {
    define('ENDPOINT_ANONYMOUS_DATA', 'https://scoped-cns-data.consumer-account-test.trustedshops.com/api/v1/anonymous-data');
}

class Client
{
    private static $identityCookie = 'TRSTD_ID_TOKEN';
    private static $codeVerifierCookie = 'TRSTD_CV';
    private static $codeChallengeCookie = 'TRSTD_CC';

    /**
     * @var object
     */
    private $certificateCache;

    /**
     * @var AuthStorage
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
     * @param string $tsId - TS ID
     * @param string $clientId - client ID
     * @param string $clientSecret - client secret
     * @param AuthStorage $authStorage - auth storage instance
     * @throws RequiredParameterMissingException - if any required parameter is missing
     */
    public function __construct($tsId, $clientId, $clientSecret, $authStorage)
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
    }

    /**
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
     * @param string $idToken
     * @return AnonymousConsumerData|null
     */
    public function getAnonymousConsumerData($idToken)
    {
        try {
            $accessToken = $this->getOrRefreshAccessToken($idToken);

            if (!$accessToken) {
                return null;
            }

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            return HttpClient::get(ENDPOINT_ANONYMOUS_DATA . ($this->tsId ? "?shopId=" . $this->tsId : ""), $headers);
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
        if (isset($_COOKIE[self::$identityCookie])) {
            $idToken = $_COOKIE[self::$identityCookie];
            $decodedToken = $this->decodeToken($idToken, false);
            $this->authStorage->remove($decodedToken->ctc_id);
            $this->removeIdentityCookie();
        }
    }

    /**
     * @param string $code - code to get token
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

        $responseJson = HttpClient::post(ENDPOINT_TOKEN, $headers, $data);
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

        $responseJson = HttpClient::post(ENDPOINT_TOKEN, $headers, $data);
        if (!$responseJson || isset($responseJson->error)) {
            return null;
        }

        return new Token($responseJson->id_token, $responseJson->refresh_token, $responseJson->access_token);
    }

    /**
     * @param string $token - token to decode
     * @param bool $validateExp - if true, validates expiration
     * @return object
     */
    private function decodeToken($token, $validateExp = true)
    {
        if ($this->certificateCache === null) {
            $this->certificateCache = HttpClient::get(ENDPOINT_CERTS);
        }

        $key = $this->certificateCache->keys[0];
        $pem = $this->jwkToPem($key);

        //TODO: find a way to validate token without validating exp
        if (!$validateExp) {
            $tks = explode('.', $token);
            return JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        }

        return JWT::decode($token, new Key($pem, $key->alg));
    }

    /**
     * @param object $jwk
     * @return string
     */
    private function jwkToPem($jwk)
    {
        $n = new BigInteger(base64_decode(strtr($jwk->n, '-_', '+/')), 256);
        $e = new BigInteger(base64_decode(strtr($jwk->e, '-_', '+/')), 256);

        $publicKey = PublicKeyLoader::load(['n' => $n, 'e' => $e]);

        return $publicKey->toString('PKCS8');
    }

    /**
     * @param string $idToken - id token to get or refresh access token
     * @return string|null
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

        return null;
    }

    /**
     * @param Token $token - token to set in storage
     */
    private function setTokenOnStorage(Token $token)
    {
        try {
            $decodedToken = $this->decodeToken($token->idToken, false);
            $this->authStorage->set($token, $decodedToken->ctc_id);
        } catch (ExpiredException $ex) {
            $this->logger->debug('id token is expired. returning...');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw new UnexpectedErrorException('Unexpected error occurred.: ' . $ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @param string $idToken - id token to get token from storage
     * @return Token|null
     */
    private function getTokenFromStorage($idToken)
    {
        try {
            $decodedToken = $this->decodeToken($idToken, false);
            $ctcId = $decodedToken->ctc_id;
            $cotAuth = $this->authStorage->getByCtcId($ctcId);
            return $cotAuth ? $cotAuth->toToken() : null;
        } catch (ExpiredException $ex) {
            $this->logger->debug('id token is expired. returning...');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw new UnexpectedErrorException('Unexpected error occurred: ' . $ex->getMessage(), 0, $ex);
        }

        return null;
    }

    /**
     * @param string $code - code to handle
     */
    private function handleAuthCode($code)
    {
        $token = $this->connect($code);

        if ($token) {
            $this->setIdentityCookie($token->idToken);
        }
    }

    /**
     * @param string $actionType - action type to handle
     */
    private function handleAction($actionType)
    {
        if ($actionType === ActionType::DISCONNECT) {
            $this->disconnect();
        }
    }

    /**
     * @param string $idToken - id token to set in cookie
     */
    private function setIdentityCookie($idToken)
    {
        setcookie(self::$identityCookie, $idToken, strtotime("2038-01-1 00:00:00"), '/',  $_SERVER['HTTP_HOST'], true, false);
    }

    /**
     * @param string $codeVerifier - code verifier to set in cookie
     * @param string $codeChallenge - code challenge to set in cookie
     */
    private function removeIdentityCookie()
    {
        setcookie(self::$identityCookie, '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, false);
    }

    /**
     * @return string
     */
    private function generateCodeVerifier()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * @param string $codeVerifier - code verifier to generate challenge
     * @return string
     */
    private function generateCodeChallenge($codeVerifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    /**
     * @param string $codeVerifier - code verifier to set in cookie
     * @param string $codeChallenge - code challenge to set in cookie
     * @return string
     */
    private function setCodeVerifierAndChallengeCookie($codeVerifier, $codeChallenge)
    {
        $encryptedCodeVerifier = $this->encryptValue($this->clientSecret, $codeVerifier);
        setcookie(self::$codeVerifierCookie, $encryptedCodeVerifier, 0, '/', $_SERVER['HTTP_HOST'], true, true);
        setcookie(self::$codeChallengeCookie, $codeChallenge, 0, '/', $_SERVER['HTTP_HOST'], true, false);
    }

    /**
     * @param bool $force - if true, refreshes the PKCE even if it is already set
     */
    private function refreshPKCE($force = false)
    {
        if ($force || !isset($_COOKIE[self::$codeVerifierCookie]) || !isset($_COOKIE[self::$codeChallengeCookie])) {
            $codeVerifier = $this->generateCodeVerifier();
            $codeChallenge = $this->generateCodeChallenge($codeVerifier);
            $this->setCodeVerifierAndChallengeCookie($codeVerifier, $codeChallenge);
        }
    }

    /**
     * @return string|null
     */
    private function getCodeVerifierCookie()
    {
        $encryptedCodeVerifier = $_COOKIE[self::$codeVerifierCookie];

        if ($encryptedCodeVerifier) {
            return $this->decryptValue($this->clientSecret, $encryptedCodeVerifier);
        }

        return null;
    }

    /**
     * @param string $key - key used for encryption
     * @param string $value - value to encrypt
     * @return string
     */
    private function encryptValue($key, $value)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param string $key - key used for encryption
     * @param string $value - value to decrypt
     * @return string
     */
    private function decryptValue($key, $value)
    {
        list($encryptedData, $iv) = explode('::', base64_decode($value), 2);
        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }
}
