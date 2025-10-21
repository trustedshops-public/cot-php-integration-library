#!/bin/bash

echo "🐳 Starting COT Test Environment (Docker/Rancher Agnostic)..."
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

# Build and start the environment
echo "🔨 Building Docker image..."
docker-compose build

echo "🚀 Starting containers..."
docker-compose up -d

echo ""
echo "✅ Test environment is running with $DOCKER_PROVIDER!"
echo "🌐 Access at: http://localhost:8081 (HTTP) / https://localhost:8443 (HTTPS)"
echo "🔗 Test page: https://localhost:8443/oauth-integration-test.php"
echo "🧪 Integration test: https://localhost:8443/oauth-integration-test.php"
echo ""
echo "📁 File watching is active - changes to src/ and vendor/ will be synced automatically"
echo "🛑 To stop: ./docker-stop.sh"
echo "📊 To view logs: docker-compose logs -f"
echo ""

# Show container status
docker-compose ps

echo ""
echo "🎯 Ready for development! Make changes to src/ and see them reflected immediately."