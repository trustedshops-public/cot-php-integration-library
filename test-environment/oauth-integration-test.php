<?php
// Load configuration
$config = require_once 'config.php';
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
        .switch-container {
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

        <div class="switch-container">
            <h3>TRSTD Switch Element</h3>
            <p>Click the switch below to authenticate and get OAuth tokens:</p>
            <trstd-switch tsId="<?php echo $config['ts_id']; ?>"></trstd-switch>
        </div>

        <div id="status" class="status info">
            <strong>Status:</strong> Waiting for user interaction...
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <button id="check-auth" class="btn">Check Authentication</button>
            <button id="get-consumer-data" class="btn">Get Consumer Data</button>
            <button id="logout" class="btn">Logout</button>
        </div>

        <div id="consumer-data" class="debug" style="display: none;">
            <h4>Consumer Data:</h4>
            <pre id="consumer-data-content"></pre>
        </div>

        <div class="debug">
            <h4>Debug Information:</h4>
            <div id="debug-info">
                <p><strong>Switch element loaded:</strong> <span id="switch-loaded">Checking...</span></p>
                <p><strong>Current URL:</strong><br><span id="current-url" style="display: block; margin-top: 5px;"><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></span></p>
                <p><strong>URL Fragment:</strong><br><span id="url-fragment" style="display: block; margin-top: 5px;">No fragment</span></p>
                <p><strong>Tokens stored:</strong> <span id="tokens-stored">No</span></p>
            </div>
        </div>
    </div>

    <!-- TRSTD Switch Script -->
    <script type="module" src="http://cdn.trstd-login-test.trstd.com/switch/switch.js"></script>
    
    <script>
        // Check if switch element is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const switchElement = document.querySelector('trstd-switch');
            const switchLoaded = document.getElementById('switch-loaded');
            
            if (switchElement) {
                switchLoaded.textContent = 'Yes';
                switchLoaded.style.color = 'green';
            } else {
                switchLoaded.textContent = 'No';
                switchLoaded.style.color = 'red';
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

            // Check for OAuth tokens in URL fragment on page load
            extractTokensFromUrl();
        });

        // Extract tokens from URL fragment and store them
        function extractTokensFromUrl() {
            const hash = window.location.hash.substring(1);
            if (!hash) return;

            const params = new URLSearchParams(hash);
            const tokens = {
                access_token: params.get('access_token'),
                id_token: params.get('id_token'),
                refresh_token: params.get('refresh_token'),
                code: params.get('code'),
                state: params.get('state'),
                session_state: params.get('session_state')
            };

            // Only proceed if we have an access token
            if (tokens.access_token && tokens.id_token) {
                console.log('OAuth tokens found in URL fragment:', tokens);
                
                // Extract user ID from ID token
                try {
                    const payload = JSON.parse(atob(tokens.id_token.split('.')[1]));
                    tokens.user_id = payload.sub;
                    
                    // Store tokens in backend
                    storeTokens(tokens);
                    
                    // Clear the URL fragment to clean up the URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                    
                } catch (e) {
                    console.error('Failed to decode ID token:', e);
                    updateStatus('error', 'Failed to decode ID token: ' + e.message);
                }
            }
        }

        // Store tokens in backend
        function storeTokens(tokenData) {
            console.log('Storing tokens:', tokenData);
            
            if (!tokenData.user_id) {
                console.error('No user ID available for token storage');
                updateStatus('error', 'No user ID available for token storage');
                return;
            }
            
            fetch('store-tokens.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: tokenData.user_id,
                    access_token: tokenData.access_token,
                    id_token: tokenData.id_token,
                    refresh_token: tokenData.refresh_token || null,
                    code: tokenData.code,
                    state: tokenData.state,
                    session_state: tokenData.session_state
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Tokens stored successfully:', data);
                    updateStatus('success', '🎉 OAuth tokens stored successfully!');
                    document.getElementById('tokens-stored').textContent = 'Yes';
                    document.getElementById('tokens-stored').style.color = 'green';
                } else {
                    console.error('Failed to store tokens:', data.error);
                    updateStatus('error', 'Failed to store tokens: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error storing tokens:', error);
                updateStatus('error', 'Error storing tokens: ' + error.message);
            });
        }

        // Listen for TRSTD Switch events
        document.addEventListener('trstd-switch-authenticated', function(event) {
            console.log('TRSTD Switch: User authenticated', event.detail);
            updateStatus('success', '✅ User authenticated via TRSTD Switch!');
        });

        document.addEventListener('trstd-switch-logout', function(event) {
            console.log('TRSTD Switch: User logged out', event.detail);
            updateStatus('info', '❌ User logged out');
            document.getElementById('tokens-stored').textContent = 'No';
            document.getElementById('tokens-stored').style.color = 'red';
        });

        function updateStatus(type, message) {
            const statusDiv = document.getElementById('status');
            statusDiv.className = `status ${type}`;
            statusDiv.innerHTML = `<strong>Status:</strong> ${message}`;
        }

        // Check authentication status
        function checkAuthStatus() {
            fetch('oauth-ajax-handler.php?action=auth_status')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.authenticated) {
                        updateStatus('success', '✅ User is authenticated');
                    } else {
                        updateStatus('info', '❌ User is not authenticated');
                    }
                })
                .catch(error => {
                    updateStatus('error', 'Error checking auth status: ' + error.message);
                });
        }

        // Get consumer data
        function getConsumerData() {
            const button = document.getElementById('get-consumer-data');
            button.disabled = true;
            button.textContent = 'Getting...';
            
            fetch('oauth-ajax-handler.php?action=get_consumer_data')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        updateStatus('success', '✅ Consumer data retrieved successfully!');
                        document.getElementById('consumer-data').style.display = 'block';
                        document.getElementById('consumer-data-content').textContent = JSON.stringify(data.data, null, 2);
                    } else {
                        updateStatus('error', 'Failed to get consumer data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    updateStatus('error', 'Error getting consumer data: ' + error.message);
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = 'Get Consumer Data';
                });
        }

        // Logout
        function logout() {
            fetch('oauth-ajax-handler.php?action=logout')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatus('info', '✅ Logged out successfully');
                        document.getElementById('tokens-stored').textContent = 'No';
                        document.getElementById('tokens-stored').style.color = 'red';
                        document.getElementById('consumer-data').style.display = 'none';
                        
                        // Clear URL fragment to remove OAuth tokens from browser history
                        if (window.location.hash) {
                            // Use replaceState to avoid adding to browser history
                            window.history.replaceState(null, null, window.location.pathname + window.location.search);
                        }
                        
                        // Force TRSTD Switch to reset by recreating it
                        const switchElement = document.querySelector('trstd-switch');
                        if (switchElement) {
                            const parent = switchElement.parentNode;
                            const nextSibling = switchElement.nextSibling;
                            const tsId = switchElement.getAttribute('tsId');
                            
                            // Remove the current switch
                            switchElement.remove();
                            
                            // Create a new switch element (this will reset its internal state)
                            const newSwitch = document.createElement('trstd-switch');
                            newSwitch.setAttribute('tsId', tsId);
                            parent.insertBefore(newSwitch, nextSibling);
                            
                            // Update debug info
                            document.getElementById('url-fragment').textContent = 'No fragment';
                        }
                    } else {
                        updateStatus('error', 'Failed to logout: ' + data.message);
                    }
                })
                .catch(error => {
                    updateStatus('error', 'Error logging out: ' + error.message);
                });
        }

        // Event listeners
        document.getElementById('check-auth').addEventListener('click', checkAuthStatus);
        document.getElementById('get-consumer-data').addEventListener('click', getConsumerData);
        document.getElementById('logout').addEventListener('click', logout);
    </script>
</body>
</html>

