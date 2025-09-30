<?php

declare(strict_types=1);

namespace TRSTD\COT\Util;

use TRSTD\COT\AuthStorageInterface;
use TRSTD\COT\Token;

/**
 * Session-based token storage implementation
 *
 * This class stores authentication tokens in PHP sessions,
 * making them accessible to the backend for API calls.
 */
class SessionTokenStorage implements AuthStorageInterface
{
    private string $sessionKey;

    public function __construct(string $sessionKey = 'trstd_tokens')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->sessionKey = $sessionKey;
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }

    /**
     * Get authentication token from session storage.
     *
     * @param string $sub The user ID (subject from JWT)
     * @return Token|null The token if found, null otherwise
     */
    public function get($sub): ?Token
    {
        if (!isset($_SESSION[$this->sessionKey][$sub])) {
            return null;
        }

        $tokenData = $_SESSION[$this->sessionKey][$sub];

        // We need at least an access token
        if (!isset($tokenData['access_token'])) {
            return null;
        }

        // Get ID token from stored data
        $idToken = $tokenData['id_token'] ?? '';

        return new Token(
            $idToken,
            $tokenData['refresh_token'] ?? '',
            $tokenData['access_token']
        );
    }

    /**
     * Set authentication token in session storage.
     *
     * @param string $sub The user ID (subject from JWT)
     * @param Token $token The token to store
     */
    public function set($sub, Token $token): void
    {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }

        $_SESSION[$this->sessionKey][$sub] = [
            'id_token' => $token->idToken,
            'refresh_token' => $token->refreshToken,
            'access_token' => $token->accessToken,
            'stored_at' => time()
        ];
    }

    /**
     * Remove authentication token from session storage.
     *
     * @param string $sub The user ID (subject from JWT)
     */
    public function remove($sub): void
    {
        if (isset($_SESSION[$this->sessionKey][$sub])) {
            unset($_SESSION[$this->sessionKey][$sub]);
        }
    }

    /**
     * Check if user is authenticated (has valid tokens).
     *
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated(): bool
    {
        // Check if we have any tokens stored in session
        if (empty($_SESSION[$this->sessionKey])) {
            return false;
        }

        // Check if we have at least one user with valid tokens
        foreach ($_SESSION[$this->sessionKey] as $userId => $tokenData) {
            if (isset($tokenData['access_token']) && !empty($tokenData['access_token'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear all stored tokens.
     */
    public function clearAll(): void
    {
        $_SESSION[$this->sessionKey] = [];
    }
}
