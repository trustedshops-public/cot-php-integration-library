# Test Environment for OAuth Integration

This directory contains all files needed to test the OAuth integration solution with the TRSTD Switch Element.

## ⚙️ Configuration:

### **1. Create Configuration File:**
```bash
cd test-environment
cp config.example.php config.php
```

### **2. Update Configuration:**
Edit `config.php` with your actual credentials:
```php
<?php
return [
    'ts_id' => 'YOUR_ACTUAL_TS_ID',                    // Your Trusted Shops ID
    'client_id' => 'trstd-switch-YOUR_ACTUAL_TS_ID',  // OAuth Client ID
    'client_secret' => 'YOUR_ACTUAL_CLIENT_SECRET',   // OAuth Client Secret
    'environment' => 'qa'                               // 'qa' or 'production'
];
?>
```

### **3. Security:**
- `config.php` is ignored by git (contains credentials)
- `config.example.php` is tracked (template only)
- Never commit actual credentials to the repository

## 🚀 Commands:

### **Start Environment:**
```bash
# From project root
make start

# From test-environment directory
cd test-environment
make start
```

### **Stop Environment:**
```bash
# From project root
make stop

# From test-environment directory
make stop
```

### **Restart Environment:**
```bash
# From project root
make restart

# From test-environment directory
make restart
```

## 🧪 Testing:

### **Test URL:**
- **Test Page**: http://localhost:8081/oauth-integration-test.php

### **How to Test:**
1. **Click the TRSTD Switch** to authenticate
2. **Use the test buttons:**
   - "Check Authentication" - Verify login status
   - "Get Consumer Data" - Retrieve user data
   - "Logout" - Clear session

## 🐳 Docker (Optional):

For Docker-based development with live file watching:
```bash
make dev          # Start Docker with live file watching
make docker-stop  # Stop Docker environment
```