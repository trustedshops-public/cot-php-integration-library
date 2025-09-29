#!/bin/bash

echo "🚀 Starting COT Test Environment with Live Development..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
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
echo "✅ Test environment is running!"
echo "🌐 Access at: http://localhost:8081"
echo "🔗 Test page: http://localhost:8081/oauth-integration-test.php"
echo "🧪 Integration test: http://localhost:8081/oauth-integration-test.php"
echo ""
echo "📁 File watching is active - changes to src/ and vendor/ will be synced automatically"
echo ""
echo "🛠️  Development commands:"
echo "   📊 View logs: docker-compose logs -f"
echo "   🔄 Restart: docker-compose restart"
echo "   🛑 Stop: ./stop-docker.sh"
echo "   🧹 Clean: docker-compose down -v"
echo ""

# Show container status
docker-compose ps

echo ""
echo "🎯 Ready for development! Make changes to src/ and see them reflected immediately."
