<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use SessionTokenStorage;
use TRSTD\COT\Token;

// Set content type to JSON
header('Content-Type: application/json');

// Start session with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    // Configure session to use cookies and ensure proper session handling
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $requiredFields = ['access_token', 'user_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Create session token storage
    $tokenStorage = new SessionTokenStorage();
    
    // Create token object
    $token = new Token(
        $input['id_token'] ?? '', // ID token from OAuth redirect
        $input['refresh_token'] ?? '', // Refresh token (may be empty)
        $input['access_token']
    );
    
    // Store tokens in session
    $tokenStorage->set($input['user_id'], $token);
    
    // Also set the ID token cookie for the TRSTD Switch component
    if (!empty($input['id_token'])) {
        // Set cookie with same settings as the original Client class
        setcookie('TRSTD_ID_TOKEN', $input['id_token'], [
            'expires' => time() + 3600, // 1 hour
            'path' => '/',
            'secure' => false, // Set to true in production with HTTPS
            'httponly' => false, // Allow JavaScript access for the Switch component
            'samesite' => 'Lax'
        ]);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'OAuth tokens stored successfully',
        'user_id' => $input['user_id'],
        'has_access_token' => !empty($input['access_token']),
        'has_id_token' => !empty($input['id_token']),
        'has_refresh_token' => !empty($input['refresh_token']),
        'cookie_set' => !empty($input['id_token'])
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
