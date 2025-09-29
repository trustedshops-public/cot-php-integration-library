#!/bin/bash

echo "🚀 Starting OAuth Integration Test Environment..."
echo "📍 Server will be available at: http://localhost:8081"
echo "🔗 Test page: http://localhost:8081/oauth-integration-test.php"
echo "🧪 Integration test: http://localhost:8081/oauth-integration-test.php"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

cd "$(dirname "$0")"
php -S localhost:8081 -t .
