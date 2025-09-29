# Test Environment for OAuth Integration

This directory contains all files needed to test the OAuth integration solution with the TRSTD Switch Element.

## 📁 Files in this directory:

- `oauth-integration-test.php` - Main frontend page with TRSTD Switch integration
- `oauth-ajax-handler.php` - AJAX backend handler for authentication and consumer data
- `store-tokens.php` - Backend endpoint for storing OAuth tokens
- `config.php` - Configuration file with test environment credentials
- `FRONTEND_CONSUMER_DATA_SOLUTION.md` - Complete documentation of the solution

## 🚀 How to start the test environment:

### **Option 1: Using Make (Recommended)**
```bash
# From project root
make dev          # Start Docker with live file watching
make docker       # Start with Docker (Desktop/Rancher agnostic)
make start        # Start Docker environment
make local        # Start local PHP server

# From test-environment directory
cd test-environment
make dev          # Start Docker with live file watching
make docker       # Start with Docker (Desktop/Rancher agnostic)
make start        # Start Docker environment
make local        # Start local PHP server
```

### **Option 2: Direct Scripts**
```bash
cd test-environment

# Start with Docker and live file watching
./dev-start.sh

# Or use the simple start script
./start-docker.sh
```

### **Option 3: Local PHP Server**
```bash
cd test-environment
php -S localhost:8081 -t .
```

### **Access the test page:**
- **Main Test Page**: http://localhost:8081/oauth-integration-test.php
- **Integration Test**: http://localhost:8081/oauth-integration-test.php

## 🧪 How to test:

1. **Click the TRSTD Switch** to authenticate
2. **Use the test buttons:**
   - "Check Authentication" - Verify login status
   - "Get Consumer Data" - Retrieve user data
   - "Logout" - Clear session

## 🔧 Configuration:

The test environment uses:
- **Environment**: QA (Test)
- **TS ID**: `X832CCBC339C1B6586599463D3C2C5DF5`
- **Client ID**: `trstd-switch-X832CCBC339C1B6586599463D3C2C5DF5`
- **Client Secret**: `Yd51N9mJxczG2N0jFFwwMAncox5P0BUB`

## 🛑 How to stop:

### **Using Make:**
```bash
# From project root
make stop         # Stop Docker environment
make local-stop   # Stop local PHP server

# From test-environment directory
make stop         # Stop Docker environment
make local-stop   # Stop local PHP server
```

### **Direct Scripts:**
```bash
./stop-docker.sh
```

### **Local PHP Server:**
- Press `Ctrl+C` in the terminal
- Or kill the process: `kill -9 $(lsof -ti:8081)`

## 🐳 Docker Development Features:

- **📁 Live File Watching**: Changes to `src/` and `vendor/` are automatically synced
- **🔄 Hot Reload**: No need to restart containers for code changes
- **📦 Isolated Environment**: Consistent PHP 8.4 environment
- **🛠️ Development Tools**: Built-in file watching and sync

## 🐳 Docker Agnostic Integration:

### **Prerequisites:**
- Docker Desktop OR Rancher Desktop must be running
- Any Docker context is supported
- Check context: `docker context ls`

### **Docker Agnostic Commands:**
```bash
# Start with Docker (works with Desktop or Rancher)
make docker

# Stop Docker environment
make docker-stop

# View Docker logs
make docker-logs
```

### **Agnostic Benefits:**
- **🐳 Universal Compatibility**: Works with Docker Desktop and Rancher Desktop
- **🔧 Auto-Detection**: Automatically detects your Docker provider
- **📊 Resource Management**: Optimized for any Docker setup
- **🛠️ Development Tools**: Enhanced debugging and monitoring

## 🛠️ Make Commands Reference:

### **Development Commands:**
```bash
make help         # Show all available commands
make dev          # Start Docker with live file watching
make docker       # Start with Docker (Desktop/Rancher agnostic)
make start        # Start Docker environment
make stop         # Stop Docker environment
make restart      # Restart Docker environment
make clean        # Clean up containers and volumes
```

### **Monitoring Commands:**
```bash
make logs         # View container logs
make docker-logs  # View Docker logs
make status       # Show container status
make test         # Test if environment is working
```

### **Local Development:**
```bash
make local        # Start local PHP server
make local-stop   # Stop local PHP server
make open         # Open test page in browser
```

### **Quick Development:**
```bash
make quick        # Clean and start development environment
```

## 📚 Documentation:

See `FRONTEND_CONSUMER_DATA_SOLUTION.md` for complete implementation details and architecture explanation.
