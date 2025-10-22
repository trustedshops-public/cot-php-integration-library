#!/bin/bash

echo "🐳 Starting COT Test Environment (Docker/Rancher Agnostic with Development Features)..."
echo "📁 Setting up file watching and live sync..."
echo ""

# Check if Docker is running (works with both Docker Desktop and Rancher Desktop)
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop or Rancher Desktop first."
    echo "💡 Make sure Docker is running and accessible"
    exit 1
fi

# Detect Docker provider
DOCKER_PROVIDER="Unknown"
if docker context ls | grep -q "rancher-desktop"; then
    DOCKER_PROVIDER="Rancher Desktop"
    echo "🐄 Detected: Rancher Desktop"
elif docker context ls | grep -q "desktop-linux"; then
    DOCKER_PROVIDER="Docker Desktop"
    echo "🐳 Detected: Docker Desktop"
else
    DOCKER_PROVIDER="Docker"
    echo "🐳 Detected: Docker"
fi

# Stop any existing containers
echo "🧹 Cleaning up existing containers..."
docker-compose down 2>/dev/null || true

# Build and start the environment
echo "🔨 Building Docker image..."
docker-compose build

echo "🚀 Starting containers..."
docker-compose up -d

# Wait for container to be ready
echo "⏳ Waiting for container to be ready..."
sleep 5

# Check if container is running
if ! docker-compose ps | grep -q "Up"; then
    echo "❌ Failed to start container. Check logs with: docker-compose logs"
    exit 1
fi

echo ""
echo "✅ Test environment is running with $DOCKER_PROVIDER!"
echo "🔗 Test page: https://localhost:8443/oauth-integration-test.php"
echo ""
echo "📁 Direct volume mounts - changes to src/ and vendor/ are reflected immediately"
echo ""
echo "🛠️  Development commands:"
echo "   📊 View logs: docker-compose logs -f"
echo "   🔄 Restart: docker-compose restart"
echo "   🛑 Stop: ./docker-stop.sh"
echo "   🧹 Clean: docker-compose down -v"
echo ""

# Show container status
docker-compose ps

echo ""
echo "🎯 Ready for development! Make changes to src/ and see them reflected immediately."
