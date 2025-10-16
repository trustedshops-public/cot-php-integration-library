<?php

declare(strict_types=1);

// Disable error output to prevent HTML from interfering with JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/SessionTokenStorage.php';

use TRSTD\COT\Token;

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'store_tokens') {
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
        
        // Handle token storage from JavaScript (URL fragments)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id_token']) || !isset($input['access_token'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Missing required token data'
            ]);
            exit;
        }
        
        // Create token object
        $token = new Token(
            $input['id_token'],
            $input['refresh_token'] ?? '',
            $input['access_token']
        );
        
        // Store in session storage using the user ID
        $userId = $input['user_id'] ?? 'user';
        $sessionTokenStorage->set($userId, $token);
        
        // Set the ID token cookie (this is what getConsumerData() needs)
        setcookie('TRSTD_ID_TOKEN', $input['id_token'], [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tokens stored successfully'
        ]);
        
    } catch (Exception $e) {
        // Clean any output that might have been generated
        ob_clean();
        
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
    } finally {
        // End output buffering
        ob_end_flush();
    }
} else {
    // No action specified or invalid action
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid or missing action']);
}