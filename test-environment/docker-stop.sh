#!/bin/bash

echo "🛑 Stopping COT Test Environment..."
echo ""

# Stop and remove containers
docker-compose down

echo "🧹 Cleaning up resources..."
# Remove the custom network if it exists
docker network rm cot-test-environment_cot-test-network 2>/dev/null || true

# Optional: Clean up unused images (be careful with this)
read -p "🗑️  Remove unused Docker images? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🧹 Cleaning up unused images..."
    docker image prune -f
fi

echo "✅ Test environment stopped successfully!"
echo ""
echo "💡 To start again: ./rancher-start.sh"
echo "🐳 Docker/Rancher agnostic setup complete!"