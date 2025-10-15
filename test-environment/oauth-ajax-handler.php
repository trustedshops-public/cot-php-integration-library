<?php

declare(strict_types=1);

// Disable error output to prevent HTML from interfering with JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use SessionTokenStorage;
use TRSTD\COT\Client;
use TRSTD\COT\Token;

// Load configuration
$config = require_once 'config.php';

// Handle AJAX requests
if (isset($_GET['action'])) {
    // Set JSON header first
    header('Content-Type: application/json');
    
    // Start output buffering to catch any errors
    ob_start();
    
    try {
        // Start session with proper configuration
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session to use cookies and ensure proper session handling
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            session_start();
        }
    
    $sessionTokenStorage = new SessionTokenStorage();
    
    // Create Client instance using configuration
    $client = new Client(
        $config['ts_id'],
        $config['client_id'],
        $config['client_secret'],
        $sessionTokenStorage,
        $config['environment']
    );
    
        switch ($_GET['action']) {
            case 'auth_status':
                $isAuthenticated = $sessionTokenStorage->isAuthenticated();
                echo json_encode([
                    'success' => true,
                    'authenticated' => $isAuthenticated
                ]);
                break;
            
        case 'get_consumer_data':
            try {
                // First try to get tokens from session
                $idToken = null;
                $accessToken = null;
                
                if ($sessionTokenStorage->isAuthenticated()) {
                    // Get the first available user's tokens from session
                    foreach ($_SESSION['trstd_tokens'] as $userId => $tokenData) {
                        if (isset($tokenData['id_token']) && !empty($tokenData['id_token'])) {
                            $idToken = $tokenData['id_token'];
                            $accessToken = $tokenData['access_token'] ?? null;
                            break;
                        }
                    }
                }
                
                // If no tokens in session, try to get from URL fragment
                if (!$idToken) {
                    $referer = $_SERVER['HTTP_REFERER'] ?? '';
                    if (strpos($referer, 'id_token=') !== false) {
                        $urlParts = parse_url($referer);
                        if (isset($urlParts['fragment'])) {
                            parse_str($urlParts['fragment'], $fragment);
                            if (isset($fragment['id_token'])) {
                                $idToken = $fragment['id_token'];
                            }
                            if (isset($fragment['access_token'])) {
                                $accessToken = $fragment['access_token'];
                            }
                        }
                    }
                }
                
                if (!$idToken) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No ID token available'
                    ]);
                    break;
                }
                
                // Set the ID token cookie so Client::getIdentityCookie() can find it
                // Use the older setcookie format for better compatibility
                setcookie('TRSTD_ID_TOKEN', $idToken, time() + 3600, '/', '', false, false);
                
                // Also set it in $_COOKIE for immediate access
                $_COOKIE['TRSTD_ID_TOKEN'] = $idToken;
                
                // Store tokens in AuthStorage so Client can find them
                if ($accessToken) {
                    // Create a Token object and store it in SessionTokenStorage
                    $token = new Token($idToken, null, $accessToken); // idToken, refreshToken, accessToken
                    
                    // Decode ID token to get user ID (sub claim)
                    $decodedToken = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $idToken)[1])), true);
                    $userId = $decodedToken['sub'] ?? 'user';
                    
                    // Store in session storage using the user ID from the ID token
                    $sessionTokenStorage->set($userId, $token);
                }
                
                // Debug: Check what's in session storage
                $sessionData = $_SESSION['trstd_tokens'] ?? [];
                $cookieSet = isset($_COOKIE['TRSTD_ID_TOKEN']);
                
                // Now use the original Client class method
                // Client will get ID token from cookie, then find matching token in storage
                $consumerData = $client->getConsumerData();
                
                if ($consumerData) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'firstName' => $consumerData->firstName ?? '',
                            'lastName' => $consumerData->lastName ?? '',
                            'primaryEmailAddress' => $consumerData->primaryEmailAddress ?? '',
                            'membershipStatus' => $consumerData->membershipStatus ?? '',
                            'membershipSince' => $consumerData->membershipSince ?? ''
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No consumer data available',
                        'debug' => [
                            'session_tokens_count' => count($sessionData),
                            'cookie_set' => $cookieSet,
                            'id_token_provided' => !empty($idToken),
                            'access_token_provided' => !empty($accessToken),
                            'session_keys' => array_keys($sessionData)
                        ]
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'logout':
            $sessionTokenStorage->clearAll();
            
            // Also clear the ID token cookie
            setcookie('TRSTD_ID_TOKEN', '', [
                'expires' => time() - 3600, // Expire in the past
                'path' => '/',
                'secure' => false,
                'httponly' => false,
                'samesite' => 'Lax'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
            break;
            
        case 'debug_tokens':
            echo json_encode([
                'success' => true,
                'session_data' => $_SESSION,
                'is_authenticated' => $sessionTokenStorage->isAuthenticated(),
                'session_id' => session_id()
            ]);
            break;
            
            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action']);
        }
        
    } catch (Exception $e) {
        // Clear any output that might have been generated
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
    
    // Clean and send output
    ob_end_flush();
    exit;
}
?>
