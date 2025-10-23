# COT PHP Integration Library - Development Makefile

.PHONY: help dev clean test logs status build docker-stop docker-logs open quick debug-logs

# Default target
help:
	@echo "🚀 COT PHP Integration Library - Development Commands"
	@echo ""
	@echo "🐳 Docker Environment:"
	@echo "  make dev          - Start Docker with live file watching"
	@echo "  make docker-stop  - Stop Docker environment"
	@echo ""
	@echo "🐛 Debugging:"
	@echo "  make dev          - Start Docker with Xdebug enabled"
	@echo "  make debug-logs   - Check Xdebug logs"
	@echo ""
	@echo "🔧 Development:"
	@echo "  make logs         - View container logs"
	@echo "  make status       - Show container status"
	@echo "  make build        - Build Docker image"
	@echo ""
	@echo "🔐 Certificates:"
	@echo "  make certs        - Generate self-signed localhost TLS certs for HTTPS (8443)"
	@echo ""


# Docker Environment Commands
dev:
	@echo "🚀 Starting Docker environment with live development..."
	@cd test-environment && ./docker-dev.sh


docker-stop:
	@echo "🛑 Stopping Docker environment..."
	@cd test-environment && ./docker-stop.sh

# Debug Commands
debug-logs:
	@echo "📋 Checking Xdebug logs..."
	@cd test-environment && docker-compose exec test-environment cat /tmp/xdebug.log 2>/dev/null || echo "No Xdebug log found"

# Development Commands
logs:
	@echo "📊 Viewing container logs..."
	@cd test-environment && docker-compose logs -f

status:
	@echo "📊 Container status:"
	@cd test-environment && docker-compose ps

build:
	@echo "🔨 Building Docker image..."
	@cd test-environment && docker-compose build



# Generate self-signed localhost TLS certificates for Apache proxy
certs:
	@echo "🔐 Generating self-signed TLS certificates for https://localhost:8443 ..."
	@mkdir -p test-environment/certs
	@openssl req -x509 -newkey rsa:2048 -keyout test-environment/certs/localhost-key.pem -out test-environment/certs/localhost.pem -days 365 -nodes -subj "/CN=localhost"
	@echo "✅ Certificates created: test-environment/certs/localhost.pem and localhost-key.pem"
	@echo "ℹ️  If your browser complains, import and trust the cert (or use mkcert)."

# Install dependencies
install:
	@echo "📦 Installing dependencies..."
	@composer install --no-dev --optimize-autoloader

# Development dependencies
install-dev:
	@echo "📦 Installing development dependencies..."
	@composer install

