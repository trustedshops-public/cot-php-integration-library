# COT PHP Integration Library - Development Makefile

.PHONY: help dev start stop clean test logs status build restart docker docker-stop docker-logs open quick

# Default target
help:
	@echo "🚀 COT PHP Integration Library - Development Commands"
	@echo ""
	@echo "🚀 Quick Start:"
	@echo "  make start        - Start local PHP server"
	@echo "  make stop         - Stop local PHP server"
	@echo "  make restart      - Restart local server"
	@echo ""
	@echo "🐳 Docker Environment:"
	@echo "  make dev          - Start Docker with live file watching"
	@echo "  make docker       - Start Docker environment (simple)"
	@echo "  make docker-stop  - Stop Docker environment"
	@echo ""
	@echo "🔧 Development:"
	@echo "  make logs         - View container logs"
	@echo "  make status       - Show container status"
	@echo "  make build        - Build Docker image"
	@echo "  make test         - Run tests"
	@echo "  make open         - Open test page in browser"
	@echo ""
	@echo "📚 Documentation:"
	@echo "  make docs         - Open documentation"
	@echo ""

# Local Development Commands
start:
	@echo "🚀 Starting local PHP server..."
	@cd test-environment && ./start.sh

stop:
	@echo "🛑 Stopping local PHP server..."
	@pkill -f "php -S localhost:8081" 2>/dev/null || true
	@kill -9 $$(lsof -ti:8081) 2>/dev/null || true
	@echo "✅ Local server stopped"

restart: stop start

# Docker Environment Commands
dev:
	@echo "🚀 Starting Docker environment with live development..."
	@cd test-environment && ./docker-dev.sh

docker:
	@echo "🚀 Starting Docker environment..."
	@cd test-environment && ./docker-start.sh

docker-stop:
	@echo "🛑 Stopping Docker environment..."
	@cd test-environment && ./docker-stop.sh

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

test:
	@echo "🧪 Testing environment..."
	@curl -s http://localhost:8081/oauth-integration-test.php > /dev/null && echo "✅ Test page accessible" || echo "❌ Test page not accessible"

# Local Development Commands

# Clean up
clean:
	@echo "🧹 Cleaning up Docker environment..."
	@cd test-environment && docker-compose down -v --remove-orphans
	@docker system prune -f

# Open browser
open:
	@echo "🌐 Opening test page..."
	@open http://localhost:8081/oauth-integration-test.php 2>/dev/null || \
		xdg-open http://localhost:8081/oauth-integration-test.php 2>/dev/null || \
		echo "📖 Please open http://localhost:8081/oauth-integration-test.php manually"

# Documentation
docs:
	@echo "📚 Opening documentation..."
	@open test-environment/README.md 2>/dev/null || \
		xdg-open test-environment/README.md 2>/dev/null || \
		echo "📖 Please open test-environment/README.md manually"

# Quick development workflow
quick: clean dev
	@echo "🎯 Quick development environment ready!"


# Install dependencies
install:
	@echo "📦 Installing dependencies..."
	@composer install --no-dev --optimize-autoloader

# Development dependencies
install-dev:
	@echo "📦 Installing development dependencies..."
	@composer install

# Code quality
lint:
	@echo "🔍 Running code quality checks..."
	@vendor/bin/php-cs-fixer fix --dry-run --diff
	@vendor/bin/phpstan analyse src/

fix:
	@echo "🔧 Fixing code style..."
	@vendor/bin/php-cs-fixer fix

# Security check
security:
	@echo "🔒 Running security audit..."
	@composer audit

