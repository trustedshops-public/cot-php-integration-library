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
    'ts_id' => 'YOUR_ACTUAL_TS_ID',                    
    'client_id' => 'trstd-switch-YOUR_ACTUAL_TS_ID',  
    'client_secret' => 'YOUR_ACTUAL_CLIENT_SECRET',   
    'environment' => 'qa'                               
];
?>
```

## 🚀 Commands:

```bash

make start

make stop

make restart

```

## 🧪 Testing:

### **Test URL:**
- **Test Page (HTTPS)**: https://localhost:8443/oauth-integration-test.php
- **HTTP (dev only)**: http://localhost:8081/oauth-integration-test.php

## 🐛 Debugging:

### **Debug Workflow:**
1. **Start Xdebug in your IDE first** (e.g., Cursor, VS Code, PhpStorm)
2. **Run development environment**: `make dev`
3. **Set breakpoints** in your PHP code
4. **Visit test page**: https://localhost:8443/oauth-integration-test.php
5. **Code will pause** at breakpoints for inspection

### **Important Notes:**
- **Always start Xdebug in IDE before running `make dev`**
- The environment will attach to your IDE's debugger
- Xdebug is configured to connect to `host.docker.internal:9003`

### **Stop Development Environment:**
```bash
make docker-stop
```
