# Test Environment Makefile

.PHONY: help dev start stop clean logs status build test local local-stop

# Default target
help:
	@echo "🧪 COT Test Environment - Development Commands"
	@echo ""
	@echo "🐳 Docker Commands:"
	@echo "  make dev          - Start with live file watching"
	@echo "  make start        - Start Docker environment"
	@echo "  make stop         - Stop Docker environment"
	@echo "  make restart      - Restart containers"
	@echo "  make clean        - Clean up everything"
	@echo ""
	@echo "🐳 Docker Commands (Agnostic):"
	@echo "  make docker       - Start with Docker (Desktop/Rancher agnostic)"
	@echo "  make docker-stop  - Stop Docker environment"
	@echo "  make docker-logs  - View Docker logs"
	@echo ""
	@echo "🔧 Development:"
	@echo "  make logs         - View container logs"
	@echo "  make status       - Show container status"
	@echo "  make build        - Build Docker image"
	@echo "  make test         - Test environment"
	@echo ""
	@echo "🌐 Local Commands:"
	@echo "  make local        - Start local PHP server"
	@echo "  make local-stop    - Stop local PHP server"
	@echo ""

# Docker Commands
dev:
	@./dev-start.sh

start:
	@./start-docker.sh

stop:
	@./stop-docker.sh

restart: stop start

clean:
	@echo "🧹 Cleaning up..."
	@docker-compose down -v --remove-orphans
	@docker system prune -f

logs:
	@docker-compose logs -f

status:
	@docker-compose ps

build:
	@docker-compose build

test:
	@echo "🧪 Testing environment..."
	@curl -s http://localhost:8081/oauth-integration-test.php > /dev/null && echo "✅ Test page accessible" || echo "❌ Test page not accessible"

# Local Development
local:
	@echo "🌐 Starting local PHP server..."
	@php -S localhost:8081 -t . &
	@echo "✅ Local server started at http://localhost:8081"
	@echo "🔗 Test page: http://localhost:8081/oauth-integration-test.php"
	@echo "🛑 To stop: make local-stop"

local-stop:
	@echo "🛑 Stopping local PHP server..."
	@kill -9 $$(lsof -ti:8081) 2>/dev/null || true
	@echo "✅ Local server stopped"

# Docker Commands (Agnostic)
docker:
	@./docker-start.sh

docker-stop:
	@./docker-stop.sh

docker-logs:
	@docker-compose logs -f

# Quick commands
quick: clean dev
	@echo "🎯 Quick development environment ready!"

# Open browser
open:
	@echo "🌐 Opening test page..."
	@open http://localhost:8081/oauth-integration-test.php 2>/dev/null || \
		xdg-open http://localhost:8081/oauth-integration-test.php 2>/dev/null || \
		echo "📖 Please open http://localhost:8081/oauth-integration-test.php manually"
