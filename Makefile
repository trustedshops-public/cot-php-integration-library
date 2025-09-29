# COT PHP Integration Library - Development Makefile

.PHONY: help dev start stop clean test logs status build

# Default target
help:
	@echo "🚀 COT PHP Integration Library - Development Commands"
	@echo ""
	@echo "📦 Docker Environment:"
	@echo "  make dev          - Start Docker environment with live file watching"
	@echo "  make start        - Start Docker environment (simple)"
	@echo "  make stop         - Stop Docker environment"
	@echo "  make restart      - Restart Docker environment"
	@echo "  make clean        - Stop and remove all containers/volumes"
	@echo ""
	@echo "🐳 Docker (Agnostic):"
	@echo "  make docker      - Start with Docker (Desktop/Rancher agnostic)"
	@echo "  make docker-stop - Stop Docker environment"
	@echo "  make docker-logs - View Docker logs"
	@echo ""
	@echo "🔧 Development:"
	@echo "  make logs         - View container logs"
	@echo "  make status       - Show container status"
	@echo "  make build        - Build Docker image"
	@echo "  make test         - Run tests"
	@echo ""
	@echo "🌐 Local Development:"
	@echo "  make local        - Start local PHP server"
	@echo "  make local-stop   - Stop local PHP server"
	@echo ""
	@echo "📚 Documentation:"
	@echo "  make docs         - Open documentation"
	@echo ""

# Docker Environment Commands
dev:
	@echo "🚀 Starting Docker environment with live development..."
	@cd test-environment && ./docker-dev.sh

start:
	@echo "🚀 Starting Docker environment..."
	@cd test-environment && ./docker-start.sh

stop:
	@echo "🛑 Stopping Docker environment..."
	@cd test-environment && ./docker-stop.sh

restart: stop start

clean:
	@echo "🧹 Cleaning up Docker environment..."
	@cd test-environment && docker-compose down -v --remove-orphans
	@docker system prune -f

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
	@echo "🧪 Running tests..."
	@cd test-environment && docker-compose exec test-environment php -v
	@echo "✅ Container is running and accessible"

# Local Development Commands
local:
	@echo "🌐 Starting local PHP server..."
	@cd test-environment && php -S localhost:8081 -t . &
	@echo "✅ Local server started at http://localhost:8081"
	@echo "🔗 Test page: http://localhost:8081/oauth-integration-test.php"
	@echo "🛑 To stop: make local-stop"

local-stop:
	@echo "🛑 Stopping local PHP server..."
	@kill -9 $$(lsof -ti:8081) 2>/dev/null || true
	@echo "✅ Local server stopped"

# Documentation
docs:
	@echo "📚 Opening documentation..."
	@open test-environment/README.md 2>/dev/null || \
		xdg-open test-environment/README.md 2>/dev/null || \
		echo "📖 Please open test-environment/README.md manually"

# Docker Commands (Agnostic)
docker:
	@cd test-environment && ./docker-start.sh

docker-stop:
	@cd test-environment && ./docker-stop.sh

docker-logs:
	@cd test-environment && docker-compose logs -f

# Quick development workflow
quick: clean dev
	@echo "🎯 Quick development environment ready!"

# Production-like testing
prod-test:
	@echo "🏭 Running production-like test..."
	@cd test-environment && docker-compose -f docker-compose.yml -f docker-compose.prod.yml up --build

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
