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
- **Test Page**: http://localhost:8081/oauth-integration-test.php

## 🐛 Debugging:

### **Start Debug Environment:**
```bash
make debug
```

### **Debug Workflow:**
1. Start debug environment: `make debug`
2. Set breakpoints in your PHP code
3. Start debugging session in IDE
4. Visit test page: http://localhost:8081/oauth-integration-test.php
5. Code will pause at breakpoints for inspection

### **Stop Debug Environment:**
```bash
make debug-stop
```
