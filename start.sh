#!/bin/bash

echo "Starting MLOOK DTS application..."

# Create storage link if it doesn't exist (idempotent)
echo "Creating storage link..."
php artisan storage:link || echo "Storage link already exists or failed"

# Start queue worker in background (suppress errors to not crash main process)
echo "Starting queue worker..."
php artisan queue:work --tries=3 --timeout=90 2>&1 &
QUEUE_PID=$!
echo "Queue worker started (PID: $QUEUE_PID)"

# Note: Reverb needs its own service with its own port
# Starting it here will fail because Railway only provides one $PORT per service
# Uncomment below if you want to try (will likely fail):
# REVERB_PORT=${REVERB_SERVER_PORT:-8080}
# php artisan reverb:start --host=0.0.0.0 --port=$REVERB_PORT 2>&1 &
# REVERB_PID=$!

# Function to cleanup background processes on exit
cleanup() {
    echo "Shutting down services..."
    [ ! -z "$QUEUE_PID" ] && kill $QUEUE_PID 2>/dev/null || true
    [ ! -z "$REVERB_PID" ] && kill $REVERB_PID 2>/dev/null || true
    exit 0
}
trap cleanup SIGTERM SIGINT EXIT

# Start main web server (foreground - this keeps container alive)
# This is the main process Railway monitors
echo "Starting web server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT

