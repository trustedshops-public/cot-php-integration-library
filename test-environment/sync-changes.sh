#!/bin/bash

echo "👀 Starting file watcher for live development..."

# Function to sync changes
sync_changes() {
    echo "🔄 Changes detected, syncing..."
    
    # Sync source code
    if [ -d "/var/www/html/src" ]; then
        rsync -av --delete /var/www/html/src/ /var/www/html/test-environment/src/ 2>/dev/null || true
        echo "✅ Source code synced"
    fi
    
    # Sync vendor directory
    if [ -d "/var/www/html/vendor" ]; then
        rsync -av --delete /var/www/html/vendor/ /var/www/html/test-environment/vendor/ 2>/dev/null || true
        echo "✅ Vendor dependencies synced"
    fi
    
    echo "🎉 Sync completed at $(date)"
}

# Initial sync
echo "🚀 Performing initial sync..."
sync_changes

# Watch for changes
while inotifywait -r -e modify,create,delete,move,attrib \
    /var/www/html/src \
    /var/www/html/vendor \
    --exclude '.*\.swp' \
    --exclude '.*\.tmp' \
    --exclude '.*\.log' \
    2>/dev/null; do
    
    sync_changes
done
