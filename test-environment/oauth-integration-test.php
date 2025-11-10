<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/SessionTokenStorage.php';

use TRSTD\COT\Client;

// Load configuration
$config = require_once 'config.php';

// Start session
session_start();

// Initialize client
$client = new Client(
    $config['client_id'],
    $config['client_secret'],
    new SessionTokenStorage(),
    $config['environment']
);

// Handle OAuth callback and initialize PKCE parameters
$client->handleCallback();

$consumerData = $client->getConsumerData();

// Debug: Check what's in session storage
$sessionData = $_SESSION['trstd_tokens'] ?? [];
$cookieSet = isset($_COOKIE['TRSTD_ID_TOKEN']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Integration Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .trstd-login-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border: 2px dashed #ddd;
            border-radius: 8px;
        }
        .debug {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 12px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            overflow-x: auto;
        }
        .debug pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            overflow-x: auto;
            background: #fff;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        .debug p {
            margin: 5px 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .debug span {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 OAuth Integration Test</h1>

        <div class="status info">
            <strong>Environment:</strong> <?php echo strtoupper($config['environment']); ?><br>
            <strong>TS ID:</strong> <?php echo $config['ts_id']; ?>
        </div>

            <div class="trstd-login-container">
                <h3>trstd login element</h3>
                <p>Click the trstd login below to authenticate and get OAuth tokens:</p>
                <trstd-login tsId="<?php echo $config['ts_id']; ?>" id="trstd-login"></trstd-login>
            </div>

        <div id="status" class="status info">
            <strong>Status:</strong> <?php echo $consumerData ? 'Authenticated' : 'Waiting for user interaction...'; ?>
        </div>

            <?php if ($consumerData): ?>
            <div class="debug">
                <h4>Consumer Data (from handleCallback):</h4>
                <pre><?php echo htmlspecialchars(json_encode($consumerData, JSON_PRETTY_PRINT)); ?></pre>
            </div>
            <?php endif; ?>



            <div class="debug">
                <h4>Debug Information:</h4>
                <div id="debug-info">
                    <p><strong>trstd login element loaded:</strong> <span id="trstd-login-loaded">Checking...</span></p>
                    <p><strong>Current URL:</strong><br><span id="current-url" style="display: block; margin-top: 5px;"><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></span></p>
                    <p><strong>URL Fragment:</strong><br><span id="url-fragment" style="display: block; margin-top: 5px;">No fragment</span></p>
                    <p><strong>Tokens stored:</strong> <span id="tokens-stored"><?php echo $consumerData ? 'Yes' : 'No'; ?></span></p>
                    <p><strong>Session tokens count:</strong> <?php echo count($sessionData); ?></p>
                    <p><strong>Cookie set:</strong> <?php echo $cookieSet ? 'Yes' : 'No'; ?></p>
                    <p><strong>Session keys:</strong> <?php echo implode(', ', array_keys($sessionData)); ?></p>
                </div>
            </div>
    </div>

    <!-- trstd login script -->
    <script type="module" src="https://cdn.trstd-login-test.trstd.com/trstd-login/script.js"></script>

    <script>
        // Check if trstd login element is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const trstdLoginElement = document.querySelector('trstd-login');
            const trstdLoginLoaded = document.getElementById('trstd-login-loaded');

            if (trstdLoginElement) {
                trstdLoginLoaded.textContent = 'Yes';
                trstdLoginLoaded.style.color = 'green';

                trstdLoginElement.redirectUrl = window.location.origin + window.location.pathname;
                console.log('trstd login configured with redirectUrl:', trstdLoginElement.redirectUrl);
            } else {
                trstdLoginLoaded.textContent = 'No';
                trstdLoginLoaded.style.color = 'red';
            }

            // Update URL fragment display
            const urlFragment = document.getElementById('url-fragment');
            const hash = window.location.hash;
            if (hash) {
                // Format long URLs with line breaks for better readability
                const formattedHash = hash.replace(/&/g, '&\n').replace(/=/g, '=\n');
                urlFragment.innerHTML = '<pre style="white-space: pre-wrap; word-wrap: break-word; font-size: 10px; max-width: 100%; overflow-x: auto;">' + formattedHash + '</pre>';
            } else {
                urlFragment.textContent = 'No fragment';
            }

        });

        // Listen for trstd login events
        document.addEventListener('trstd-login.auth', function(event) {
            if (event.detail === 'LOGGED_IN') {
                console.log('trstd login: User authenticated', event.detail);
                updateStatus('success', '✅ User authenticated via trstd login!');
            } else {
                console.log('trstd login: User logged out', event.detail);
                updateStatus('info', '❌ User logged out');
                document.getElementById('tokens-stored').textContent = 'No';
                document.getElementById('tokens-stored').style.color = 'red';
            }
        });

        function updateStatus(type, message) {
            const statusDiv = document.getElementById('status');
            statusDiv.className = `status ${type}`;
            statusDiv.innerHTML = `<strong>Status:</strong> ${message}`;
        }

    </script>
</body>
</html>

